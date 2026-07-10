"""
Structure Normalizer — Lớp chuẩn hóa cấu trúc thô (100% rule-based, KHÔNG AI).

Nhận output thô của extractor.extract_docx_structure (paragraphs + tables với
grid_span/v_merge) và suy ra schema field gợi ý bằng luật xác định:

  Rule 1  merge_continuation_tables  — gộp các bảng bị ngắt trang (header trùng nhau).
  Rule 2  section rows               — hàng gộp ngang gần hết bề rộng = tiêu đề nhóm.
  Rule 3  repeated_header rows       — hàng data trùng y hệt header = header lặp -> loại.
  Type inference                     — date / number / checkbox / select / text theo regex + thống kê.
  Field key                          — slug hóa tiếng Việt -> snake_case, đảm bảo unique.

Không xóa dữ liệu: các hàng bị loại được ghi vào `excluded_rows` để admin xem lại.
"""

from __future__ import annotations

import re
import unicodedata
from typing import Any

# ── Ngưỡng cấu hình ────────────────────────────────────────────────
ENUM_MAX_DISTINCT = 8      # cột có <= N giá trị unique (không rỗng) -> coi là select
HEADER_MIN_DISTINCT = 2    # hàng header phải có > 1 nhãn khác nhau (để không nhầm với section)

_DOTLINE_RE = re.compile(r"[.…_]{2,}")  # chuỗi chấm/gạch dưới = ô điền trống

# Từ khóa nhận diện khối "không phải dữ liệu" cần bỏ khỏi gợi ý:
_SIGNATURE_KW = [
    "(ký", "ký tên", "ký ghi rõ", "ký và ghi", "họ và tên", "họ tên",
    "người lập", "người kiểm tra", "người duyệt", "người phê duyệt", "người soát",
    "phê duyệt", "giám đốc", "trưởng khoa", "trưởng phòng", "phụ trách", "kỹ sư",
    "xác nhận của", "thủ trưởng",
]
_LETTERHEAD_KW = [
    "cộng hòa xã hội", "độc lập", "tự do", "hạnh phúc",
    "bệnh viện", "sở y tế", "trung tâm y tế", "bộ y tế",
]
# Dòng ngày ký kiểu "…, ngày … tháng … năm …"
_DATELINE_RE = re.compile(r"ng[àa]y.*th[áa]ng.*n[ăa]m", re.IGNORECASE)
_HAS_LETTER_RE = re.compile(r"[A-Za-zÀ-ỹ]")
_DATE_RE = re.compile(r"^\s*\d{1,2}\s*[/\-.]\s*\d{1,2}\s*[/\-.]\s*\d{2,4}\s*$")
_NUMBER_RE = re.compile(r"^\s*-?\d{1,3}([.,]\d{3})*([.,]\d+)?\s*$")
_BOOL_VALUES = {"x", "✓", "v", "có", "co", "yes", "true", "1", "☑"}


# ── Slug hóa tiếng Việt -> snake_case ─────────────────────────────
def _strip_vietnamese(text: str) -> str:
    text = text.replace("đ", "d").replace("Đ", "D")
    nfkd = unicodedata.normalize("NFKD", text)
    return "".join(c for c in nfkd if not unicodedata.combining(c))


def slugify_key(label: str, fallback: str = "field") -> str:
    ascii_txt = _strip_vietnamese(label).lower()
    ascii_txt = re.sub(r"[^a-z0-9]+", "_", ascii_txt).strip("_")
    ascii_txt = re.sub(r"_+", "_", ascii_txt)
    # Cắt bớt cho gọn (giữ tối đa 6 từ)
    parts = [p for p in ascii_txt.split("_") if p]
    ascii_txt = "_".join(parts[:6])
    return ascii_txt or fallback


def _unique_key(base: str, used: set[str]) -> str:
    key = base
    i = 2
    while key in used:
        key = f"{base}_{i}"
        i += 1
    used.add(key)
    return key


# ── Suy kiểu field từ danh sách giá trị data ──────────────────────
def _looks_like_date(v: str) -> bool:
    return bool(_DATE_RE.match(v))


def _looks_like_number(v: str) -> bool:
    return bool(_NUMBER_RE.match(v))


def infer_column_type(values: list[str]) -> tuple[str, list[str]]:
    """Trả về (type, options). options chỉ có khi type == 'select'."""
    non_empty = [v.strip() for v in values if v and v.strip()]
    if not non_empty:
        return "text", []  # cột trống (form mẫu trắng) -> mặc định text

    low = [v.lower() for v in non_empty]

    # checkbox: mọi giá trị đều là dấu tick / rỗng
    if all(v in _BOOL_VALUES for v in low):
        return "checkbox", []

    # date: đa số là ngày
    if sum(_looks_like_date(v) for v in non_empty) >= max(1, len(non_empty) * 0.6):
        return "date", []

    # number: đa số là số
    if sum(_looks_like_number(v) for v in non_empty) >= max(1, len(non_empty) * 0.6):
        return "number", []

    # select: ít giá trị unique
    distinct = sorted(set(non_empty))
    if len(distinct) <= ENUM_MAX_DISTINCT and len(non_empty) > len(distinct):
        return "select", distinct

    return "text", []


# ── Dựng lưới ô từ cells thô ──────────────────────────────────────
def _build_table_grid(table: dict[str, Any]) -> tuple[list[list[str]], list[list[int]]]:
    """
    Trả về (grid_text, grid_span) kích thước rows x cols.
    Ô merge ngang đã bị python-docx nhân bản text ra nhiều cột -> giữ nguyên.
    """
    rows, cols = table["rows"], table["cols"]
    grid = [["" for _ in range(cols)] for _ in range(rows)]
    span = [[1 for _ in range(cols)] for _ in range(rows)]
    for c in table["cells"]:
        r, col = c["row"], c["col"]
        if 0 <= r < rows and 0 <= col < cols:
            # Chuẩn hóa mọi loại khoảng trắng đặc biệt (nbsp, zero-width...) về space thường,
            # giữ dấu tách 2-space (tab/xuống dòng/khoảng trắng dài) để nhận diện tích chọn.
            t = (c["text"] or "").replace("\r", " ").replace("\n", "  ").replace("\t", "  ")
            t = re.sub("[\u00a0\u2000-\u200b\u202f\u205f\u3000\ufeff]", " ", t)
            t = re.sub("[\uf000-\uf0ff]", "  ", t)
            grid[r][col] = re.sub(r" {3,}", "  ", t).strip()
            span[r][col] = c.get("grid_span", 1) or 1
    return grid, span


def _row_distinct_nonempty(row: list[str]) -> list[str]:
    seen, out = set(), []
    for v in row:
        if v and v not in seen:
            seen.add(v)
            out.append(v)
    return out


def _is_section_row(row: list[str], cols: int) -> bool:
    """Hàng gộp ngang gần hết bề rộng, chỉ 1 nhãn -> tiêu đề nhóm (section)."""
    distinct = _row_distinct_nonempty(row)
    filled = sum(1 for v in row if v)
    return len(distinct) == 1 and filled >= cols - 1 and cols >= 3


def _is_header_row(row: list[str], cols: int) -> bool:
    """Hàng nhãn cột: mọi cột đều có text và có > 1 nhãn khác nhau."""
    if any(v == "" for v in row):
        return False
    return len(_row_distinct_nonempty(row)) >= HEADER_MIN_DISTINCT


def _is_boilerplate_table(grid: list[list[str]], rows: int, cols: int) -> bool:
    """
    True nếu bảng là letterhead (tên BV/quốc hiệu) hoặc khối chữ ký cuối form
    — không phải bảng dữ liệu. Bảo thủ: chỉ bỏ bảng NHỎ + có từ khóa đặc trưng,
    không đụng bảng dữ liệu thật (nhiều hàng hoặc có header nhiều nhãn).
    """
    header = _header_block(grid, cols)
    header_labels = len(_row_distinct_nonempty(grid[header[-1]])) if header else 0
    is_real_data = rows >= 4 or header_labels >= 3
    if is_real_data:
        return False
    all_text = " ".join(c for row in grid for c in row).lower()
    return any(kw in all_text for kw in _SIGNATURE_KW) or any(kw in all_text for kw in _LETTERHEAD_KW)


def _header_block(grid: list[list[str]], cols: int) -> list[int]:
    """Các hàng header liên tiếp ở đầu bảng."""
    header = []
    for r in range(len(grid)):
        if _is_header_row(grid[r], cols):
            header.append(r)
        else:
            break
    return header


def _header_signature(grid: list[list[str]], header_rows: list[int]) -> tuple:
    return tuple(tuple(grid[r]) for r in header_rows)


# ── Ghép nhãn header đa tầng thành nhãn cột lá ────────────────────
def _leaf_labels(grid: list[list[str]], header_rows: list[int], cols: int) -> list[str]:
    labels = []
    for col in range(cols):
        parts = []
        for r in header_rows:
            txt = grid[r][col]
            if txt and (not parts or parts[-1] != txt):
                parts.append(txt)
        label = " - ".join(parts) if parts else f"Cột {col + 1}"
        labels.append(re.sub(r"\s+", " ", label).strip())
    return labels


# ── Chuẩn hóa 1 nhóm bảng (đã gộp) thành field repeatable_table ───
def _normalize_logical_table(
    group: list[dict[str, Any]],
    title: str,
    used_keys: set[str],
) -> dict[str, Any]:
    base = group[0]
    grid0, _ = _build_table_grid(base)
    cols = base["cols"]
    header_rows = _header_block(grid0, cols)
    header_sig = _header_signature(grid0, header_rows) if header_rows else ()

    # Gom toàn bộ data rows từ tất cả bảng trong nhóm
    data_rows: list[list[str]] = []
    excluded: list[dict[str, Any]] = []
    sections: list[dict[str, Any]] = []

    for ti, tbl in enumerate(group):
        grid, _span = _build_table_grid(tbl)
        # Bảng đầu bỏ qua header_rows; các bảng sau bỏ qua header lặp lại ở đầu
        skip = set(header_rows) if ti == 0 else set(range(len(_header_block(grid, cols))))
        for r in range(len(grid)):
            if r in skip:
                continue
            row = grid[r]
            if all(v == "" for v in row):
                continue  # hàng trống hoàn toàn của form mẫu -> giữ làm data trống? bỏ để đỡ nhiễu
            if _is_section_row(row, cols):
                sections.append({"label": _row_distinct_nonempty(row)[0], "source_table": tbl["index"], "row": r})
                excluded.append({"reason": "section", "source_table": tbl["index"], "row": r, "texts": row})
                continue
            # Rule 3: header lặp giữa bảng
            if header_sig and tuple(row) in {tuple(h) for h in ([grid0[x] for x in header_rows])}:
                excluded.append({"reason": "repeated_header", "source_table": tbl["index"], "row": r, "texts": row})
                continue
            data_rows.append(row)

    # Nhãn cột lá + suy kiểu từ data
    labels = _leaf_labels(grid0, header_rows, cols) if header_rows else [f"Cột {i+1}" for i in range(cols)]
    columns = []
    col_keys: set[str] = set()
    for col in range(cols):
        col_values = [dr[col] for dr in data_rows if col < len(dr)]
        ftype, options = infer_column_type(col_values)
        # Key ưu tiên nhãn con (tầng header sâu nhất) cho ngắn gọn, ý nghĩa
        child = grid0[header_rows[-1]][col] if header_rows else ""
        key_source = child or labels[col]
        ckey = _unique_key(slugify_key(key_source, f"col_{col+1}"), col_keys)
        column = {"key": ckey, "label": labels[col], "type": ftype}
        if options:
            column["options"] = options
        columns.append(column)

    field_key = _unique_key(slugify_key(title, "bang_du_lieu"), used_keys)
    return {
        "key": field_key,
        "label": title,
        "type": "repeatable_table",
        "required": False,
        "options": [],
        "columns": columns,
        # metadata để admin audit
        "_meta": {
            "source_tables": [t["index"] for t in group],
            "merged": len(group) > 1,
            "header_rows": len(header_rows),
            "data_row_count": len(data_rows),
            "excluded_rows": excluded,
            "sections": sections,
        },
    }


# Ký tự ô tích trong Word (checkbox / wingdings)
_CHECKBOX_CHARS = "☐□⬜▢◻◯○✔✓☑"
_OPT_SPLIT_RE = re.compile(r"[" + _CHECKBOX_CHARS + r"]|\s{2,}|\t|\s/\s")


def detect_choice(text: str) -> tuple[str, str, list[str]]:
    """
    Nhận diện field dạng tích chọn: 'Nhãn: Lựa chọn A   Lựa chọn B   Lựa chọn C'.
    Trả (label, type, options). type='select' nếu có 2-6 lựa chọn, ngược lại '' (không phải).
    """
    if ":" in text:
        label, after = text.split(":", 1)
    elif any(c in text for c in _CHECKBOX_CHARS):
        label, after = "", text
    else:
        return text, "", []

    label = re.sub(r"\s+", " ", label).strip()
    # Nhãn phải ngắn gọn — tránh cả câu / nhiều field dồn vào 1 ô bị tách nhầm
    if len(label) > 40:
        return text, "", []

    opts = []
    for p in _OPT_SPLIT_RE.split(after):
        p = p.strip().strip(":.–-…").strip()   # strip mọi khoảng trắng (kể cả nbsp) rồi dấu
        if 1 <= len(p) <= 25 and _HAS_LETTER_RE.search(p):
            opts.append(p)
    # loại trùng, giữ thứ tự
    seen = set(); uniq = []
    for o in opts:
        if o.lower() not in seen:
            seen.add(o.lower()); uniq.append(o)

    if 2 <= len(uniq) <= 6:
        return label or text.strip(), "select", uniq
    return text, "", []


# ── Bảng "bố cục" (không có hàng tiêu đề) -> field phẳng theo từng ô nhãn ──
def table_to_flat_fields(grid: list[list[str]], rows: int, cols: int, used_keys: set[str]) -> list[dict[str, Any]]:
    """
    Với bảng KHÔNG nhận được hàng tiêu đề (form bố cục: mỗi hàng là 1-vài nhãn
    'Thời gian:', 'Mã tài liệu'...), coi mỗi ô nhãn là 1 field phẳng thay vì cột 'Cột N'.
    """
    fields: list[dict[str, Any]] = []
    seen: set[str] = set()

    for r in range(rows):
        for raw in _row_distinct_nonempty(grid[r]):
            txt = raw.strip()
            if not txt:
                continue
            # bỏ dòng ngày ký / letterhead / chữ ký
            low_full = txt.lower()
            if _DATELINE_RE.search(txt):
                continue
            if any(kw in low_full for kw in _SIGNATURE_KW) or any(kw in low_full for kw in _LETTERHEAD_KW):
                continue

            # Thử nhận diện tích chọn: 'Nhãn: A  B  C' -> select
            clabel, ctype, coptions = detect_choice(txt)
            if ctype == "select":
                label = clabel
                options = coptions
                ftype = "select"
            else:
                # nhãn = phần trước dấu ':' (nếu ngắn) hoặc trước chuỗi chấm
                label = txt
                if ':' in label:
                    head = label.split(':', 1)[0].strip()
                    if 2 <= len(head) <= 45:
                        label = head
                label = _DOTLINE_RE.split(label)[0].strip(' :–-').strip()
                options = []
                asc = _strip_vietnamese(label.lower())
                ftype = "date" if ("ngay" in asc or "thoi gian" in asc) else "text"

            label = re.sub(r"\s+", " ", label).strip()
            if not label or len(label) > 60 or not _HAS_LETTER_RE.search(label):
                continue
            low = label.lower()
            if low in seen:
                continue
            seen.add(low)

            fields.append({
                "key": _unique_key(slugify_key(label, "field"), used_keys),
                "label": label,
                "type": ftype,
                "required": False,
                "options": options,
                "columns": [],
            })

    return fields


# ── Bóc field từ đoạn văn có chỗ điền (Label: …………) ─────────────
def extract_paragraph_fields(paragraphs: list[dict[str, Any]], used_keys: set[str]) -> list[dict[str, Any]]:
    """
    Nhận diện field dạng 'Nhãn: …………' trong paragraph.
    Tách được nhiều field/dòng ('Bộ phận……Khoa:……' -> 2 field) và bỏ trùng
    (form hay lặp lại 2 lần trong 1 file).
    """
    fields: list[dict[str, Any]] = []
    seen: set[str] = set()

    for p in paragraphs:
        text = p.get("text", "")
        if not _DOTLINE_RE.search(text):
            continue  # không có chỗ điền -> tiêu đề/ghi chú, bỏ qua

        # Dòng ký "…, ngày … tháng … năm …" -> 1 field ngày duy nhất (không tách vụn)
        if _DATELINE_RE.search(text):
            if "ngay_lap" not in {f["key"] for f in fields}:
                fields.append({
                    "key": _unique_key("ngay_lap", used_keys),
                    "label": "Ngày lập / ký",
                    "type": "date", "required": False, "options": [], "columns": [],
                })
            continue

        for seg in _DOTLINE_RE.split(text):
            label = seg.strip().strip(":").strip("-").strip("–").strip()
            # loại nhãn rỗng, quá dài (là câu chứ không phải field), không có chữ cái
            if not label or len(label) > 60 or not _HAS_LETTER_RE.search(label):
                continue
            norm = label.lower()
            if norm in seen:
                continue
            seen.add(norm)

            low = _strip_vietnamese(norm)
            ftype = "date" if ("ngay" in low or "thoi gian" in low) else "text"
            fields.append({
                "key": _unique_key(slugify_key(label, "field"), used_keys),
                "label": label,
                "type": ftype,
                "required": False,
                "options": [],
                "columns": [],
            })

    return fields


# ── API chính ─────────────────────────────────────────────────────
def normalize_structure(raw: dict[str, Any]) -> dict[str, Any]:
    """
    Nhận raw {paragraphs, tables} -> trả {suggested_fields, notes}.
    suggested_fields dùng đúng shape mà Livewire SchemaReview mong đợi.
    """
    tables = raw.get("tables", [])
    paragraphs = raw.get("paragraphs", [])
    notes: list[str] = []

    used_keys: set[str] = set()

    # Field dạng đoạn văn ('Tên thiết bị: ……', 'Bộ phận…Khoa:…') — đứng trước bảng
    para_fields = extract_paragraph_fields(paragraphs, used_keys)
    if para_fields:
        notes.append(f"Đã bóc {len(para_fields)} field từ đoạn văn có chỗ điền (…).")

    if not tables:
        if not para_fields:
            notes.append("Không tìm thấy bảng hay ô điền nào trong file.")
        return {"suggested_fields": para_fields, "notes": notes}

    # Rule 1: gộp bảng liên tiếp có header trùng nhau
    groups: list[list[dict[str, Any]]] = []
    for tbl in tables:
        if tbl["cols"] == 0 or tbl["rows"] == 0:
            continue
        if groups:
            prev = groups[-1][0]
            grid_prev, _ = _build_table_grid(prev)
            grid_cur, _ = _build_table_grid(tbl)
            hp = _header_block(grid_prev, prev["cols"])
            hc = _header_block(grid_cur, tbl["cols"])
            same = (
                prev["cols"] == tbl["cols"]
                and hp and hc
                and _header_signature(grid_prev, hp) == _header_signature(grid_cur, hc)
            )
            if same:
                groups[-1].append(tbl)
                continue
        groups.append([tbl])

    merged_count = sum(1 for g in groups if len(g) > 1)
    if merged_count:
        notes.append(f"Đã tự động gộp {sum(len(g) for g in groups if len(g) > 1)} bảng bị ngắt trang thành {merged_count} bảng logic.")

    # Tiêu đề bảng: dùng paragraph gần nhất phía trên (nếu có), fallback theo thứ tự
    doc_title = paragraphs[0]["text"] if paragraphs else "Bảng dữ liệu"

    # Bỏ bảng letterhead / khối chữ ký khỏi gợi ý (bảo thủ)
    data_groups = []
    skipped_boiler = 0
    for group in groups:
        g0 = group[0]
        grid, _ = _build_table_grid(g0)
        if _is_boilerplate_table(grid, g0["rows"], g0["cols"]):
            skipped_boiler += 1
            continue
        data_groups.append(group)
    if skipped_boiler:
        notes.append(f"Đã bỏ {skipped_boiler} khối không phải dữ liệu (letterhead / chữ ký).")

    suggested_fields: list[dict[str, Any]] = list(para_fields)  # field đoạn văn trước
    n_data = len(data_groups)
    flat_from_tables = 0
    for gi, group in enumerate(data_groups):
        g0 = group[0]
        grid0, _ = _build_table_grid(g0)
        header_rows = _header_block(grid0, g0["cols"])
        if not header_rows:
            # Bảng bố cục (không có tiêu đề) -> field phẳng theo hàng, tránh 'Cột N'
            flat = table_to_flat_fields(grid0, g0["rows"], g0["cols"], used_keys)
            suggested_fields.extend(flat)
            flat_from_tables += len(flat)
        else:
            title = doc_title if n_data == 1 else f"{doc_title} ({gi + 1})"
            suggested_fields.append(_normalize_logical_table(group, title, used_keys))
    if flat_from_tables:
        notes.append(f"Đã chuyển {flat_from_tables} ô nhãn từ bảng bố cục thành field phẳng.")

    total_excluded = sum(len(f.get("_meta", {}).get("excluded_rows", [])) for f in suggested_fields)
    if total_excluded:
        notes.append(f"Đã đánh dấu {total_excluded} hàng nghi là header lặp / tiêu đề nhóm (xem _meta.excluded_rows).")

    return {"suggested_fields": suggested_fields, "notes": notes}

<?php

namespace App\Services;

use App\Models\FormTemplate;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

/**
 * Dựng giao diện nhập "giống bản gốc": convert template .docx -> HTML (LibreOffice),
 * biến ${key} thành ô nhập / ${chk_*} thành ô tích ngay trên layout.
 * Điền xong ghép ngược vào .docx bằng TemplateProcessor.
 */
class HtmlFormService
{
    private function cacheDir(): string
    {
        $dir = storage_path('app/html-cache');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    /** Convert docx -> HTML thô (cache theo file + mtime). */
    private function rawHtml(FormTemplate $t): string
    {
        $src = Storage::disk('local')->path($t->file_goc_path);
        if (! is_file($src)) {
            throw new RuntimeException('Không thấy file mẫu.');
        }
        $cache = $this->cacheDir() . '/' . md5($src . filemtime($src)) . '.html';
        if (! is_file($cache)) {
            $work = $this->cacheDir() . '/w_' . $t->id;
            @mkdir($work, 0775, true);
            $tmp = $work . '/t.docx';
            copy($src, $tmp);
            exec('HOME=' . escapeshellarg($work) . ' soffice --headless --convert-to html --outdir ' . escapeshellarg($work) . ' ' . escapeshellarg($tmp) . ' 2>&1');
            $html = @file_get_contents($work . '/t.html');
            if ($html === false) {
                throw new RuntimeException('Convert HTML thất bại.');
            }
            file_put_contents($cache, $html);
            @unlink($tmp); @unlink($work . '/t.html'); @rmdir($work);
        }
        return file_get_contents($cache);
    }

    /** HTML đã gắn ô nhập inline (style bản gốc + input cho ${key}), biết kiểu theo schema. */
    public function formHtml(FormTemplate $t, array $fields = [], array $tableRows = []): string
    {
        $raw  = $this->rawHtml($t);
        $meta = $this->buildMeta($fields);

        preg_match('/<style[^>]*>(.*?)<\/style>/is', $raw, $ms);
        $style = $ms[1] ?? '';
        preg_match('/<body[^>]*>(.*?)<\/body>/is', $raw, $mb);
        $body = $mb[1] ?? $raw;

        // Bỏ ảnh do soffice tách ra file rời (logo/letterhead) — src trỏ file không phục vụ
        // được nên hiện icon vỡ; xoá cho form gọn "chuẩn" hơn.
        $body = preg_replace('/<img\b[^>]*>/i', '', $body);

        // (0) BẢNG: nhân bản dòng mẫu theo số dòng + nút +/✕ (bỏ bảng đã ẩn)
        foreach ($fields as $f) {
            if (($f['type'] ?? '') === 'repeatable_table' && empty($f['hidden'])) {
                $body = $this->expandTable($body, $f, max(1, (int) ($tableRows[$f['key']] ?? 1)));
            }
        }

        // (0.5) BỘ BA "ngày __ tháng __ năm __": tách 3 ô SỐ nhỏ (ngày/tháng/năm),
        // KHÔNG để 1 date-picker ôm nguyên "07/15/2026" chèn vào chỗ "ngày".
        $body = preg_replace_callback(
            '/\$\{([a-z0-9_]+)\}\s*(tháng)\s*\$\{([a-z0-9_]+)\}\s*(năm)\s*\$\{([a-z0-9_]+)\}/iu',
            function ($m) use ($meta) {
                foreach ([1, 3, 5] as $i) {
                    if (! empty($meta[$m[$i]]['hidden'])) {
                        return $m[0];   // có ô bị ẩn -> để vòng chung xử lý
                    }
                }
                return $this->dayNumInput($m[1], 'ngày', 46) . ' ' . $m[2] . ' '
                    . $this->dayNumInput($m[3], 'tháng', 46) . ' ' . $m[4] . ' '
                    . $this->dayNumInput($m[5], 'năm', 62);
            },
            $body
        );

        // Thay ${key} -> ô nhập ĐÚNG KIỂU + gợi ý
        $body = preg_replace_callback('/\$\{([a-z0-9_]+)\}/i', function ($m) use ($meta) {
            $key  = $m[1];
            $info = $meta[$key] ?? ['type' => str_starts_with($key, 'chk_') ? 'chk' : 'text', 'label' => $this->humanize($key), 'group' => ''];
            if (! empty($info['hidden'])) {
                return '';   // field đã ẩn -> xoá placeholder, không render ô nhập
            }
            // ${chk_*} LUÔN là ô tích (docx xuất ☒/☐) — kể cả schema lỡ gán 'text'
            $type = str_starts_with($key, 'chk_') ? 'chk' : $info['type'];
            $hint = htmlspecialchars($info['label'] ?? '');

            if ($type === 'chk') {
                $title = htmlspecialchars(trim(($info['group'] ?? '') . ' · ' . ($info['label'] ?? ''), ' ·'));
                return '<input type="checkbox" wire:model="vals.' . $key . '" class="qf-chk" title="' . $title . '">';
            }
            if ($type === 'date') {
                return '<input type="date" wire:model="vals.' . $key . '" class="qf-in qf-date" title="' . $hint . '">';
            }
            if ($type === 'number') {
                return '<input type="number" inputmode="decimal" wire:model.blur="vals.' . $key . '" class="qf-in" placeholder="' . $hint . '">';
            }
            return '<input type="text" wire:model.blur="vals.' . $key . '" class="qf-in" placeholder="' . $hint . '">';
        }, $body);

        // Khổ giấy thật từ @page: đổi inch/cm/mm -> px (96dpi) để tờ giấy đúng bề ngang bản gốc.
        // Đây là bề rộng TỐI THIỂU; nếu trong trang có bảng rộng hơn (col width cố định)
        // thì tờ giấy tự nở ôm lấy bảng — y như mở trong Word, bàn xám cuộn ngang.
        $pageW = 816;
        if (preg_match('/@page[^}]*\bsize:\s*([\d.]+)(in|cm|mm)?\s+([\d.]+)/i', $style, $mp)) {
            $unit = strtolower($mp[2] ?? 'in');
            $dpi  = $unit === 'cm' ? 37.795 : ($unit === 'mm' ? 3.7795 : 96);
            $pageW = (int) round((float) $mp[1] * $dpi);
        }

        // Đóng gói: nền xám + tờ giấy trắng (.qf-page) canh giữa, giống mở file Word
        return '<div class="qf-doc">'
            . '<style>' . $this->scopeStyle($style) . '</style>'
            . '<div class="qf-page" style="min-width:' . $pageW . 'px">' . $body . '</div>'
            . '</div>';
    }

    /** Map placeholder key -> kiểu/nhãn từ schema (để render đúng ô + gợi ý). */
    private function buildMeta(array $fields): array
    {
        $meta = [];
        foreach ($fields as $f) {
            $type = $f['type'] ?? 'text';
            $hid  = ! empty($f['hidden']);
            $grp = $this->cleanLabel($f['label'] ?? '');
            if ($type === 'repeatable_table') {
                foreach ($f['columns'] ?? [] as $c) {
                    $meta[$c['key']] = ['type' => $c['type'] ?? 'text', 'label' => $this->cleanLabel($c['label'] ?? ''), 'group' => $grp, 'hidden' => $hid];
                }
            } elseif (! empty($f['option_ph'])) {
                foreach ($f['option_ph'] as $optText => $ph) {
                    $meta[$ph] = ['type' => 'chk', 'label' => $this->cleanLabel($optText), 'group' => $grp, 'hidden' => $hid];
                }
            } else {
                $meta[$f['key']] = ['type' => $type, 'label' => $this->cleanLabel($f['label'] ?? ''), 'group' => '', 'hidden' => $hid];
            }
        }
        return $meta;
    }

    private function humanize(string $key): string
    {
        $s = trim(preg_replace('/[_\-]+/', ' ', $key));
        return $s !== '' ? mb_convert_case($s, MB_CASE_TITLE, 'UTF-8') : $key;
    }

    /** Bỏ mọi ${...} lẫn trong nhãn (injector đôi khi nuốt placeholder trước làm nhãn). */
    private function cleanLabel(?string $label): string
    {
        return trim(preg_replace('/\$\{[a-z0-9_]+\}/i', '', (string) $label));
    }

    /** Ô số nhỏ cho ngày/tháng/năm (căn giữa, rộng cố định). */
    private function dayNumInput(string $key, string $ph, int $w): string
    {
        return '<input type="number" inputmode="numeric" wire:model.blur="vals.' . $key
            . '" class="qf-in qf-dnum" placeholder="' . $ph . '" title="' . $ph
            . '" style="width:' . $w . 'px;min-width:' . $w . 'px">';
    }

    private static function isStt(array $col): bool
    {
        $k = mb_strtolower(trim($col['key'] ?? ''), 'UTF-8');
        $l = mb_strtolower(trim($col['label'] ?? ''), 'UTF-8');
        return in_array($k, ['stt', 'tt', 'so_tt', 'sott'], true) || preg_match('/^(stt|tt|số\s*tt|#)$/u', $l);
    }

    /** Nhân bản dòng mẫu của 1 bảng thành $count dòng (bind theo chỉ số) + nút +/✕. */
    private function expandTable(string $body, array $field, int $count): string
    {
        $tkey = $field['key'];
        $cols = $field['columns'] ?? [];
        if (empty($cols)) {
            return $body;
        }
        $anchor = '${' . $cols[0]['key'] . '}';

        // Tìm khối <table> chứa dòng mẫu
        if (! preg_match_all('/<table\b.*?<\/table>/is', $body, $tm)) {
            return $body;
        }
        $tableBlock = null;
        foreach ($tm[0] as $tb) {
            if (str_contains($tb, $anchor)) {
                $tableBlock = $tb;
                break;
            }
        }
        if ($tableBlock === null) {
            return $body;
        }

        // Dòng mẫu trong bảng đó
        preg_match_all('/<tr\b.*?<\/tr>/is', $tableBlock, $rm);
        $tplRow = null;
        foreach ($rm[0] as $r) {
            if (str_contains($r, $anchor)) {
                $tplRow = $r;
                break;
            }
        }
        if ($tplRow === null) {
            return $body;
        }

        $rowsHtml = '';
        for ($i = 0; $i < $count; $i++) {
            $r = $tplRow;
            foreach ($cols as $c) {
                $ph = '${' . $c['key'] . '}';
                if (self::isStt($c)) {
                    $cell = '<span class="qf-stt">' . ($i + 1) . '</span>';
                } else {
                    $type = in_array($c['type'] ?? 'text', ['date', 'number']) ? $c['type'] : 'text';
                    $lbl  = htmlspecialchars($c['label'] ?? '');
                    $cls  = $type === 'date' ? 'qf-in qf-date' : 'qf-in';
                    $cell = '<input type="' . $type . '" wire:model' . ($type === 'date' ? '' : '.blur')
                        . '="vals.t.' . $tkey . '.' . $i . '.' . $c['key'] . '" class="' . $cls . '" placeholder="' . $lbl . '">';
                }
                $r = str_replace($ph, $cell, $r);
            }
            // ✕ NỔI RA NGOÀI phải dòng (absolute, không chiếm chỗ trong ô) — nhét vào ô cuối
            $del = '<button type="button" wire:click="removeRow(\'' . $tkey . '\',' . $i . ')" class="qf-del" title="Xoá dòng">✕</button>';
            $r   = preg_replace('/<\/td>(\s*)<\/tr>\s*$/is', $del . '</td>$1</tr>', $r, 1);
            $rowsHtml .= $r;
        }

        $newTable = str_replace($tplRow, $rowsHtml, $tableBlock);
        // "+ Thêm dòng" ĐẶT NGOÀI bảng (ngay dưới)
        $addBtn = '<div class="qf-addrow"><button type="button" wire:click="addRow(\'' . $tkey . '\')">+ Thêm dòng</button></div>';

        return str_replace($tableBlock, $newTable . $addBtn, $body);
    }

    /** Giới hạn CSS bản gốc trong .qf-doc để không phá app + thêm style ô nhập. */
    private function scopeStyle(string $css): string
    {
        // bỏ selector body/html toàn cục để không phá app
        $css = preg_replace('/\b(body|html)\b\s*\{[^}]*\}/i', '', $css);
        return $css . '
            /* ===== Nền xám + tờ giấy A4 trắng, canh giữa, bóng đổ — giống mở Word ===== */
            .qf-doc{overflow-x:auto;background:#54565a;padding:26px 18px;
                background-image:linear-gradient(#54565a,#54565a)}
            /* Tờ giấy nở theo nội dung: min-width = khổ giấy thật (set inline), nhưng
               fit-content cho phép nở thêm khi có bảng rộng hơn -> hết tràn ra ngoài giấy */
            .qf-page{background:#fff;width:fit-content;max-width:none;margin:0 auto;
                padding:64px 76px;box-sizing:border-box;
                box-shadow:0 2px 5px rgba(0,0,0,.30),0 10px 34px rgba(0,0,0,.22);
                font-family:"Times New Roman",Times,serif;font-size:15px;line-height:1.55;
                color:#000;-webkit-font-smoothing:antialiased}
            .qf-page p{margin:0 0 .18em}
            /* KHÔNG ép bảng co (col width cố định không co được -> tràn); để giấy nở ôm bảng */
            .qf-page table{max-width:none}
            .qf-page img{max-width:100%;height:auto}
            /* ===== Ô nhập: hoà vào văn bản (mực xanh, blank tô vàng nhạt) ===== */
            .qf-in{font-family:inherit;font-size:inherit;line-height:inherit;color:#1a4bd6;
                font-weight:500;border:0;border-bottom:1px solid #b9bcc4;
                background:rgba(255,241,158,.40);min-width:56px;padding:0 3px;
                border-radius:2px 2px 0 0;transition:background .12s,border-color .12s}
            .qf-in:hover{background:rgba(255,231,120,.62)}
            .qf-in:focus{outline:none;background:#fff3b0;border-bottom-color:#e0a500}
            .qf-in::placeholder{color:#9aa0ad;font-style:italic;font-weight:400;font-size:.82em}
            .qf-date{min-width:120px}
            .qf-dnum{text-align:center;padding:0 2px}
            .qf-dnum::-webkit-inner-spin-button,.qf-dnum::-webkit-outer-spin-button{-webkit-appearance:none;margin:0}
            .qf-date::-webkit-calendar-picker-indicator{opacity:.45;cursor:pointer}
            .qf-chk{width:15px;height:15px;accent-color:#1a4bd6;vertical-align:middle;cursor:pointer;margin:0 1px}
            .qf-stt{font-weight:600}
            /* Nút xoá dòng nổi ngoài phải; nút thêm dòng dạng chữ nhẹ */
            .qf-page td{position:relative}
            .qf-del{position:absolute;right:-22px;top:50%;transform:translateY(-50%);opacity:0;
                border:0;background:#fee2e2;color:#dc2626;border-radius:50%;width:18px;height:18px;
                line-height:16px;text-align:center;padding:0;cursor:pointer;font-size:11px;transition:opacity .15s}
            .qf-page tr:hover .qf-del{opacity:1}
            .qf-addrow{margin:6px 0 14px}
            .qf-addrow button{border:1px dashed #9aa0ad;background:#fafafa;color:#374151;
                border-radius:7px;padding:4px 12px;font-family:system-ui,sans-serif;font-size:12.5px;
                font-weight:600;cursor:pointer}
            .qf-addrow button:hover{background:#f0f0f0;border-color:#6b7280}
            @media(max-width:640px){.qf-doc{padding:12px 8px}.qf-page{padding:32px 20px}}
        ';
    }

    /** Danh sách key placeholder (để khởi tạo vals). */
    public function keys(FormTemplate $t): array
    {
        $tp = new TemplateProcessor(Storage::disk('local')->path($t->file_goc_path));
        return $tp->getVariables();
    }

    /** Ghép giá trị vào .docx: bảng -> cloneRow; text -> setValue; chk_* -> ☒/☐; xoá placeholder sót. */
    public function fill(FormTemplate $t, array $vals, array $fields = []): string
    {
        $tp = new TemplateProcessor(Storage::disk('local')->path($t->file_goc_path));

        // Bảng nhiều dòng
        $tableCols = [];
        foreach ($fields as $f) {
            if (($f['type'] ?? '') !== 'repeatable_table') {
                continue;
            }
            $tkey = $f['key'];
            $cols = $f['columns'] ?? [];
            foreach ($cols as $c) {
                $tableCols[$c['key']] = true;
            }
            $rowData = [];
            foreach (array_values($vals['t'][$tkey] ?? []) as $i => $r) {
                $entry = [];
                foreach ($cols as $c) {
                    $entry[$c['key']] = self::isStt($c) ? (string) ($i + 1) : htmlspecialchars((string) ($r[$c['key']] ?? ''));
                }
                $rowData[] = $entry;
            }
            if ($rowData) {
                try { $tp->cloneRowAndSetValues($tkey, $rowData); } catch (\Throwable $e) {}
            }
        }

        // Text + ô tích (bỏ cột bảng đã xử lý)
        foreach ($tp->getVariables() as $key) {
            if (isset($tableCols[$key])) {
                continue;
            }
            if (str_starts_with($key, 'chk_')) {
                $tp->setValue($key, ! empty($vals[$key]) ? '☒' : '☐');
            } else {
                $tp->setValue($key, htmlspecialchars((string) ($vals[$key] ?? '')));
            }
        }

        // Xoá mọi placeholder còn sót
        foreach ($tp->getVariables() as $leftover) {
            $tp->setValue($leftover, '');
        }

        $out = tempnam(sys_get_temp_dir(), 'qf_') . '.docx';
        $tp->saveAs($out);
        return $out;
    }
}

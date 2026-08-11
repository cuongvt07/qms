<?php

namespace App\Services;

use ZipArchive;

/**
 * Sinh file .xlsx thật (không cần thư viện ngoài) — xlsx chỉ là một file zip chứa XML.
 * Dùng inlineStr nên tiếng Việt không cần bảng sharedStrings, mở bằng Excel / LibreOffice đều đúng.
 *
 *   XlsxWriter::taiVe('the-kho.xlsx', [[
 *       'name'   => 'Thẻ kho',
 *       'title'  => 'THẺ KHO',            // dòng tiêu đề in đậm (không bắt buộc)
 *       'header' => ['Ngày', 'Nhập', 'Xuất'],
 *       'rows'   => [['01/08/2026', 10, 2]],
 *       'widths' => [14, 10, 10],
 *   ]]);
 */
class XlsxWriter
{
    /** Trả về response tải file cho trình duyệt. */
    public static function taiVe(string $fileName, array $sheets)
    {
        $bin = self::build($sheets);

        return response($bin, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length'      => strlen($bin),
            'Cache-Control'       => 'no-store',
        ]);
    }

    public static function build(array $sheets): string
    {
        $sheets = array_values($sheets);
        $tmp    = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip    = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE | ZipArchive::CREATE);

        $n = count($sheets);

        $zip->addFromString('[Content_Types].xml', self::contentTypes($n));
        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml', self::workbook($sheets));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRels($n));
        $zip->addFromString('xl/styles.xml', self::styles());

        foreach ($sheets as $i => $s) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', self::sheet($s));
        }
        $zip->close();

        $bin = file_get_contents($tmp);
        @unlink($tmp);

        return $bin;
    }

    private static function contentTypes(int $n): string
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        for ($i = 1; $i <= $n; $i++) {
            $x .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return $x . '</Types>';
    }

    private static function workbook(array $sheets): string
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        foreach ($sheets as $i => $s) {
            $ten = self::tenSheet($s['name'] ?? ('Sheet' . ($i + 1)));
            $x .= '<sheet name="' . self::esc($ten) . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return $x . '</sheets></workbook>';
    }

    private static function workbookRels(int $n): string
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= $n; $i++) {
            $x .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $i . '.xml"/>';
        }

        return $x . '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    /** 0 = thường, 1 = tiêu đề lớn, 2 = ô header (đậm, nền xám, viền), 3 = ô có viền */
    private static function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="3">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFEFF3F7"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFB0BAC5"/></left><right style="thin"><color rgb="FFB0BAC5"/></right>'
            . '<top style="thin"><color rgb="FFB0BAC5"/></top><bottom style="thin"><color rgb="FFB0BAC5"/></bottom><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private static function sheet(array $s): string
    {
        $header = $s['header'] ?? [];
        $rows   = $s['rows'] ?? [];
        $widths = $s['widths'] ?? [];
        $title  = $s['title'] ?? null;
        $ghiChu = $s['note'] ?? null;                 // các dòng chú thích trước bảng

        $cols = '';
        if ($widths) {
            $cols = '<cols>';
            foreach ($widths as $i => $w) {
                $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>';
            }
            $cols .= '</cols>';
        }

        $r    = 1;
        $body = '';

        if ($title !== null) {
            $body .= '<row r="' . $r . '" ht="20" customHeight="1">' . self::cell(1, $r, $title, 1) . '</row>';
            $r++;
        }
        foreach ((array) $ghiChu as $line) {
            $body .= '<row r="' . $r . '">' . self::cell(1, $r, $line, 0) . '</row>';
            $r++;
        }
        if ($title !== null || $ghiChu) {
            $r++;                                      // chừa một dòng trống
        }

        $dongHeader = 0;
        if ($header) {
            $dongHeader = $r;
            $c = '';
            foreach ($header as $i => $h) {
                $c .= self::cell($i + 1, $r, $h, 2);
            }
            $body .= '<row r="' . $r . '" ht="28" customHeight="1">' . $c . '</row>';
            $r++;
        }
        foreach ($rows as $row) {
            $c = '';
            foreach (array_values((array) $row) as $i => $v) {
                $c .= self::cell($i + 1, $r, $v, 3);
            }
            $body .= '<row r="' . $r . '">' . $c . '</row>';
            $r++;
        }

        // đóng băng dòng header để cuộn vẫn thấy tên cột
        $freeze = $dongHeader
            ? '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . $dongHeader . '" topLeftCell="A' . ($dongHeader + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $freeze . $cols . '<sheetData>' . $body . '</sheetData></worksheet>';
    }

    private static function cell(int $col, int $row, $v, int $style): string
    {
        $ref = self::cot($col) . $row;
        $st  = $style ? ' s="' . $style . '"' : '';

        if ($v === null || $v === '') {
            return '<c r="' . $ref . '"' . $st . '/>';
        }
        // số thật mới ghi dạng số; mã như "26C2472" hay "0001" phải giữ nguyên chuỗi
        if (is_int($v) || is_float($v) || (is_string($v) && preg_match('/^-?\d+(\.\d+)?$/', $v) && $v[0] !== '0')) {
            return '<c r="' . $ref . '"' . $st . '><v>' . $v . '</v></c>';
        }

        return '<c r="' . $ref . '"' . $st . ' t="inlineStr"><is><t xml:space="preserve">' . self::esc((string) $v) . '</t></is></c>';
    }

    private static function cot(int $n): string
    {
        $s = '';
        while ($n > 0) {
            $m = ($n - 1) % 26;
            $s = chr(65 + $m) . $s;
            $n = (int) (($n - $m) / 26);
        }

        return $s;
    }

    /** Excel cấm một số ký tự trong tên sheet và giới hạn 31 ký tự. */
    private static function tenSheet(string $s): string
    {
        return mb_substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', $s), 0, 31);
    }

    private static function esc(string $s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s);

        return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}

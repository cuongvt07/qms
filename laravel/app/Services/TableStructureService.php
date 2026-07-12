<?php

namespace App\Services;

use App\Models\FormTemplateVersion;

/**
 * Đọc CẤU TRÚC BẢNG trong .docx (đã chuẩn hoá macro) để dựng lại đúng bảng ở "Dạng phiếu":
 * xử lý gộp cột (gridSpan) + gộp hàng (vMerge) → giữ đúng tiêu đề cột (gồm cột chia nhỏ 0/5/10)
 * và tiêu đề hàng. Chỉ lấy các bảng CÓ placeholder ${...} (ô cần nhập).
 *
 * Kết quả forVersion(): [
 *   'tables'     => [ ['keys'=>set, 'firstKey'=>k, 'gridW'=>int, 'headerRows'=>int, 'rows'=>[[cell,...]]], ... ],
 *   'keyToTable' => [key => tableIndex],
 * ]
 * cell = ['segments'=>[['t'=>text]|['k'=>key], ...], 'colspan'=>int, 'rowspan'=>int, 'hasKey'=>bool, 'skip'=>bool]
 */
class TableStructureService
{
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    private static array $cache = [];

    public function forVersion(FormTemplateVersion $version): array
    {
        $vid = $version->id;
        if (isset(self::$cache[$vid])) {
            return self::$cache[$vid];
        }
        $out = ['tables' => [], 'keyToTable' => []];
        try {
            $path = app(InlineDocxService::class)->templatePathFor($version);
            $zip  = new \ZipArchive();
            if ($zip->open($path) !== true) {
                return self::$cache[$vid] = $out;
            }
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xml === false) {
                return self::$cache[$vid] = $out;
            }
            $out = $this->parse($xml);
        } catch (\Throwable $e) {
            // hỏng -> không có bảng dựng lại, dùng cách cũ
        }
        return self::$cache[$vid] = $out;
    }

    private function parse(string $xml): array
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xml, LIBXML_PARSEHUGE);
        $ns   = self::NS;
        $tbls = $dom->getElementsByTagNameNS($ns, 'tbl');

        $tables = [];
        $keyToTable = [];
        foreach ($tbls as $tbl) {
            // bỏ bảng lồng nhau (chỉ xử lý bảng cấp cao nhất để tránh trùng)
            if ($this->ancestorIsTbl($tbl)) {
                continue;
            }
            $parsed = $this->parseTable($tbl, $dom);
            if ($parsed === null || empty($parsed['keys'])) {
                continue;
            }
            $idx = count($tables);
            foreach ($parsed['keys'] as $k) {
                if (! isset($keyToTable[$k])) {
                    $keyToTable[$k] = $idx;
                }
            }
            $tables[] = $parsed;
        }
        return ['tables' => $tables, 'keyToTable' => $keyToTable];
    }

    private function ancestorIsTbl(\DOMNode $tbl): bool
    {
        $p = $tbl->parentNode;
        while ($p) {
            if ($p->localName === 'tbl' && $p->namespaceURI === self::NS) {
                return true;
            }
            $p = $p->parentNode;
        }
        return false;
    }

    private function parseTable(\DOMNode $tbl, \DOMDocument $dom): ?array
    {
        $ns = self::NS;
        // Hàng trực tiếp của bảng
        $trs = [];
        foreach ($tbl->childNodes as $c) {
            if ($c->localName === 'tr' && $c->namespaceURI === $ns) {
                $trs[] = $c;
            }
        }
        if (! $trs) {
            return null;
        }

        $rows     = [];   // mỗi hàng: list cell thô
        $keys     = [];
        $firstKey = null;
        $gridW    = 0;

        foreach ($trs as $tr) {
            $col   = 0;
            $cells = [];
            foreach ($tr->childNodes as $tc) {
                if ($tc->localName !== 'tc' || $tc->namespaceURI !== $ns) {
                    continue;
                }
                // text đầy đủ (giữ ${..})
                $text = '';
                foreach ($tc->getElementsByTagNameNS($ns, 't') as $t) {
                    $text .= $t->nodeValue;
                }
                // gridSpan
                $span = 1;
                foreach ($tc->getElementsByTagNameNS($ns, 'gridSpan') as $gs) {
                    $span = max(1, (int) $gs->getAttribute('w:val'));
                    break;
                }
                // vMerge
                $vm = null;
                foreach ($tc->getElementsByTagNameNS($ns, 'vMerge') as $v) {
                    $vm = ($v->getAttribute('w:val') === 'restart') ? 'restart' : 'continue';
                    break;
                }
                // tách segment text / ${key}
                $segments = $this->segments($text);
                $ckeys = [];
                foreach ($segments as $s) {
                    if (isset($s['k'])) {
                        $ckeys[] = $s['k'];
                        $keys[$s['k']] = true;
                        if ($firstKey === null) {
                            $firstKey = $s['k'];
                        }
                    }
                }
                $cells[] = [
                    'segments' => $segments,
                    'colspan'  => $span,
                    'vm'       => $vm,
                    'startCol' => $col,
                    'hasKey'   => ! empty($ckeys),
                    'rowspan'  => 1,
                    'skip'     => false,
                ];
                $col += $span;
            }
            $gridW = max($gridW, $col);
            $rows[] = $cells;
        }

        if (empty($keys)) {
            return null;
        }

        // Tính rowspan cho vMerge=restart, đánh dấu skip cho continue
        $R = count($rows);
        for ($r = 0; $r < $R; $r++) {
            foreach ($rows[$r] as &$cell) {
                if ($cell['vm'] === 'restart') {
                    $rs = 1;
                    for ($r2 = $r + 1; $r2 < $R; $r2++) {
                        $cont = $this->cellAtCol($rows[$r2], $cell['startCol']);
                        if ($cont && $cont['vm'] === 'continue') {
                            $rs++;
                        } else {
                            break;
                        }
                    }
                    $cell['rowspan'] = $rs;
                } elseif ($cell['vm'] === 'continue') {
                    $cell['skip'] = true;
                }
            }
            unset($cell);
        }

        // Số hàng tiêu đề = các hàng ĐẦU không có placeholder
        $headerRows = 0;
        foreach ($rows as $cells) {
            $hasKey = false;
            foreach ($cells as $c) {
                if ($c['hasKey']) {
                    $hasKey = true;
                    break;
                }
            }
            if ($hasKey) {
                break;
            }
            $headerRows++;
        }

        return [
            'keys'       => array_keys($keys),
            'firstKey'   => $firstKey,
            'gridW'      => $gridW,
            'headerRows' => $headerRows,
            'rows'       => $rows,
        ];
    }

    private function cellAtCol(array $cells, int $col): ?array
    {
        foreach ($cells as $c) {
            if ($c['startCol'] === $col) {
                return $c;
            }
        }
        return null;
    }

    /** Tách chuỗi thành đoạn text và ${key}. */
    private function segments(string $text): array
    {
        $out   = [];
        $parts = preg_split('/(\$\{[a-z0-9_]+\})/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $p) {
            if (preg_match('/^\$\{([a-z0-9_]+)\}$/i', $p, $m)) {
                $out[] = ['k' => $m[1]];
            } else {
                $t = trim(preg_replace('/\s+/u', ' ', $p));
                if ($t !== '') {
                    $out[] = ['t' => $t];
                }
            }
        }
        return $out;
    }
}

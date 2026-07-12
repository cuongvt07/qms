<?php

namespace App\Services;

use App\Models\FormTemplateVersion;
use Illuminate\Support\Facades\Storage;

/**
 * Chèn thêm placeholder ${key} vào file .docx GỐC theo vị trí người dùng click ở màn "giống bản gốc".
 * KHÔNG sửa file gốc: dựng một bản .docx phái sinh (cache theo nội dung) rồi phục vụ cho cả render lẫn xuất.
 * Vị trí chèn được neo theo đúng đoạn chữ đã click (nodeText + offset) nên không lệch dù bản render đã thay ${..} bằng ô.
 */
class InlineDocxService
{
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * Trả về đường dẫn file .docx dùng làm "template" cho version:
     *  - nếu version không có ô thêm inline  -> chính file gốc.
     *  - nếu có -> bản phái sinh đã chèn ${..} (cache tại storage/app/inline_aug).
     */
    public function templatePathFor(FormTemplateVersion $version): string
    {
        $orig = Storage::disk('local')->path($version->formTemplate->file_goc_path);
        if (! is_file($orig)) {
            return $orig;
        }
        $added = $this->addedFields($version);

        $dir = Storage::disk('local')->path('inline_aug');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        // hash gồm cả mtime file gốc -> đè file mới => cache tự làm lại. 'n2' = phiên bản chuẩn hoá macro.
        $hash  = substr(md5(json_encode($added) . '|' . @filemtime($orig) . '|n2'), 0, 12);
        $cache = $dir . DIRECTORY_SEPARATOR . $version->id . '_' . $hash . '.docx';

        if (! is_file($cache)) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . $version->id . '_*.docx') ?: [] as $old) {
                @unlink($old);
            }
            $this->augment($orig, $added, $cache);
        }

        return $cache;
    }

    /** Danh sách field được thêm inline (có 'added_inline'). */
    public function addedFields(FormTemplateVersion $version): array
    {
        return array_values(array_filter(
            $version->fields,
            fn ($f) => ! empty($f['added_inline'])
        ));
    }

    /**
     * Copy file gốc, CHUẨN HOÁ macro bị Word cắt rời ở mọi part (document/header/footer),
     * rồi chèn các placeholder người dùng thêm vào document.xml. Ghi ra $dest.
     */
    private function augment(string $src, array $added, string $dest): void
    {
        if (! @copy($src, $dest)) {
            return;
        }
        $zip = new \ZipArchive();
        if ($zip->open($dest) !== true) {
            return;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (! preg_match('#^word/(document|header\d*|footer\d*)\.xml$#', $name)) {
                continue;
            }
            $xml = $zip->getFromName($name);
            if ($xml === false) {
                continue;
            }
            $fixed = $this->fixBrokenMacros($xml);   // gộp ${..} bị cắt + bỏ khoảng trắng thừa trong macro

            // Chỉ document.xml mới chèn ô người dùng thêm
            if ($name === 'word/document.xml' && ! empty($added)) {
                $dom = new \DOMDocument();
                $dom->preserveWhiteSpace = true;
                $dom->formatOutput       = false;
                $dom->loadXML($fixed, LIBXML_PARSEHUGE);
                foreach ($added as $f) {
                    $a = $f['added_inline'];
                    if (($a['placement'] ?? 'inline') === 'below') {
                        $this->insertBelow($dom, (string) ($a['paraText'] ?? ''), (string) ($a['nodeText'] ?? ''), (string) $f['key']);
                    } else {
                        $this->insertPlaceholder(
                            $dom,
                            (string) ($a['paraText'] ?? ''),
                            (string) ($a['nodeText'] ?? ''),
                            (int) ($a['nodeOffset'] ?? 0),
                            (int) ($a['nodeOccur'] ?? 0),
                            (string) $f['key']
                        );
                    }
                }
                $fixed = $dom->saveXML();
            }

            if ($fixed !== $xml) {
                $zip->addFromString($name, $fixed);
            }
        }

        $zip->close();
    }

    /**
     * Gộp macro ${..} bị Word cắt rời qua nhiều run (có tag XML xen giữa) về 1 chuỗi liền,
     * và bỏ mọi khoảng trắng bên trong ${...} (key placeholder không có dấu cách).
     * Macro liền mạch sẵn -> giữ nguyên. Không đụng vào text thường (chỉ khớp đúng dạng ${...}).
     */
    private function fixBrokenMacros(string $xml): string
    {
        return preg_replace_callback('/\$(?:<[^>]+>|\s)*\{[^}]*\}/u', function ($m) {
            $inner = strip_tags($m[0]);                 // bỏ các tag </w:t></w:r><w:r><w:t> xen giữa
            return preg_replace('/\s+/u', '', $inner);  // bỏ khoảng trắng thừa: "${chu_nhiem_khoa }" -> "${chu_nhiem_khoa}"
        }, $xml) ?? $xml;
    }

    /** Tìm đoạn theo text (đã bỏ ${..}); fallback đoạn chứa run có text == nodeText. */
    private function findParagraph(\DOMDocument $dom, string $paraText, string $nodeText): ?\DOMNode
    {
        $ns   = self::NS;
        $ps   = $dom->getElementsByTagNameNS($ns, 'p');
        $want = $this->norm($paraText);
        foreach ($ps as $p) {
            if ($want !== '' && $this->norm($this->strip($this->pText($p))) === $want) {
                return $p;
            }
        }
        if ($nodeText !== '') {
            foreach ($ps as $p) {
                foreach ($p->getElementsByTagNameNS($ns, 't') as $t) {
                    if ($this->strip($t->nodeValue) === $nodeText || $t->nodeValue === $nodeText) {
                        return $p;
                    }
                }
            }
        }
        return null;
    }

    /** Chèn 1 ĐOẠN MỚI (dòng dưới) chứa ${key}, kế thừa căn lề + định dạng của đoạn neo. */
    private function insertBelow(\DOMDocument $dom, string $paraText, string $nodeText, string $key): bool
    {
        $ns      = self::NS;
        $targetP = $this->findParagraph($dom, $paraText, $nodeText);
        if (! $targetP) {
            return false;
        }
        $newP = $dom->createElementNS($ns, 'w:p');
        // giữ căn lề (pPr) của đoạn neo
        foreach ($targetP->childNodes as $c) {
            if ($c->localName === 'pPr') {
                $newP->appendChild($c->cloneNode(true));
                break;
            }
        }
        $run = $dom->createElementNS($ns, 'w:r');
        // giữ định dạng chữ (rPr) của run đầu trong đoạn neo
        foreach ($targetP->getElementsByTagNameNS($ns, 'r') as $r) {
            foreach ($r->childNodes as $c) {
                if ($c->localName === 'rPr') {
                    $run->appendChild($c->cloneNode(true));
                    break 2;
                }
            }
            break;
        }
        $t = $dom->createElementNS($ns, 'w:t');
        $t->setAttribute('xml:space', 'preserve');
        $t->nodeValue = '${' . $key . '}';
        $run->appendChild($t);
        $newP->appendChild($run);

        $targetP->parentNode->insertBefore($newP, $targetP->nextSibling);
        return true;
    }

    /** Tìm đúng đoạn + đúng run chữ đã click rồi chèn ${key} vào giữa (cùng dòng). */
    private function insertPlaceholder(\DOMDocument $dom, string $paraText, string $nodeText, int $offset, int $occur, string $key): bool
    {
        if ($nodeText === '') {
            return false;
        }
        $ns      = self::NS;
        $targetP = $this->findParagraph($dom, $paraText, $nodeText);
        if (! $targetP) {
            return false;
        }

        // 2) run thứ $occur có text (đã bỏ ${..}) == nodeText — vì nhãn và ${..} có thể nằm CHUNG 1 w:t.
        $i       = 0;
        $targetT = null;
        foreach ($targetP->getElementsByTagNameNS($ns, 't') as $t) {
            if ($this->strip($t->nodeValue) === $nodeText) {
                if ($i === $occur) {
                    $targetT = $t;
                    break;
                }
                $i++;
            }
        }
        if (! $targetT) {
            foreach ($targetP->getElementsByTagNameNS($ns, 't') as $t) {
                if ($this->strip($t->nodeValue) === $nodeText) {
                    $targetT = $t;
                    break;
                }
            }
        }
        if (! $targetT) {
            return false;
        }

        // offset là vị trí trong text SẠCH -> ánh xạ về vị trí thật (bỏ qua ${..} phía trước)
        $rawOffset = $this->rawOffset($targetT->nodeValue, $offset);
        $this->splitInsert($dom, $targetT, $rawOffset, '${' . $key . '}');
        return true;
    }

    /** Đổi vị trí trong chuỗi đã bỏ ${..} sang vị trí trong chuỗi thật. */
    private function rawOffset(string $raw, int $cleanOffset): int
    {
        $clean = 0;
        $i     = 0;
        $len   = mb_strlen($raw);
        while ($i < $len) {
            if ($clean === $cleanOffset) {
                return $i;   // đã tới đúng vị trí -> chèn ở đây, TRƯỚC cả ${..} kế tiếp
            }
            if (mb_substr($raw, $i, 2) === '${') {
                $close = mb_strpos($raw, '}', $i);
                if ($close === false) {
                    break;
                }
                $i = $close + 1;
                continue;
            }
            $clean++;
            $i++;
        }
        return $i;
    }

    /** Tách run tại $offset (vị trí thật), chèn run placeholder (kế thừa định dạng) vào giữa. */
    private function splitInsert(\DOMDocument $dom, \DOMNode $t, int $offset, string $ph): void
    {
        $run    = $t->parentNode;                 // <w:r>
        $full   = $t->nodeValue;
        $offset = max(0, min($offset, mb_strlen($full)));
        $left   = mb_substr($full, 0, $offset);
        $right  = mb_substr($full, $offset);
        $ref    = $run->nextSibling;

        $phRun = $run->cloneNode(true);
        $this->setRunText($dom, $phRun, $ph);

        $t->nodeValue = $left;
        if (method_exists($t, 'setAttribute')) {
            $t->setAttribute('xml:space', 'preserve');
        }

        $run->parentNode->insertBefore($phRun, $ref);
        if ($right !== '') {
            $tail = $run->cloneNode(true);
            $this->setRunText($dom, $tail, $right);
            $run->parentNode->insertBefore($tail, $ref);
        }
    }

    /** Đặt lại text cho 1 run (giữ 1 w:t duy nhất, preserve space). */
    private function setRunText(\DOMDocument $dom, \DOMNode $run, string $text): void
    {
        $ts = $run->getElementsByTagNameNS(self::NS, 't');
        if ($ts->length > 0) {
            $keep = $ts->item(0);
            while ($ts->length > 1) {
                $extra = $ts->item(1);
                $extra->parentNode->removeChild($extra);
            }
            $keep->nodeValue = $text;
            $keep->setAttribute('xml:space', 'preserve');
        } else {
            $t = $dom->createElementNS(self::NS, 'w:t');
            $t->setAttribute('xml:space', 'preserve');
            $t->nodeValue = $text;
            $run->appendChild($t);
        }
    }

    private function pText(\DOMNode $p): string
    {
        $s = '';
        foreach ($p->getElementsByTagNameNS(self::NS, 't') as $t) {
            $s .= $t->nodeValue;
        }
        return $s;
    }

    private function strip(string $s): string
    {
        return preg_replace('/\$\{[^}]*\}/u', '', $s) ?? $s;
    }

    private function norm(string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', $s) ?? $s);
    }
}

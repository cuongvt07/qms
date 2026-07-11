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
        $orig  = Storage::disk('local')->path($version->formTemplate->file_goc_path);
        $added = $this->addedFields($version);
        if (empty($added) || ! is_file($orig)) {
            return $orig;
        }

        $dir = Storage::disk('local')->path('inline_aug');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $hash  = substr(md5(json_encode($added)), 0, 12);
        $cache = $dir . DIRECTORY_SEPARATOR . $version->id . '_' . $hash . '.docx';

        if (! is_file($cache)) {
            // dọn bản cache cũ của version này
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

    /** Copy file gốc rồi chèn tất cả placeholder vào document.xml, ghi ra $dest. */
    private function augment(string $src, array $added, string $dest): void
    {
        if (! @copy($src, $dest)) {
            return;
        }
        $zip = new \ZipArchive();
        if ($zip->open($dest) !== true) {
            return;
        }
        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            return;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput       = false;
        $dom->loadXML($xml, LIBXML_PARSEHUGE);

        foreach ($added as $f) {
            $a = $f['added_inline'];
            $this->insertPlaceholder(
                $dom,
                (string) ($a['paraText'] ?? ''),
                (string) ($a['nodeText'] ?? ''),
                (int) ($a['nodeOffset'] ?? 0),
                (int) ($a['nodeOccur'] ?? 0),
                (string) $f['key']
            );
        }

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();
    }

    /** Tìm đúng đoạn + đúng run chữ đã click rồi chèn ${key} vào giữa. */
    private function insertPlaceholder(\DOMDocument $dom, string $paraText, string $nodeText, int $offset, int $occur, string $key): bool
    {
        if ($nodeText === '') {
            return false;
        }
        $ns = self::NS;
        $ps = $dom->getElementsByTagNameNS($ns, 'p');

        // 1) đoạn khớp text (đã bỏ ${..}) — ưu tiên; nếu không có, đoạn nào chứa run == nodeText
        $want    = $this->norm($paraText);
        $targetP = null;
        foreach ($ps as $p) {
            if ($this->norm($this->strip($this->pText($p))) === $want) {
                $targetP = $p;
                break;
            }
        }
        if (! $targetP) {
            foreach ($ps as $p) {
                foreach ($p->getElementsByTagNameNS($ns, 't') as $t) {
                    if ($t->nodeValue === $nodeText) {
                        $targetP = $p;
                        break 2;
                    }
                }
            }
        }
        if (! $targetP) {
            return false;
        }

        // 2) run thứ $occur có text == nodeText trong đoạn đó
        $i       = 0;
        $targetT = null;
        foreach ($targetP->getElementsByTagNameNS($ns, 't') as $t) {
            if ($t->nodeValue === $nodeText) {
                if ($i === $occur) {
                    $targetT = $t;
                    break;
                }
                $i++;
            }
        }
        if (! $targetT) {
            foreach ($targetP->getElementsByTagNameNS($ns, 't') as $t) {
                if ($t->nodeValue === $nodeText) {
                    $targetT = $t;
                    break;
                }
            }
        }
        if (! $targetT) {
            return false;
        }

        $this->splitInsert($dom, $targetT, $offset, '${' . $key . '}');
        return true;
    }

    /** Tách run tại $offset, chèn run placeholder (kế thừa định dạng) vào giữa. */
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

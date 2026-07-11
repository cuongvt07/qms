<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\FormSubmissionRow;
use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use App\Services\ActivityLogger;
use Livewire\Component;

/**
 * Điền trực tiếp GIỐNG BẢN GỐC — render docx bằng docx-preview (client), chèn ô nhập inline.
 * LƯU CÙNG MÔ HÌNH với "dạng phiếu" (RegisterFill) để 2 màn đồng bộ:
 *   - flat + checkbox lẻ (chk_*) + nhóm chọn (field_key = giá trị chọn) -> data_json
 *   - bảng lặp -> form_submission_rows
 * Xuất .docx dùng chung DocxExportService.
 */
class InlineFill extends Component
{
    public int    $versionId;
    public int    $templateId;
    public string $ngay;
    public array  $vals         = [];     // client: flat + chk_ph(bool) + t[tkey][i][col]
    public ?int   $submissionId = null;
    public ?string $savedAt     = null;
    public bool   $config       = false;  // chế độ cấu hình ẩn ô (không phải điền)

    public function mount(int $versionId): void
    {
        $v = FormTemplateVersion::findOrFail($versionId);
        $this->versionId  = $versionId;
        $this->templateId = $v->form_template_id;
        $this->ngay       = now()->toDateString();
        $this->config     = request()->routeIs('forms.inline-config');

        $existing = FormSubmission::where('form_template_version_id', $versionId)
            ->where('user_id', auth()->id())
            ->where('ngay_nhap', $this->ngay)->first();
        if ($existing) {
            $this->submissionId = $existing->id;
            $this->vals         = $this->toClientVals($existing, $v->fields);
        }
    }

    /** Mô hình chuẩn (data_json field_key + form_submission_rows) -> vals client (chk_ph bool + t[tkey]). */
    private function toClientVals(FormSubmission $sub, array $fields): array
    {
        $vals = $sub->data_json ?? [];   // đã gồm flat + chk_ lẻ; giữ nguyên
        foreach ($fields as $f) {
            $type = $f['type'] ?? 'text';
            if ($type === 'repeatable_table') {
                $rows = FormSubmissionRow::where('form_submission_id', $sub->id)
                    ->where('field_key', $f['key'])->orderBy('row_index')->get();
                if ($rows->isNotEmpty()) {
                    $vals['t'][$f['key']] = $rows->map(fn ($r) => $r->row_data_json)->all();
                } elseif (! empty($sub->data_json['t'][$f['key']])) {
                    $vals['t'][$f['key']] = $sub->data_json['t'][$f['key']];   // tương thích data inline cũ
                }
            } elseif (! empty($f['option_ph'])) {
                // field_key = giá trị chọn -> bật các chk_ph tương ứng cho client
                $chosenVal = $vals[$f['key']] ?? null;
                $chosen = is_array($chosenVal)
                    ? array_map('strval', $chosenVal)
                    : ($chosenVal !== null && $chosenVal !== '' ? [(string) $chosenVal] : []);
                foreach ($f['option_ph'] as $opt => $ph) {
                    if (in_array((string) $opt, $chosen, true)) {
                        $vals[$ph] = true;
                    }
                }
            }
        }
        return $vals;
    }

    /** Nhận vals client rồi lưu về MÔ HÌNH CHUẨN (đồng bộ dạng phiếu). $silent=true khi autosave. */
    public function save($clientVals = null, bool $silent = false): void
    {
        $vals   = is_array($clientVals) ? $clientVals : $this->vals;
        $fields = FormTemplateVersion::find($this->versionId)?->fields ?? [];

        $phOfGroup    = [];   // chk_ph thuộc nhóm option -> loại khỏi data_json (thay bằng field_key)
        $optionFields = [];   // field_key -> [opt => ph]
        $tableFields  = [];   // field_key -> columns
        foreach ($fields as $f) {
            if (($f['type'] ?? '') === 'repeatable_table') {
                $tableFields[$f['key']] = $f['columns'] ?? [];
            } elseif (! empty($f['option_ph'])) {
                $optionFields[$f['key']] = $f['option_ph'];
                foreach ($f['option_ph'] as $ph) {
                    $phOfGroup[$ph] = true;
                }
            }
        }

        // data_json = flat + chk_ LẺ (giữ); bỏ 't' và chk_ph của nhóm
        $data = [];
        foreach ($vals as $k => $vv) {
            if ($k === 't' || isset($phOfGroup[$k])) {
                continue;
            }
            $data[$k] = $vv;
        }
        // nhóm chọn -> data_json[field_key] = giá trị chọn (scalar nếu 1, mảng nếu nhiều)
        foreach ($optionFields as $fk => $optph) {
            $chosen = [];
            foreach ($optph as $opt => $ph) {
                if (! empty($vals[$ph])) {
                    $chosen[] = (string) $opt;
                }
            }
            // 0 chọn -> '' (tránh mảng rỗng làm dạng phiếu lỗi render), 1 -> scalar, nhiều -> mảng
            $data[$fk] = count($chosen) <= 1 ? ($chosen[0] ?? '') : $chosen;
        }

        $sub = FormSubmission::updateOrCreate(
            ['form_template_version_id' => $this->versionId, 'user_id' => auth()->id(), 'ngay_nhap' => $this->ngay],
            ['data_json' => $data, 'trang_thai' => 'hoan_thanh']
        );

        // bảng -> form_submission_rows (mô hình chuẩn: dạng phiếu + xuất .docx đọc chỗ này)
        foreach ($tableFields as $fk => $cols) {
            $sttKeys = collect($cols)->filter(fn ($c) => RegisterFill::isSttCol($c))->pluck('key')->all();
            FormSubmissionRow::where('form_submission_id', $sub->id)->where('field_key', $fk)->delete();
            foreach (array_values($vals['t'][$fk] ?? []) as $ri => $rd) {
                foreach ($sttKeys as $sk) {
                    $rd[$sk] = $ri + 1;
                }
                FormSubmissionRow::create([
                    'form_submission_id' => $sub->id, 'field_key' => $fk,
                    'row_index' => $ri, 'row_data_json' => $rd,
                ]);
            }
        }

        $this->submissionId = $sub->id;
        $this->savedAt      = now()->format('H:i');
        $this->dispatch('saved');

        if (! $silent) {
            $maBm = FormTemplate::find($this->templateId)?->ma_bm;
            ActivityLogger::log('save', "Điền trực tiếp & lưu biểu mẫu {$maBm} — ngày {$this->ngay}", $sub);
            session()->flash('success', 'Đã lưu. Bấm "Tải .docx" để xuất bản điền.');
        }
    }

    /** Lưu cấu hình ẩn ô cho màn Giống bản gốc (danh sách key placeholder bị ẩn) vào version. */
    public function saveConfig($hidden = []): void
    {
        $hidden = is_array($hidden) ? array_values(array_unique(array_map('strval', $hidden))) : [];
        $v = FormTemplateVersion::find($this->versionId);
        if (! $v) {
            return;
        }
        $sj = $v->schema_json;
        $sj['inline_hidden'] = $hidden;
        $v->schema_json = $sj;
        $v->save();

        $maBm = FormTemplate::find($this->templateId)?->ma_bm;
        ActivityLogger::log('config', "Cấu hình ẩn " . count($hidden) . " ô ở màn giống bản gốc — biểu mẫu {$maBm}");
        session()->flash('success', 'Đã lưu cấu hình. Ẩn ' . count($hidden) . ' ô.');
    }

    /**
     * Thêm 1 ô nhập tại vị trí người dùng click ở màn giống bản gốc.
     * $pos: paraText, nodeText, nodeOffset, nodeOccur (do JS bắt từ caret).
     * Chèn ${extra_xx} vào .docx (qua InlineDocxService) + đăng ký field text để lưu/xuất.
     */
    public function addField($pos = []): void
    {
        if (! $this->config || ! is_array($pos) || empty($pos['nodeText'])) {
            return;
        }
        $v = FormTemplateVersion::find($this->versionId);
        if (! $v) {
            return;
        }
        $sj     = $v->schema_json;
        $fields = $sj['fields'] ?? [];

        $key   = 'extra_' . substr(md5(($pos['paraText'] ?? '') . '|' . ($pos['nodeText'] ?? '') . '|' . ($pos['nodeOffset'] ?? '') . '|' . uniqid('', true)), 0, 8);
        $label = 'Bổ sung: ' . trim(mb_substr(preg_replace('/\s+/u', ' ', (string) ($pos['paraText'] ?? '')) ?: 'ô nhập', 0, 40));

        $fields[] = [
            'key'          => $key,
            'type'         => 'text',
            'label'        => $label,
            'added_inline' => [
                'paraText'   => (string) ($pos['paraText'] ?? ''),
                'nodeText'   => (string) ($pos['nodeText'] ?? ''),
                'nodeOffset' => (int) ($pos['nodeOffset'] ?? 0),
                'nodeOccur'  => (int) ($pos['nodeOccur'] ?? 0),
            ],
        ];
        $sj['fields']   = $fields;
        $v->schema_json = $sj;
        $v->save();

        $maBm = FormTemplate::find($this->templateId)?->ma_bm;
        ActivityLogger::log('config', "Thêm 1 ô nhập ở màn giống bản gốc — biểu mẫu {$maBm}");
        $this->dispatch('inline-changed');   // JS tải lại để render bản .docx có ô mới
    }

    /** Xoá 1 ô đã thêm inline (hoàn tác). */
    public function removeAddedField($key): void
    {
        if (! $this->config) {
            return;
        }
        $v = FormTemplateVersion::find($this->versionId);
        if (! $v) {
            return;
        }
        $sj = $v->schema_json;
        $sj['fields'] = array_values(array_filter(
            $sj['fields'] ?? [],
            fn ($f) => ($f['key'] ?? '') !== $key || empty($f['added_inline'])
        ));
        // gỡ luôn khỏi danh sách ẩn nếu có
        if (! empty($sj['inline_hidden'])) {
            $sj['inline_hidden'] = array_values(array_diff($sj['inline_hidden'], [$key]));
        }
        $v->schema_json = $sj;
        $v->save();
        $this->dispatch('inline-changed');
    }

    public function render()
    {
        $t      = FormTemplate::find($this->templateId);
        $v      = FormTemplateVersion::find($this->versionId);
        $fields = $v?->fields ?? [];
        $added  = collect($fields)->filter(fn ($f) => ! empty($f['added_inline']))->pluck('key')->values()->all();
        return view('livewire.inline-fill', [
            'template'     => $t,
            'fields'       => $fields,
            'vals'         => $this->vals,
            'docxUrl'      => route('forms.inline-source', $this->versionId),
            'inlineHidden' => $v?->schema_json['inline_hidden'] ?? [],
            'inlineAdded'  => $added,
        ]);
    }
}

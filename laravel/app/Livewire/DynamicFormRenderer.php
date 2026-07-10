<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\FormSubmissionRow;
use App\Models\FormTemplateVersion;
use App\Services\FormValidationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Component cốt lõi: render form HTML động từ schema và xử lý nhập liệu.
 * Dùng chung cho mọi biểu mẫu (không viết riêng cho từng BM).
 */
class DynamicFormRenderer extends Component
{
    public int    $versionId  = 0;
    public string $ngayNhap   = '';
    public array  $data       = [];   // Giá trị các field flat
    public array  $tableData  = [];   // Giá trị repeatable_table: [field_key => [[col=>val,...]]]

    // State
    public ?int $submissionId = null;
    public bool $isSaved      = false;

    protected FormValidationService $validationService;

    public function boot(FormValidationService $validationService): void
    {
        $this->validationService = $validationService;
    }

    public function mount(int $versionId, ?string $ngayNhap = null): void
    {
        $this->versionId = $versionId;
        $this->ngayNhap  = $ngayNhap ?? now()->toDateString();

        // Load existing submission nếu có
        $existing = FormSubmission::where('form_template_version_id', $versionId)
            ->where('user_id', auth()->id())
            ->where('ngay_nhap', $this->ngayNhap)
            ->first();

        if ($existing) {
            $this->submissionId = $existing->id;
            $this->data         = $existing->data_json ?? [];

            // Load repeatable table data
            $version = FormTemplateVersion::find($versionId);
            foreach ($version->fields as $field) {
                if ($field['type'] === 'repeatable_table') {
                    $rows = $existing->rowsForField($field['key'])->get();
                    $this->tableData[$field['key']] = $rows->map(fn ($r) => $r->row_data_json)->toArray();
                }
            }
        }
    }

    public function getVersionProperty(): FormTemplateVersion
    {
        return FormTemplateVersion::findOrFail($this->versionId);
    }

    public function getFieldsProperty(): array
    {
        // Bỏ field đã ẩn — không hiển thị khi điền.
        return array_values(array_filter(
            $this->version->fields,
            fn ($f) => empty($f['hidden'])
        ));
    }

    public function addTableRow(string $fieldKey): void
    {
        // Lấy cột của field này
        $field = collect($this->fields)->firstWhere('key', $fieldKey);
        if (! $field) {
            return;
        }

        $emptyRow = [];
        foreach ($field['columns'] ?? [] as $col) {
            $emptyRow[$col['key']] = '';
        }

        $this->tableData[$fieldKey][] = $emptyRow;
    }

    public function removeTableRow(string $fieldKey, int $rowIndex): void
    {
        if (isset($this->tableData[$fieldKey][$rowIndex])) {
            array_splice($this->tableData[$fieldKey], $rowIndex, 1);
            $this->tableData[$fieldKey] = array_values($this->tableData[$fieldKey]);
        }
    }

    public function saveDraft(): void
    {
        $this->saveSubmission('nhap_dang_do');
        $this->isSaved = true;
    }

    public function submit(): void
    {
        $version = $this->version;
        $rules   = $this->validationService->buildRules($this->fields);      // chỉ field đang hiển thị
        $messages = $this->validationService->buildMessages($this->fields);

        // Merge tableData vào data để validate repeatable_table
        $dataToValidate = $this->data;
        foreach ($this->tableData as $key => $rows) {
            $dataToValidate[$key] = $rows;
        }

        $this->validate(
            $rules,
            $messages,
            ['data' => $dataToValidate]
        );

        $this->saveSubmission('hoan_thanh');

        session()->flash('success', 'Đã lưu biểu mẫu thành công!');
        $this->dispatch('form-submitted', submissionId: $this->submissionId);
    }

    private function saveSubmission(string $trangThai): void
    {
        DB::transaction(function () use ($trangThai) {
            $submission = FormSubmission::updateOrCreate(
                [
                    'form_template_version_id' => $this->versionId,
                    'user_id'                  => auth()->id(),
                    'ngay_nhap'                => $this->ngayNhap,
                ],
                [
                    'data_json'  => $this->data,
                    'trang_thai' => $trangThai,
                ]
            );

            $this->submissionId = $submission->id;

            // Lưu repeatable_table rows
            foreach ($this->tableData as $fieldKey => $rows) {
                // Xóa rows cũ rồi insert lại
                FormSubmissionRow::where('form_submission_id', $submission->id)
                    ->where('field_key', $fieldKey)
                    ->delete();

                foreach ($rows as $idx => $rowData) {
                    FormSubmissionRow::create([
                        'form_submission_id' => $submission->id,
                        'field_key'          => $fieldKey,
                        'row_index'          => $idx,
                        'row_data_json'      => $rowData,
                    ]);
                }
            }
        });
    }

    public function render()
    {
        return view('livewire.dynamic-form-renderer');
    }
}

<?php

namespace App\Services;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Exception\Exception as PhpWordException;
use RuntimeException;

/**
 * Xuất file .docx đã điền dữ liệu bằng cách ghi giá trị vào template gốc.
 * Dùng PHPWord TemplateProcessor — template cần có {{placeholder}} tương ứng field key.
 */
class DocxExportService
{
    /**
     * Tạo file .docx đã điền từ 1 FormSubmission.
     *
     * @return string  Đường dẫn file tạm (caller chịu trách nhiệm xóa sau khi response)
     * @throws RuntimeException
     */
    public function export(FormSubmission $submission): string
    {
        $version     = $submission->templateVersion;
        $template    = $version->formTemplate;
        $templatePath = Storage::disk('local')->path($template->file_goc_path);

        if (! file_exists($templatePath)) {
            throw new RuntimeException("Không tìm thấy file template gốc: {$template->file_goc_path}");
        }

        try {
            $processor = new TemplateProcessor($templatePath);
            $data      = $submission->data_json ?? [];
            $fields    = $version->fields;

            foreach ($fields as $field) {
                $key   = $field['key'];
                $value = $data[$key] ?? '';

                if ($field['type'] === 'repeatable_table') {
                    // Với repeatable_table: dùng cloneRow nếu template có row marker
                    $this->fillRepeatableTable($processor, $submission, $field);
                } elseif (! empty($field['option_ph'])) {
                    // Ô tích: điền ☒ vào ô được chọn, ☐ vào ô còn lại.
                    $chosen = is_array($value) ? array_map('strval', $value) : [(string) $value];
                    foreach ($field['option_ph'] as $optText => $ph) {
                        $processor->setValue($ph, in_array((string) $optText, $chosen, true) ? '☒' : '☐');
                    }
                } else {
                    $processor->setValue($key, htmlspecialchars((string) $value));
                }
            }

            // Quét xoá MỌI placeholder còn sót (bảng chưa nhập dòng, field không map…)
            // để không bao giờ lọt ${key} ra file xuất.
            foreach ($processor->getVariables() as $leftover) {
                $processor->setValue($leftover, '');
            }

            // Ghi vào file tạm
            $tmpPath = tempnam(sys_get_temp_dir(), 'qms_export_') . '.docx';
            $processor->saveAs($tmpPath);

            return $tmpPath;
        } catch (PhpWordException $e) {
            throw new RuntimeException('Lỗi xuất file .docx: ' . $e->getMessage());
        }
    }

    private function fillRepeatableTable(
        TemplateProcessor $processor,
        FormSubmission $submission,
        array $field
    ): void {
        $rows = $submission->rowsForField($field['key'])->get();
        if ($rows->isEmpty()) {
            return;
        }

        $columns    = $field['columns'] ?? [];
        $firstRow   = $rows->first();
        $rowData    = [];

        foreach ($rows as $idx => $row) {
            $entry = [];
            foreach ($columns as $col) {
                $entry[$col['key']] = htmlspecialchars((string) ($row->row_data_json[$col['key']] ?? ''));
            }
            $rowData[] = $entry;
        }

        // Dùng cloneRowAndSetValues của PHPWord nếu template có row marker `${field_key}`
        $processor->cloneRowAndSetValues($field['key'], $rowData);
    }
}

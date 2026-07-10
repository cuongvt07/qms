<?php

namespace App\Services;

/**
 * Build Laravel validation rules động từ schema field.
 * Không viết cứng rule cho từng BM — mọi BM đều dùng chung service này.
 */
class FormValidationService
{
    /**
     * @param  array  $fields   Danh sách field từ schema_json['fields']
     * @return array  Mảng rules cho Validator::make()
     */
    public function buildRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key      = $field['key'];
            $required = ($field['required'] ?? false) ? 'required' : 'nullable';
            $type     = $field['type'] ?? 'text';

            $typeRule = match ($type) {
                'text'             => 'string|max:1000',
                'textarea'         => 'string|max:10000',
                'number'           => 'numeric',
                'date'             => 'date',
                'select', 'radio'  => 'string|in:' . implode(',', $field['options'] ?? []),
                'checkbox'         => 'boolean',
                'repeatable_table' => 'array',
                default            => 'string|max:1000',
            };

            $rules["data.{$key}"] = "{$required}|{$typeRule}";

            // Với repeatable_table, thêm rule cho từng cột
            if ($type === 'repeatable_table' && ! empty($field['columns'])) {
                foreach ($field['columns'] as $col) {
                    $colRequired = ($col['required'] ?? false) ? 'required' : 'nullable';
                    $colType     = $col['type'] ?? 'text';

                    $colRule = match ($colType) {
                        'text', 'textarea' => 'string|max:1000',
                        'number'           => 'numeric',
                        'date'             => 'date',
                        'select', 'radio'  => 'string|in:' . implode(',', $col['options'] ?? []),
                        'checkbox'         => 'boolean',
                        default            => 'string|max:1000',
                    };

                    $rules["data.{$key}.*.{$col['key']}"] = "{$colRequired}|{$colRule}";
                }
            }
        }

        return $rules;
    }

    /**
     * Build custom messages tiếng Việt cho validation rules.
     */
    public function buildMessages(array $fields): array
    {
        $messages = [];

        foreach ($fields as $field) {
            $label = $field['label'] ?? $field['key'];
            $messages["data.{$field['key']}.required"] = "{$label} là bắt buộc.";
            $messages["data.{$field['key']}.date"]     = "{$label} phải là ngày hợp lệ.";
            $messages["data.{$field['key']}.numeric"]  = "{$label} phải là số.";
            $messages["data.{$field['key']}.in"]       = "{$label} không hợp lệ.";
        }

        return $messages;
    }
}

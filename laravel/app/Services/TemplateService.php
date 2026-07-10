<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

/**
 * Đọc placeholder ${key} từ file .docx mẫu — nền tảng cho luồng TemplateProcessor.
 * Mỗi ${key} trong file = 1 ô nhập trên form web; key chính là tên placeholder.
 */
class TemplateService
{
    /**
     * Danh sách key placeholder (đã bỏ trùng) trong file .docx.
     * @return string[]
     */
    public function getVariables(string $absPath): array
    {
        if (! is_file($absPath)) {
            throw new RuntimeException("Không thấy file mẫu: {$absPath}");
        }

        $processor = new TemplateProcessor($absPath);
        // getVariables() trả tên placeholder không kèm ${ }, gồm cả marker của bảng cloneRow.
        $vars = $processor->getVariables();

        $clean = [];
        foreach ($vars as $v) {
            $k = trim((string) $v);
            if ($k !== '' && ! in_array($k, $clean, true)) {
                $clean[] = $k;
            }
        }
        return $clean;
    }

    /**
     * Tạo cấu hình field mặc định từ danh sách placeholder.
     * Nhãn = key ở dạng dễ đọc (người dùng chỉnh lại), kiểu mặc định text.
     * Đoán kiểu đơn giản theo tên key.
     */
    public function fieldsFromVariables(array $vars): array
    {
        $fields = [];
        foreach ($vars as $key) {
            $fields[] = [
                'key'      => $key,
                'label'    => $this->humanize($key),
                'type'     => $this->guessType($key),
                'required' => false,
                'hidden'   => false,
                'options'  => [],
                'columns'  => [],
            ];
        }
        return $fields;
    }

    private function humanize(string $key): string
    {
        $s = trim(preg_replace('/[_\-]+/', ' ', $key));
        return $s !== '' ? mb_convert_case($s, MB_CASE_TITLE, 'UTF-8') : $key;
    }

    private function guessType(string $key): string
    {
        $k = mb_strtolower($key, 'UTF-8');
        if (str_contains($k, 'ngay') || str_contains($k, 'date') || str_contains($k, 'thoi_gian')) return 'date';
        if (preg_match('/\b(so|sl|so_luong|number|amount|gia|tien|dinh_muc)\b/', $k))               return 'number';
        if (str_contains($k, 'noi_dung') || str_contains($k, 'ghi_chu') || str_contains($k, 'ly_do')) return 'textarea';
        return 'text';
    }
}

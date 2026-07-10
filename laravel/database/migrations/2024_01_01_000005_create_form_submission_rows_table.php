<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng riêng cho field type = repeatable_table.
     * Cho phép query/thống kê từng dòng (VD: đếm "Không đạt" trong tháng)
     * mà không cần query JSON lồng nhau trong form_submissions.data_json.
     */
    public function up(): void
    {
        Schema::create('form_submission_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')
                  ->constrained('form_submissions')
                  ->cascadeOnDelete();
            $table->string('field_key');     // VD: "danh_sach_vat_tu"
            $table->unsignedSmallInteger('row_index'); // Thứ tự dòng trong bảng
            $table->json('row_data_json');   // Dữ liệu của 1 dòng: {ten_vat_tu, so_lot, ...}
            $table->timestamps();

            $table->index(['form_submission_id', 'field_key']);
            $table->index('field_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_rows');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            // Luôn trỏ đúng version schema tại thời điểm nhập
            $table->foreignId('form_template_version_id')
                  ->constrained('form_template_versions')
                  ->restrictOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->date('ngay_nhap');
            $table->json('data_json')->nullable();              // Giá trị field flat (text/date/select...)
            $table->enum('trang_thai', ['nhap_dang_do', 'hoan_thanh'])->default('nhap_dang_do');
            $table->timestamps();

            // 1 user chỉ có 1 submission cho 1 version/ngày
            $table->unique(['form_template_version_id', 'user_id', 'ngay_nhap'], 'unique_submission_per_day');
            $table->index(['user_id', 'ngay_nhap']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};

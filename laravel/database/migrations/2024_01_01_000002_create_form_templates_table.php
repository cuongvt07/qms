<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')
                  ->constrained('document_categories')
                  ->cascadeOnDelete();
            $table->string('ma_bm')->unique();          // VD: BM_02_QTQL_29_1_19
            $table->string('ten_bm');                   // Tên biểu mẫu
            $table->string('file_goc_path')->nullable(); // Đường dẫn file .docx gốc
            $table->enum('trang_thai', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_required')->default(true); // Bắt buộc phải nhập trong ngày
            $table->json('raw_structure')->nullable();  // JSON thô từ Python service (cache)
            $table->timestamps();

            $table->index('trang_thai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_templates');
    }
};

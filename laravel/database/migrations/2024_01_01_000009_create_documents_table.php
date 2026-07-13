<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ổ tài liệu (document drive): cây thư mục + file trong mỗi Mục tài liệu.
 * Mỗi document_category = 1 ổ; parent_id = thư mục cha (null = gốc của ổ).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('documents')->cascadeOnDelete();
            $table->string('type', 10)->default('file');          // folder | file
            $table->string('name');
            $table->string('path')->nullable();                    // đường dẫn lưu (file)
            $table->string('mime', 150)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20)->default('upload');       // upload | form | system
            $table->unsignedBigInteger('form_submission_id')->nullable();
            $table->boolean('is_system')->default(false);          // thư mục/hệ thống (Biểu mẫu…)
            $table->timestamps();

            $table->index(['document_category_id', 'parent_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng nhắc việc: admin cấu hình "user X cần nhập BM Y vào thứ Z / hàng ngày".
     */
    public function up(): void
    {
        Schema::create('daily_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')
                  ->constrained('form_templates')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            // null = mỗi ngày, hoặc JSON array [1,2,3,4,5] = T2-T6
            $table->json('ngay_trong_tuan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['form_template_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_checklists');
    }
};

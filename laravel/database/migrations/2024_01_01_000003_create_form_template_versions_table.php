<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')
                  ->constrained('form_templates')
                  ->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->json('schema_json');                       // Schema field đã được admin duyệt
            $table->foreignId('duyet_boi')                    // Admin đã duyệt
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->text('ghi_chu')->nullable();               // Ghi chú lý do cập nhật
            $table->timestamps();

            $table->unique(['form_template_id', 'version']);
            $table->index('form_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_template_versions');
    }
};

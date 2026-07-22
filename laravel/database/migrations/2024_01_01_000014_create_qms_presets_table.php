<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mẫu mặc định của các form nhập nhiều: lưu lại lựa chọn để lần sau mở lên có sẵn.
 * module: env | device | waste ; preset_key: batch | month | daily…
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_presets', function (Blueprint $table) {
            $table->id();
            $table->string('module', 20);
            $table->string('preset_key', 40);
            $table->json('payload');
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['module', 'preset_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_presets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Luồng nhập liệu nối tiếp: khai báo thứ tự các popup cần mở trong ngày.
 *  - qms_flow_steps: từng bước (module + loại popup)
 *  - qms_options   : tuỳ chọn chung dạng khoá/giá trị (vd bật tự mở khi đăng nhập)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sort')->default(0);
            $table->string('module', 20);          // env | device | waste
            $table->string('action', 20);          // daily/month, event/batch, batch/single
            $table->string('label')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('qms_options', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_options');
        Schema::dropIfExists('qms_flow_steps');
    }
};

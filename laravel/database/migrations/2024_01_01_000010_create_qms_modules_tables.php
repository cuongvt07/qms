<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Các module QMS dạng CRM (chuyển từ 3 mẫu thiết kế):
 *  - qms_staff   : nhân sự dùng chung (người kiểm tra / thực hiện) cho cả 3 module
 *  - env_settings: cấu hình + thông tin thiết bị của module Nhiệt độ/độ ẩm/vệ sinh
 *  - env_records : nhật ký theo dõi nhiệt độ, độ ẩm, vệ sinh
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_staff', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 40)->unique();      // u-1, u-2… (khớp mẫu thiết kế)
            $table->string('name');
            $table->string('role', 40)->default('technician');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('env_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('PHIẾU THEO DÕI NHIỆT ĐỘ, ĐỘ ẨM VÀ VỆ SINH PHÒNG XÉT NGHIỆM');
            $table->string('device_name')->nullable();
            $table->string('location')->nullable();
            $table->string('serial', 100)->nullable();
            $table->string('reviewer')->nullable();
            $table->decimal('temperature_min', 5, 1)->default(20);
            $table->decimal('temperature_max', 5, 1)->default(26);
            $table->decimal('humidity_min', 5, 1)->default(40);
            $table->decimal('humidity_max', 5, 1)->default(70);
            $table->string('time1', 5)->default('08:00');
            $table->string('time2', 5)->default('15:00');
            $table->timestamps();
        });

        Schema::create('env_records', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 60)->unique();       // env-1… (id phía giao diện)
            $table->date('date');
            $table->string('inspector_ext_id', 40)->nullable();
            $table->decimal('temperature1', 5, 1)->nullable();
            $table->decimal('temperature2', 5, 1)->nullable();
            $table->decimal('humidity1', 5, 1)->nullable();
            $table->decimal('humidity2', 5, 1)->nullable();
            $table->string('cleaning', 10)->nullable();    // yes | no | ''
            $table->text('remedy')->nullable();
            $table->unsignedInteger('rev')->default(1);
            $table->timestamps();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('env_records');
        Schema::dropIfExists('env_settings');
        Schema::dropIfExists('qms_staff');
    }
};

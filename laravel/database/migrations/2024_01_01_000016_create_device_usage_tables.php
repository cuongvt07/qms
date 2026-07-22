<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module nhật ký sử dụng thiết bị (mẫu thiết kế thứ 4).
 *  - qms_devices.default_hours: số giờ tiêu chuẩn dùng làm mẫu khi tạo sổ tháng
 *  - device_usage_logs        : mỗi ngày của mỗi thiết bị là 1 dòng
 *  - device_usage_closures    : chốt sổ theo tháng của từng thiết bị
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_devices', function (Blueprint $table) {
            $table->decimal('default_hours', 4, 1)->default(8)->after('department');
        });

        Schema::create('device_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_ext_id', 40)->index();
            $table->date('date');
            $table->string('user_name')->nullable();
            $table->decimal('hours', 5, 1)->nullable();
            $table->string('condition')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('pending');   // pending | confirmed | changed | issue | off
            $table->string('confirmed_at', 40)->nullable();
            $table->unsignedInteger('rev')->default(1);
            $table->timestamps();
            $table->unique(['device_ext_id', 'date']);
        });

        Schema::create('device_usage_closures', function (Blueprint $table) {
            $table->id();
            $table->string('device_ext_id', 40);
            $table->string('month', 7);                          // YYYY-MM
            $table->string('closed_at', 40)->nullable();
            $table->string('closed_by')->nullable();
            $table->timestamps();
            $table->unique(['device_ext_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_usage_closures');
        Schema::dropIfExists('device_usage_logs');
        Schema::table('qms_devices', function (Blueprint $table) {
            $table->dropColumn('default_hours');
        });
    }
};

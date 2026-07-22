<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module theo dõi khử nhiễm trang thiết bị (mẫu thiết kế thứ 2).
 *  - qms_devices  : danh mục trang thiết bị
 *  - device_events: nhật ký khử nhiễm / bảo dưỡng / sửa chữa…
 * Nhân sự dùng chung bảng qms_staff, tách theo cột `module` để mỗi module có danh sách riêng.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_staff', function (Blueprint $table) {
            $table->string('module', 20)->default('env')->after('ext_id')->index();
        });
        // ext_id chỉ duy nhất trong phạm vi 1 module
        Schema::table('qms_staff', function (Blueprint $table) {
            $table->dropUnique('qms_staff_ext_id_unique');
            $table->unique(['module', 'ext_id']);
        });

        Schema::create('qms_devices', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 40)->unique();       // dv-1…
            $table->string('code', 60)->nullable();
            $table->string('name');
            $table->string('serial', 100)->nullable();
            $table->string('location')->nullable();
            $table->string('department')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('device_events', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 60)->unique();       // evt-…
            $table->date('date');
            $table->string('device_ext_id', 40)->nullable()->index();
            $table->string('activity_type', 30)->default('decontamination');
            $table->text('reason')->nullable();
            $table->string('condition', 20)->default('pending');
            $table->string('condition_text')->nullable();
            $table->text('note')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('created_by', 40)->nullable();
            $table->unsignedInteger('rev')->default(1);
            $table->timestamps();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_events');
        Schema::dropIfExists('qms_devices');
        Schema::table('qms_staff', function (Blueprint $table) {
            $table->dropUnique(['module', 'ext_id']);
            $table->unique('ext_id');
            $table->dropColumn('module');
        });
    }
};

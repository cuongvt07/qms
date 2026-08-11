<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Một bệnh nhân có thể có nhiều mã tiêu bản.
 * Mã bệnh nhân (số hồ sơ) là khóa nối; khai báo khi làm hóa mô miễn dịch,
 * nhưng gắn thẳng vào mã tiêu bản để tra được "bệnh nhân này còn mã nào khác".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slide_patients', function (Blueprint $table) {
            $table->id();
            $table->string('ma_bn', 40)->unique();       // số hồ sơ / mã bệnh nhân
            $table->string('ho_ten', 190);
            $table->string('nam_sinh', 10)->nullable();
            $table->string('doi_tuong', 40)->nullable();
            $table->string('khoa', 60)->nullable();
            $table->timestamps();
        });

        Schema::table('slide_records', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('seq')
                ->constrained('slide_patients')->nullOnDelete();
        });

        Schema::table('slide_ihc', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('slide_code')
                ->constrained('slide_patients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('slide_ihc', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
        });
        Schema::table('slide_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
        });
        Schema::dropIfExists('slide_patients');
    }
};

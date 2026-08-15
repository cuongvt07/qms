<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mã chờ xử lý thêm (khử canxi, cắt lại, chờ khoa gửi bù...) thì phiên soạn đi
 * tiếp qua mã sau, rất dễ quên. Ghi ngày hẹn ra tiêu bản để còn đòi kíp xử lý.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slide_records', function (Blueprint $table) {
            $table->date('ngay_hen')->nullable()->after('ngay_soan');
        });
    }

    public function down(): void
    {
        Schema::table('slide_records', function (Blueprint $table) {
            $table->dropColumn('ngay_hen');
        });
    }
};

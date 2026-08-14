<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bác sĩ nhận giá tiêu bản về đọc, nên một mã đi qua ba trạng thái:
 * chưa đọc → đã nhận (bác sĩ đã lấy giá về) → đã đọc.
 *
 * Cột da_doc cũ vẫn được giữ và luôn đồng bộ với trạng thái 'doc' cho các
 * màn tra cứu / xuất Excel đang dựa vào nó.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slide_records', function (Blueprint $table) {
            $table->string('trang_thai_doc', 10)->default('chua')->after('da_doc');   // chua | nhan | doc
            $table->date('ngay_nhan')->nullable()->after('trang_thai_doc');
            $table->index('trang_thai_doc');
        });

        DB::table('slide_records')->where('da_doc', true)->update(['trang_thai_doc' => 'doc']);
    }

    public function down(): void
    {
        Schema::table('slide_records', function (Blueprint $table) {
            $table->dropIndex(['trang_thai_doc']);
            $table->dropColumn(['trang_thai_doc', 'ngay_nhan']);
        });
    }
};

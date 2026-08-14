<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chốt một mã tiêu bản là xóa hẳn phiếu hóa mô và phiên hội chẩn của nó, nên
 * phải giữ lại nguyên văn hai phần đó trong lịch sử — không chỉ marker và câu kết luận.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slide_history', function (Blueprint $table) {
            $table->json('hmmd')->nullable()->after('markers');          // toàn bộ phiếu HMMD của lượt này
            $table->json('hoi_chan')->nullable()->after('ket_luan_hoi_chan'); // kết luận + mọi ý kiến hội chẩn
        });
    }

    public function down(): void
    {
        Schema::table('slide_history', function (Blueprint $table) {
            $table->dropColumn(['hmmd', 'hoi_chan']);
        });
    }
};

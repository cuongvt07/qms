<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bổ sung thông tin quản lý mã hàng: nhóm hàng, trạng thái, ghi chú.
 * Mã hàng và thẻ kho là quan hệ 1–1 (card_no đã có sẵn ở bảng này).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('name');
            $table->boolean('active')->default(true)->after('max_qty');
            $table->text('note')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->dropColumn(['group_name', 'active', 'note']);
        });
    }
};

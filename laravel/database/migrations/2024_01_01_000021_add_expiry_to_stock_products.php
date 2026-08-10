<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hạn dùng của hóa chất / vật tư, khai báo ngay lúc tạo thẻ kho.
 * Khác với stock_transactions.expiry (hạn của từng lô nhập) — cột này là hạn chung
 * của mã hàng, dùng để cảnh báo trên trang danh mục kể cả khi hàng không quản lý theo lô.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->date('expiry')->nullable()->after('supplier');
        });
    }

    public function down(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->dropColumn('expiry');
        });
    }
};

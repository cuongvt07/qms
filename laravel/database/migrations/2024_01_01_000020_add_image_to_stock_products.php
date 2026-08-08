<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Ảnh minh họa của mã hàng (hiển thị ở màn chọn thẻ hàng kiểu đặt hàng). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};

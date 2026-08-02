<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module quản lý thẻ kho (mẫu thiết kế thứ 5).
 *  - stock_products    : mỗi mã hàng = 1 thẻ kho duy nhất (card_no tự sinh)
 *  - stock_transactions: các phát sinh nhập / xuất / hủy / kiểm kê ghi nối tiếp theo thời gian
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_products', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 40)->unique();       // id phía giao diện
            $table->string('card_no', 30)->unique();       // số thẻ kho tự sinh: TK-00001…
            $table->string('code', 80)->index();           // mã hàng hóa
            $table->string('name');
            $table->string('unit', 40)->nullable();
            $table->string('packing')->nullable();
            $table->string('supplier')->nullable();
            $table->decimal('min_qty', 14, 2)->default(0);
            $table->decimal('max_qty', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 40)->unique();
            $table->string('product_ext_id', 40)->index();
            $table->date('date');
            $table->string('type', 12);                    // import | export | destroy | adjust
            $table->decimal('qty', 14, 2)->default(0);
            $table->decimal('actual', 14, 2)->nullable();  // đếm kho thực tế (kiểm kê)
            $table->string('batch', 80)->nullable();
            $table->date('expiry')->nullable();
            $table->string('destination')->nullable();
            $table->string('deliverer')->nullable();
            $table->string('receiver')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
        Schema::dropIfExists('stock_products');
    }
};

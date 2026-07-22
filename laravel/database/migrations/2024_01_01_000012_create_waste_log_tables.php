<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module nhật ký xử lý rác thải (mẫu thiết kế thứ 3).
 *  - waste_settings: thông tin biểu mẫu (mã tài liệu, phiên bản, khoa/phòng, năm)
 *  - waste_catalogs: danh mục dùng cho ô chọn (loại chất thải / biện pháp xử lý / vị trí)
 *  - waste_batches : đợt nhập liệu
 *  - waste_rows    : từng dòng nhật ký
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_settings', function (Blueprint $table) {
            $table->id();
            $table->string('document_code', 60)->nullable();
            $table->string('form_version', 30)->nullable();
            $table->string('effective_date', 30)->nullable();
            $table->string('department')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->timestamps();
        });

        Schema::create('waste_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 30);           // wasteTypes | treatments | locations
            $table->string('value');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->index(['kind', 'sort']);
        });

        Schema::create('waste_batches', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 60)->unique();      // batch-…
            $table->string('department')->nullable();
            $table->text('note')->nullable();
            $table->string('created_by', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('waste_rows', function (Blueprint $table) {
            $table->id();
            $table->string('ext_id', 60)->unique();      // row-…
            $table->string('batch_ext_id', 60)->nullable()->index();
            $table->date('date')->nullable();
            $table->string('time', 5)->nullable();
            $table->string('waste_type')->nullable();
            $table->string('treatment')->nullable();
            $table->string('location')->nullable();
            $table->string('performer_ext_id', 40)->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('rev')->default(1);
            $table->timestamps();
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_rows');
        Schema::dropIfExists('waste_batches');
        Schema::dropIfExists('waste_catalogs');
        Schema::dropIfExists('waste_settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sổ soạn tiêu bản + hóa mô miễn dịch (gộp hai quyển sổ giấy thành một luồng).
 *
 * Mã tiêu bản: 2 số cuối năm + chữ cái A..Z + số thứ tự 4 chữ số  →  26C2472
 * Mỗi mã là duy nhất, nhưng một mã có nhiều block và nhiều lam.
 * Sổ được chia theo đầu mã (26A, 26B…), mỗi đầu mã có 9999 dòng.
 * Dòng chỉ được ghi xuống CSDL khi có người nhập số block / số tiêu bản.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slide_records', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();          // 26C2472
            $table->unsignedTinyInteger('yy');             // 26
            $table->char('letter', 1);                     // C
            $table->unsignedSmallInteger('seq');           // 2472
            $table->unsignedSmallInteger('so_block')->nullable();
            $table->unsignedSmallInteger('so_tieu_ban')->nullable();
            $table->date('ngay_soan')->nullable();         // tự lấy ngày nhập
            $table->string('gia_so', 20)->nullable();
            $table->string('ktv_cat', 120)->nullable();
            $table->string('ktv_soan', 120)->nullable();   // tự lấy tài khoản đang đăng nhập
            $table->string('bs_doc', 120)->nullable();
            $table->text('ket_qua')->nullable();           // kết quả / tình trạng đọc
            $table->boolean('da_doc')->default(false);
            $table->date('ngay_doc')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
            $table->index(['yy', 'letter', 'seq']);
            $table->index('gia_so');
        });

        // Phiếu hóa mô miễn dịch — chỉ ca nào làm HMMD mới có thông tin bệnh nhân
        Schema::create('slide_ihc', function (Blueprint $table) {
            $table->id();
            $table->string('slide_code', 12)->index();
            $table->string('benh_nhan', 190)->nullable();
            $table->string('nam_sinh', 10)->nullable();
            $table->string('doi_tuong', 40)->nullable();
            $table->string('khoa', 60)->nullable();
            $table->string('vi_tri', 190)->nullable();
            $table->string('cd_lam_sang', 255)->nullable();
            $table->unsignedSmallInteger('so_block')->nullable();
            $table->json('markers')->nullable();
            $table->string('bs_chi_dinh', 120)->nullable();
            $table->date('ngay_chi_dinh')->nullable();
            $table->date('ngay_lay_mau')->nullable();
            $table->date('ngay_nhan_mau')->nullable();
            $table->date('ngay_nhuom')->nullable();
            $table->string('bs_doc_kq', 120)->nullable();
            $table->date('ngay_doc_kq')->nullable();
            $table->string('trang_thai', 12)->default('cho');   // cho | nhuom | doc
            $table->timestamps();
        });

        Schema::create('slide_consults', function (Blueprint $table) {
            $table->id();
            $table->string('slide_code', 12)->unique();
            $table->text('ket_luan')->nullable();
            $table->string('bs_chot', 120)->nullable();
            $table->date('ngay_chot')->nullable();
            $table->timestamps();
        });

        Schema::create('slide_consult_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consult_id')->constrained('slide_consults')->cascadeOnDelete();
            $table->string('bs', 120);
            $table->text('noi_dung');
            $table->timestamps();
        });

        // Ảnh hội chẩn — xóa sạch ngay khi chốt kết luận cho nhẹ ổ đĩa
        Schema::create('slide_consult_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consult_id')->constrained('slide_consults')->cascadeOnDelete();
            $table->string('path');
            $table->string('ten_goc', 190)->nullable();
            $table->string('nguoi_tai', 120)->nullable();
            $table->timestamps();
        });

        // Danh mục marker (nạp từ file theo dõi kho kháng thể)
        Schema::create('ihc_markers', function (Blueprint $table) {
            $table->id();
            $table->string('ten', 120)->unique();
            $table->string('clone', 120)->nullable();
            $table->string('hang', 80)->nullable();
            $table->boolean('active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ihc_markers');
        Schema::dropIfExists('slide_consult_images');
        Schema::dropIfExists('slide_consult_notes');
        Schema::dropIfExists('slide_consults');
        Schema::dropIfExists('slide_ihc');
        Schema::dropIfExists('slide_records');
    }
};

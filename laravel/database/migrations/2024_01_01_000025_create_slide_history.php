<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lịch sử mã tiêu bản đã hoàn tất.
 *
 * Khi bác sĩ đọc xong và nhập kết quả, cả ca được chụp lại vào đây rồi dòng trong
 * sổ soạn bị xóa để mã tiêu bản quay về danh sách mã trống, dùng lại cho ca sau.
 * Vì vậy một mã có thể có nhiều lượt — phân biệt bằng cột `lan`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slide_history', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12);
            $table->unsignedSmallInteger('lan')->default(1);      // lượt thứ mấy của mã này
            $table->unsignedTinyInteger('yy');
            $table->char('letter', 1);
            $table->unsignedSmallInteger('seq');

            // ảnh chụp dòng sổ soạn
            $table->unsignedSmallInteger('so_block')->nullable();
            $table->unsignedSmallInteger('so_tieu_ban')->nullable();
            $table->date('ngay_soan')->nullable();
            $table->string('gia_so', 20)->nullable();
            $table->string('ktv_cat', 120)->nullable();
            $table->string('ktv_soan', 120)->nullable();
            $table->string('bs_doc', 120)->nullable();
            $table->text('ket_qua')->nullable();
            $table->date('ngay_nhan')->nullable();
            $table->date('ngay_doc')->nullable();
            $table->text('ghi_chu')->nullable();

            // ảnh chụp phần hóa mô miễn dịch, bệnh nhân và hội chẩn kèm theo
            $table->foreignId('patient_id')->nullable()->constrained('slide_patients')->nullOnDelete();
            $table->string('ma_bn', 40)->nullable();
            $table->string('benh_nhan', 190)->nullable();
            $table->string('khoa', 60)->nullable();
            $table->string('vi_tri', 190)->nullable();
            $table->json('markers')->nullable();
            $table->date('ngay_doc_hmmd')->nullable();
            $table->text('ket_luan_hoi_chan')->nullable();

            $table->string('nguoi_chot', 120)->nullable();        // ai bấm hoàn tất
            $table->date('ngay_chot');                            // ngày hoàn tất
            $table->timestamps();

            $table->unique(['code', 'lan']);
            $table->index('ngay_chot');
            $table->index('gia_so');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slide_history');
    }
};

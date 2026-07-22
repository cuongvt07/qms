<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dữ liệu nền dùng chung cho các module QMS:
 *  - qms_departments: danh sách khoa / phòng ban
 *  - qms_staff      : gộp nhân sự của 3 module thành 1 danh sách chung (module = 'all')
 * Vì mỗi module trước đây có không gian mã riêng (u-1 ở module này khác u-1 ở module kia),
 * migration ánh xạ lại mã người thực hiện trong các bảng nhật ký theo tên.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('qms_staff', function (Blueprint $table) {
            $table->string('department')->nullable()->after('name');
        });

        $this->mergeStaff();
        $this->seedDepartments();
    }

    /** Gộp nhân sự 3 module theo tên, ánh xạ lại mã trong các bảng nhật ký. */
    private function mergeStaff(): void
    {
        $rows = DB::table('qms_staff')
            ->orderByRaw("FIELD(module,'env','device','waste')")
            ->orderBy('id')->get();

        $byName = [];   // ten chuan hoa => ext_id chung
        $used   = [];   // ext_id da dung
        $map    = [];   // "module|ext_id" => ext_id chung
        $keep   = [];   // id hang giu lai

        foreach ($rows as $r) {
            $key = $this->normalize($r->name);
            if (isset($byName[$key])) {
                $map[$r->module . '|' . $r->ext_id] = $byName[$key];
                continue;                       // trùng người -> bỏ hàng này
            }
            $ext = $r->ext_id;
            if (isset($used[$ext])) {           // mã đã bị người khác chiếm
                $i = 1;
                while (isset($used['u-g' . $i])) {
                    $i++;
                }
                $ext = 'u-g' . $i;
                DB::table('qms_staff')->where('id', $r->id)->update(['ext_id' => $ext]);
            }
            $used[$ext]   = true;
            $byName[$key] = $ext;
            $map[$r->module . '|' . $r->ext_id] = $ext;
            $keep[] = $r->id;
        }

        // ánh xạ lại mã người thực hiện trong dữ liệu đã có
        $remap = [
            ['env_records',   'inspector_ext_id', 'env'],
            ['device_events', 'created_by',       'device'],
            ['waste_rows',    'performer_ext_id', 'waste'],
            ['waste_batches', 'created_by',       'waste'],
        ];
        foreach ($remap as [$table, $col, $module]) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($map as $k => $new) {
                [$mod, $old] = explode('|', $k, 2);
                if ($mod !== $module || $old === $new) {
                    continue;
                }
                DB::table($table)->where($col, $old)->update([$col => $new]);
            }
        }

        DB::table('qms_staff')->whereNotIn('id', $keep ?: [0])->delete();
        DB::table('qms_staff')->update(['module' => 'all']);
    }

    /** Bỏ tiền tố chức danh + dấu cách thừa để nhận ra cùng một người. */
    private function normalize(string $name): string
    {
        $s = mb_strtolower(trim($name));
        $s = preg_replace('/^(ktv|kỹ thuật viên|bs|bác sĩ|ths|ts|cn)\.?\s+/u', '', $s);
        return preg_replace('/\s+/u', ' ', $s);
    }

    private function seedDepartments(): void
    {
        $names = [];
        if (Schema::hasTable('qms_devices')) {
            $names = array_merge($names, DB::table('qms_devices')->distinct()->pluck('department')->all());
        }
        if (Schema::hasTable('waste_settings')) {
            $names = array_merge($names, DB::table('waste_settings')->distinct()->pluck('department')->all());
        }
        $names = array_values(array_unique(array_filter(array_map('trim', $names))));
        if (! $names) {
            $names = ['Trung tâm Xét nghiệm'];
        }
        foreach ($names as $i => $n) {
            DB::table('qms_departments')->insertOrIgnore([
                'name' => $n, 'active' => 1, 'sort' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('qms_staff', function (Blueprint $table) {
            $table->dropColumn('department');
        });
        Schema::dropIfExists('qms_departments');
    }
};

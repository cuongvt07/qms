<?php

namespace App\Http\Controllers;

use App\Models\QmsStaff;
use App\Models\WasteBatch;
use App\Models\WasteCatalog;
use App\Models\WasteRow;
use App\Models\WasteSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module nhật ký xử lý rác thải.
 * Giao diện giữ nguyên mẫu thiết kế; localStorage thay bằng 2 API state / save.
 */
class WasteLogController extends Controller
{
    private const MODULE = 'waste';
    private const KINDS  = ['wasteTypes', 'treatments', 'locations'];

    public function page()
    {
        return view('modules.waste-log');
    }

    /** Nạp dữ liệu mẫu ở lần chạy đầu tiên. */
    private function seedIfEmpty(): void
    {
        if (WasteSetting::exists()) {
            return;
        }
        $seed = [];
        $file = database_path('seed/waste_seed.json');
        if (is_file($file)) {
            $seed = json_decode(file_get_contents($file), true) ?: [];
        }
        $f = $seed['form'] ?? [];
        WasteSetting::create([
            'document_code'  => $f['documentCode'] ?? 'BM.02/QTQL.25',
            'form_version'   => $f['formVersion'] ?? '1.24',
            'effective_date' => $f['effectiveDate'] ?? null,
            'department'     => $f['department'] ?? 'Trung tâm Xét nghiệm',
            'year'           => $f['year'] ?? (int) date('Y'),
        ]);

        foreach (self::KINDS as $kind) {
            foreach (($seed['catalogs'][$kind] ?? []) as $i => $v) {
                WasteCatalog::create(['kind' => $kind, 'value' => $v, 'sort' => $i]);
            }
        }
        foreach ($seed['users'] ?? [] as $u) {
            QmsStaff::updateOrCreate(['module' => self::MODULE, 'ext_id' => $u['id']], [
                'name' => $u['name'] ?? $u['id'],
                'role' => $u['role'] ?? 'staff',
            ]);
        }
        foreach ($seed['batches'] ?? [] as $b) {
            WasteBatch::updateOrCreate(['ext_id' => $b['id']], [
                'department' => $b['department'] ?? null,
                'note'       => $b['note'] ?? null,
                'created_by' => $b['createdBy'] ?? null,
            ]);
        }
        foreach ($seed['rows'] ?? [] as $r) {
            WasteRow::updateOrCreate(['ext_id' => $r['id']], [
                'batch_ext_id'     => $r['batchId'] ?? null,
                'date'             => $r['date'] ?? null,
                'time'             => $r['time'] ?? null,
                'waste_type'       => $r['wasteType'] ?? null,
                'treatment'        => $r['treatment'] ?? null,
                'location'         => $r['location'] ?? null,
                'performer_ext_id' => $r['performerId'] ?? null,
                'note'             => $r['note'] ?? null,
                'rev'              => $r['version'] ?? 1,
            ]);
        }
    }

    /** Trạng thái cho giao diện (đúng cấu trúc mẫu thiết kế). */
    public function state(): JsonResponse
    {
        $this->seedIfEmpty();
        $s = WasteSetting::first();

        $catalogs = [];
        foreach (self::KINDS as $kind) {
            $catalogs[$kind] = WasteCatalog::where('kind', $kind)->orderBy('sort')->orderBy('id')
                ->pluck('value')->all();
        }

        return response()->json([
            'version' => 2,
            'form'    => [
                'documentCode'  => $s->document_code,
                'formVersion'   => $s->form_version,
                'effectiveDate' => $s->effective_date,
                'department'    => $s->department,
                'year'          => (int) $s->year,
            ],
            'catalogs' => $catalogs,
            'users'    => QmsStaff::where('module', self::MODULE)->where('active', true)->orderBy('id')
                ->get()->map(fn ($u) => ['id' => $u->ext_id, 'name' => $u->name, 'role' => $u->role])->all(),
            'batches' => WasteBatch::orderBy('id')->get()->map(fn ($b) => [
                'id'         => $b->ext_id,
                'department' => $b->department,
                'note'       => $b->note ?? '',
                'createdBy'  => $b->created_by,
                'createdAt'  => optional($b->created_at)->toIso8601String(),
                'updatedAt'  => optional($b->updated_at)->toIso8601String(),
            ])->all(),
            'rows' => WasteRow::orderBy('date', 'desc')->orderBy('time', 'desc')->orderBy('id', 'desc')
                ->get()->map(fn ($r) => [
                    'id'          => $r->ext_id,
                    'batchId'     => $r->batch_ext_id,
                    'date'        => $r->date?->toDateString(),
                    'time'        => $r->time ?? '',
                    'wasteType'   => $r->waste_type ?? '',
                    'treatment'   => $r->treatment ?? '',
                    'location'    => $r->location ?? '',
                    'performerId' => $r->performer_ext_id,
                    'note'        => $r->note ?? '',
                    'createdAt'   => optional($r->created_at)->toIso8601String(),
                    'updatedAt'   => optional($r->updated_at)->toIso8601String(),
                    'version'     => $r->rev,
                    'history'     => [],
                ])->all(),
            'currentUserId' => 'u-admin',
        ]);
    }

    /** Lưu toàn bộ trạng thái từ giao diện xuống CSDL. */
    public function save(Request $request): JsonResponse
    {
        $request->validate([
            'form'        => 'array',
            'catalogs'    => 'array',
            'users'       => 'array',
            'batches'     => 'array',
            'rows'        => 'array',
            'rows.*.id'   => 'required|string|max:60',
            'rows.*.date' => 'nullable|date',
        ]);
        // validate() chỉ trả field đã khai báo -> đọc payload thô để không mất cột.
        $form     = $request->input('form', []);
        $catalogs = $request->input('catalogs', []);
        $users    = $request->input('users', []);
        $batches  = $request->input('batches', []);
        $rows     = $request->input('rows', []);

        $this->seedIfEmpty();
        $s = WasteSetting::first();
        $s->update(array_filter([
            'document_code'  => $form['documentCode'] ?? null,
            'form_version'   => $form['formVersion'] ?? null,
            'effective_date' => $form['effectiveDate'] ?? null,
            'department'     => $form['department'] ?? null,
            'year'           => $form['year'] ?? null,
        ], fn ($v) => $v !== null));

        // Danh mục: ghi đè theo đúng thứ tự trên giao diện
        foreach (self::KINDS as $kind) {
            if (! isset($catalogs[$kind]) || ! is_array($catalogs[$kind])) {
                continue;
            }
            WasteCatalog::where('kind', $kind)->delete();
            foreach ($catalogs[$kind] as $i => $v) {
                if ($v === null || $v === '') {
                    continue;
                }
                WasteCatalog::create(['kind' => $kind, 'value' => $v, 'sort' => $i]);
            }
        }

        if (! empty($users)) {
            $keep = [];
            foreach ($users as $u) {
                if (empty($u['id'])) {
                    continue;
                }
                QmsStaff::updateOrCreate(['module' => self::MODULE, 'ext_id' => $u['id']], [
                    'name'   => $u['name'] ?? $u['id'],
                    'role'   => $u['role'] ?? 'staff',
                    'active' => true,
                ]);
                $keep[] = $u['id'];
            }
            QmsStaff::where('module', self::MODULE)->whereNotIn('ext_id', $keep)->update(['active' => false]);
        }

        $keep = [];
        foreach ($batches as $b) {
            if (empty($b['id'])) {
                continue;
            }
            WasteBatch::updateOrCreate(['ext_id' => $b['id']], [
                'department' => $b['department'] ?? null,
                'note'       => $b['note'] ?? null,
                'created_by' => $b['createdBy'] ?? null,
            ]);
            $keep[] = $b['id'];
        }
        WasteBatch::whereNotIn('ext_id', $keep ?: ['__none__'])->delete();

        $keep = [];
        foreach ($rows as $r) {
            WasteRow::updateOrCreate(['ext_id' => $r['id']], [
                'batch_ext_id'     => $r['batchId'] ?? null,
                'date'             => $r['date'] ?? null,
                'time'             => $r['time'] ?? null,
                'waste_type'       => $r['wasteType'] ?? null,
                'treatment'        => $r['treatment'] ?? null,
                'location'         => $r['location'] ?? null,
                'performer_ext_id' => $r['performerId'] ?? null,
                'note'             => $r['note'] ?? null,
                'rev'              => $r['version'] ?? 1,
            ]);
            $keep[] = $r['id'];
        }
        WasteRow::whereNotIn('ext_id', $keep ?: ['__none__'])->delete();

        ActivityLogger::log('waste_log', 'Cập nhật nhật ký xử lý rác thải (' . count($rows) . ' dòng)');

        return response()->json(['ok' => true, 'rows' => count($rows)]);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\DocumentCategory;
use App\Models\FormSubmission;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Trung tâm điều hành (CMS trên PC):
 * - KPI ngày: cần nhập / đã xong / đang dở / chưa nhập
 * - Mỗi bộ TL là một thẻ task-menu, chạm để xem chi tiết BM + ai đã nhập
 */
class OperationsCenter extends Component
{
    public string $ngay = '';
    public ?int   $openCat = null;

    private const PALETTE = ['#0d7d8a', '#7c3aed', '#b45309', '#15803d', '#0369a1', '#be123c', '#0891b2', '#4f46e5'];

    public function mount(): void
    {
        $this->ngay = now()->toDateString();
    }

    public function changeDate(string $d): void
    {
        $this->ngay = $d;
    }

    public function selectCat(int $id): void
    {
        $this->openCat = $this->openCat === $id ? null : $id;
    }

    private function iconFor(string $name): string
    {
        $n = Str::lower(Str::ascii($name));
        return match (true) {
            str_contains($n, 'noi kiem') || str_contains($n, 'chat luong') || str_contains($n, 'bao cao') => 'chart',
            str_contains($n, 'thiet bi') || str_contains($n, 'hieu chuan')                                 => 'wrench',
            str_contains($n, 'moi truong') || str_contains($n, 'nhiet')                                     => 'thermo',
            str_contains($n, 'khu nhiem') || str_contains($n, 'an toan') || str_contains($n, 'rui ro')      => 'shield',
            str_contains($n, 'nhan su') || str_contains($n, 'dao tao') || str_contains($n, 'khach hang')    => 'users',
            str_contains($n, 'mua') || str_contains($n, 'hang hoa') || str_contains($n, 'vat tu')           => 'cart',
            default                                                                                          => 'doc',
        };
    }

    /** Bảng điều hành: mỗi TL kèm thống kê nhập liệu theo ngày (toàn bộ nhân viên) */
    public function getBoardProperty(): array
    {
        $date = $this->ngay;

        $categories = DocumentCategory::with(['formTemplates' => function ($q) {
            $q->where('trang_thai', 'active')->with('versions');
        }])->where('is_active', true)->orderBy('ten_muc')->get();

        // Đếm submission theo template_id + trạng thái trong ngày (mọi user)
        $subs = FormSubmission::where('ngay_nhap', $date)
            ->with('templateVersion:id,form_template_id')
            ->get()
            ->groupBy(fn ($s) => $s->templateVersion?->form_template_id);

        $board = [];
        $i = 0;
        foreach ($categories as $cat) {
            $forms = [];
            $done = $doing = 0;

            foreach ($cat->formTemplates as $t) {
                $v = $t->versions()->first();
                if (! $v) {
                    continue;
                }
                $group    = $subs->get($t->id) ?? collect();
                $doneN    = $group->where('trang_thai', 'hoan_thanh')->count();
                $doingN   = $group->where('trang_thai', 'nhap_dang_do')->count();
                $status   = $doneN > 0 ? 'done' : ($doingN > 0 ? 'doing' : 'todo');
                if ($status === 'done') $done++;
                elseif ($status === 'doing') $doing++;

                $forms[] = [
                    'template_id' => $t->id,
                    'version_id'  => $v->id,
                    'ma_bm'       => $t->ma_bm,
                    'ten_bm'      => $t->ten_bm,
                    'is_required' => $t->is_required,
                    'done_count'  => $doneN,
                    'doing_count' => $doingN,
                    'status'      => $status,
                ];
            }
            if (empty($forms)) {
                continue;
            }

            $total = count($forms);
            $code  = trim(explode('-', $cat->ten_muc)[0]);
            $board[] = [
                'id'      => $cat->id,
                'code'    => Str::limit($code, 10, ''),
                'name'    => $cat->ten_muc,
                'color'   => self::PALETTE[$i % count(self::PALETTE)],
                'icon'    => $this->iconFor($cat->ten_muc),
                'forms'   => $forms,
                'total'   => $total,
                'done'    => $done,
                'doing'   => $doing,
                'todo'    => $total - $done - $doing,
                'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
            ];
            $i++;
        }

        return $board;
    }

    public function getKpiProperty(): array
    {
        $b = $this->board;
        $total = array_sum(array_column($b, 'total'));
        $done  = array_sum(array_column($b, 'done'));
        $doing = array_sum(array_column($b, 'doing'));
        return [
            'total'   => $total,
            'done'    => $done,
            'doing'   => $doing,
            'todo'    => $total - $done - $doing,
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.admin.operations-center');
    }
}

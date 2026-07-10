<?php

namespace App\Livewire;

use App\Models\DocumentCategory;
use App\Models\FormSubmission;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Màn hình chính người dùng — "app sheet":
 * - Mỗi bộ tài liệu (TL) là một ô icon, badge = số BM cần nhập hôm nay.
 * - Chạm một ô → mở danh sách biểu mẫu của TL đó (task list, tích trạng thái).
 */
class Dashboard extends Component
{
    public string $ngayHienTai = '';
    public ?int   $openCategory = null;   // TL đang mở (null = màn launcher)

    /** Bảng màu + icon xoay theo TL (rule-based, ổn định theo id) */
    private const PALETTE = ['#0d7d8a', '#7c3aed', '#b45309', '#15803d', '#0369a1', '#be123c', '#0891b2', '#4f46e5'];

    public function mount(): void
    {
        $this->ngayHienTai = now()->toDateString();
    }

    public function changeDate(string $date): void
    {
        $this->ngayHienTai = $date;
    }

    public function openCat(int $id): void
    {
        $this->openCategory = $id;
    }

    public function closeCat(): void
    {
        $this->openCategory = null;
    }

    /** Đoán icon theo từ khóa trong tên TL (không AI, chỉ match chữ) */
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

    /**
     * Nhóm biểu mẫu theo TL, kèm màu/icon/đếm trạng thái theo ngày hiện tại.
     */
    public function getCategoriesWithFormsProperty(): array
    {
        $userId = auth()->id();
        $date   = $this->ngayHienTai;

        $categories = DocumentCategory::with(['formTemplates' => function ($q) {
            $q->where('trang_thai', 'active')->with('versions');
        }])->where('is_active', true)->orderBy('ten_muc')->get();

        $submissions = FormSubmission::where('user_id', $userId)
            ->where('ngay_nhap', $date)
            ->with('templateVersion.formTemplate')
            ->get()
            ->keyBy('templateVersion.formTemplate.id');

        $result = [];
        $i = 0;
        foreach ($categories as $category) {
            $forms = [];
            $done = 0;
            $requiredMissing = false;

            foreach ($category->formTemplates as $template) {
                $latestVersion = $template->versions()->first();
                if (! $latestVersion) {
                    continue;
                }

                $submission = $submissions[$template->id] ?? null;
                $trangThai  = $submission ? $submission->trang_thai : 'chua_nhap';
                $isComplete = $submission && $submission->trang_thai === 'hoan_thanh';

                if ($isComplete) {
                    $done++;
                }
                if ($template->is_required && ! $isComplete) {
                    $requiredMissing = true;
                }

                $forms[] = [
                    'template_id'   => $template->id,
                    'version_id'    => $latestVersion->id,
                    'ma_bm'         => $template->ma_bm,
                    'ten_bm'        => $template->ten_bm,
                    'is_required'   => $template->is_required,
                    'trang_thai'    => $trangThai,
                    'is_complete'   => $isComplete,
                    'submission_id' => $submission?->id,
                ];
            }

            if (empty($forms)) {
                continue;
            }

            $total = count($forms);
            $result[] = [
                'category_id'          => $category->id,
                'category_name'        => $category->ten_muc,
                'color'                => self::PALETTE[$i % count(self::PALETTE)],
                'icon'                 => $this->iconFor($category->ten_muc),
                'forms'                => $forms,
                'total'                => $total,
                'done'                 => $done,
                'todo'                 => $total - $done,
                'has_required_missing' => $requiredMissing,
            ];
            $i++;
        }

        return $result;
    }

    /** Tổng quan cả ngày cho thanh trên cùng */
    public function getSummaryProperty(): array
    {
        $cats = $this->categoriesWithForms;
        $total = array_sum(array_column($cats, 'total'));
        $done  = array_sum(array_column($cats, 'done'));
        return [
            'total'   => $total,
            'done'    => $done,
            'todo'    => $total - $done,
            'percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}

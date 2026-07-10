<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Lịch tháng cho 1 biểu mẫu: ngày ĐÃ điền nền xanh, CHƯA điền (đã qua) nền đỏ,
 * bấm vào ngày để mở phiếu ngày đó (xem/điền).
 */
class FormCalendar extends Component
{
    public int    $versionId;
    public int    $templateId;
    public string $thang;   // YYYY-MM

    public function mount(int $versionId): void
    {
        $v = FormTemplateVersion::findOrFail($versionId);
        $this->versionId  = $versionId;
        $this->templateId = $v->form_template_id;
        $this->thang      = now()->format('Y-m');
    }

    public function prevMonth(): void
    {
        $this->thang = Carbon::parse($this->thang . '-01')->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->thang = Carbon::parse($this->thang . '-01')->addMonth()->format('Y-m');
    }

    public function thisMonth(): void
    {
        $this->thang = now()->format('Y-m');
    }

    /** Ngày (Y-m-d) đã có bản ghi trong tháng đang xem. */
    public function getFilledDatesProperty(): array
    {
        [$y, $m] = explode('-', $this->thang);
        return FormSubmission::where('form_template_version_id', $this->versionId)
            ->where('user_id', auth()->id())
            ->whereYear('ngay_nhap', (int) $y)
            ->whereMonth('ngay_nhap', (int) $m)
            ->pluck('ngay_nhap')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->all();
    }

    public function render()
    {
        return view('livewire.form-calendar', [
            'template' => FormTemplate::find($this->templateId),
        ]);
    }
}

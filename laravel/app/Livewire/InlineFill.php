<?php

namespace App\Livewire;

use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use Livewire\Component;

/**
 * Điền trực tiếp GIỐNG BẢN GỐC — render docx bằng docx-preview (client), chèn ô nhập inline.
 * Giá trị gom ở trình duyệt, bấm Lưu đẩy 1 lần về server. Xuất .docx vẫn qua HtmlFormService::fill.
 */
class InlineFill extends Component
{
    public int    $versionId;
    public int    $templateId;
    public string $ngay;
    public array  $vals         = [];     // phẳng + t[tkey][i][col]
    public ?int   $submissionId = null;

    public function mount(int $versionId): void
    {
        $v = FormTemplateVersion::findOrFail($versionId);
        $this->versionId  = $versionId;
        $this->templateId = $v->form_template_id;
        $this->ngay       = now()->toDateString();

        $existing = FormSubmission::where('form_template_version_id', $versionId)
            ->where('user_id', auth()->id())
            ->where('ngay_nhap', $this->ngay)->first();
        if ($existing) {
            $this->vals         = $existing->data_json ?? [];
            $this->submissionId = $existing->id;
        }
    }

    /** Nhận vals gom từ client rồi lưu. */
    public function save($clientVals = null): void
    {
        if (is_array($clientVals)) {
            $this->vals = $clientVals;
        }
        $sub = FormSubmission::updateOrCreate(
            ['form_template_version_id' => $this->versionId, 'user_id' => auth()->id(), 'ngay_nhap' => $this->ngay],
            ['data_json' => $this->vals, 'trang_thai' => 'hoan_thanh']
        );
        $this->submissionId = $sub->id;
        session()->flash('success', 'Đã lưu. Bấm "Tải .docx" để xuất bản điền.');
    }

    public function render()
    {
        $t      = FormTemplate::find($this->templateId);
        $fields = FormTemplateVersion::find($this->versionId)?->fields ?? [];
        return view('livewire.inline-fill', [
            'template' => $t,
            'fields'   => $fields,
            'vals'     => $this->vals,
            'docxUrl'  => route('forms.inline-source', $this->versionId),
        ]);
    }
}

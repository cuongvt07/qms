<?php

namespace App\Livewire\Admin;

use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

/**
 * Cấu hình các ô nhập của 1 biểu mẫu mẫu (template có placeholder ${key}).
 * KEY khoá theo placeholder trong file .docx — chỉ đặt Nhãn / Kiểu / Bắt buộc.
 */
class TemplateConfig extends Component
{
    public int    $templateId = 0;
    public array  $fields     = [];
    public string $ghi_chu    = '';
    public array  $missingVars = [];   // placeholder trong file nhưng chưa có field

    public array $supportedTypes = [
        'text', 'textarea', 'number', 'date', 'select', 'radio', 'checkbox', 'repeatable_table',
    ];

    public function mount(int $templateId, TemplateService $templates): void
    {
        $this->templateId = $templateId;
        $template = FormTemplate::findOrFail($templateId);

        $version = $template->versions()->first();
        $this->fields = $version?->fields ?? [];

        // Đối chiếu với placeholder thực tế trong file — báo nếu thiếu/thừa.
        try {
            $abs  = Storage::disk('local')->path($template->file_goc_path);
            $vars = $templates->getVariables($abs);
            $have = array_column($this->fields, 'key');
            $this->missingVars = array_values(array_diff($vars, $have));
        } catch (\Throwable $e) {
            $this->missingVars = [];
        }
    }

    public function addMissing(TemplateService $templates): void
    {
        foreach ($templates->fieldsFromVariables($this->missingVars) as $f) {
            $this->fields[] = $f;
        }
        $this->missingVars = [];
    }

    public function removeField(int $i): void
    {
        array_splice($this->fields, $i, 1);
        $this->fields = array_values($this->fields);
    }

    /** Ẩn / hiện field: giữ trong mẫu nhưng không cho nhập (xuất để trắng). */
    public function toggleHidden(int $i): void
    {
        if (isset($this->fields[$i])) {
            $this->fields[$i]['hidden'] = ! ($this->fields[$i]['hidden'] ?? false);
        }
    }

    /** Ẩn tất cả field trống nhãn / nghi rác. */
    public function hideJunk(): void
    {
        foreach ($this->fields as $i => $f) {
            $lbl = trim((string) ($f['label'] ?? ''));
            if ($lbl === '' || mb_strlen($lbl, 'UTF-8') < 2 || preg_match('/^o(_\d+)?$/i', $f['key'] ?? '')) {
                $this->fields[$i]['hidden'] = true;
            }
        }
    }

    public function moveUp(int $i): void
    {
        if ($i > 0) {
            [$this->fields[$i - 1], $this->fields[$i]] = [$this->fields[$i], $this->fields[$i - 1]];
        }
    }

    public function moveDown(int $i): void
    {
        if ($i < count($this->fields) - 1) {
            [$this->fields[$i], $this->fields[$i + 1]] = [$this->fields[$i + 1], $this->fields[$i]];
        }
    }

    public function addOption(int $i): void      { $this->fields[$i]['options'][] = ''; }
    public function removeOption(int $i, int $o): void
    {
        array_splice($this->fields[$i]['options'], $o, 1);
        $this->fields[$i]['options'] = array_values($this->fields[$i]['options']);
    }

    public function addColumn(int $i): void
    {
        $this->fields[$i]['columns'][] = ['key' => '', 'label' => '', 'type' => 'text'];
    }

    public function removeColumn(int $i, int $c): void
    {
        array_splice($this->fields[$i]['columns'], $c, 1);
        $this->fields[$i]['columns'] = array_values($this->fields[$i]['columns']);
    }

    public function save(): void
    {
        $this->validate([
            'fields'         => 'required|array|min:1',
            'fields.*.key'   => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.type'  => 'required|in:' . implode(',', $this->supportedTypes),
        ], [
            'fields.*.key.required'   => 'Thiếu key placeholder.',
            'fields.*.label.required' => 'Nhãn không được để trống.',
        ]);

        $template = FormTemplate::findOrFail($this->templateId);
        $latest   = $template->versions()->first();

        FormTemplateVersion::create([
            'form_template_id' => $this->templateId,
            'version'          => $latest ? $latest->version + 1 : 1,
            'schema_json'      => ['fields' => array_values($this->fields)],
            'duyet_boi'        => auth()->id(),
            'ghi_chu'          => $this->ghi_chu ?: 'Cập nhật cấu hình ô nhập',
        ]);

        $template->update(['trang_thai' => 'active']);

        session()->flash('success', 'Đã lưu cấu hình. Biểu mẫu sẵn sàng để điền.');
        $this->redirect(route('admin.form-templates.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.template-config', [
            'template' => FormTemplate::find($this->templateId),
        ]);
    }
}

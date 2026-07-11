<?php

namespace App\Livewire\Admin;

use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use App\Services\ActivityLogger;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Cấu hình các ô nhập của 1 biểu mẫu mẫu (template có placeholder ${key}).
 * KEY khoá theo placeholder trong file .docx — chỉ đặt Nhãn / Kiểu / Bắt buộc.
 */
class TemplateConfig extends Component
{
    use WithFileUploads;

    public int    $templateId = 0;
    public array  $fields     = [];
    public string $ghi_chu    = '';
    public array  $missingVars = [];   // placeholder trong file nhưng chưa có field
    public array  $orphanVars  = [];   // field có key nhưng file mới không còn placeholder đó
    public $upload = null;             // file .docx đè lên bản gốc

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

    /**
     * Đè file .docx gốc bằng file người dùng upload (đã chỉnh đúng vị trí placeholder trong Word).
     * GIỮ NGUYÊN schema (nhãn/kiểu đã cấu hình) — chỉ cần KEY placeholder ${..} trùng là khớp lại.
     */
    public function replaceSource(TemplateService $templates): void
    {
        $this->validate(
            ['upload' => 'required|file|max:25600'],
            ['upload.required' => 'Chưa chọn file.', 'upload.max' => 'File tối đa 25MB.']
        );
        if (strtolower($this->upload->getClientOriginalExtension()) !== 'docx') {
            $this->addError('upload', 'Chỉ nhận file .docx');
            return;
        }

        // File phải mở được như .docx và đọc được placeholder
        try {
            $vars = $templates->getVariables($this->upload->getRealPath());
        } catch (\Throwable $e) {
            $this->addError('upload', 'File .docx không hợp lệ: ' . $e->getMessage());
            return;
        }

        $template = FormTemplate::findOrFail($this->templateId);
        $path     = $template->file_goc_path;

        // Sao lưu bản cũ 1 lần rồi đè lên đúng đường dẫn (mọi version/xuất .docx đều dùng chỗ này)
        if ($path && Storage::disk('local')->exists($path) && ! Storage::disk('local')->exists($path . '.bak')) {
            Storage::disk('local')->copy($path, $path . '.bak');
        }
        Storage::disk('local')->put($path, file_get_contents($this->upload->getRealPath()));

        // Xoá cache bản .docx phái sinh (ô thêm inline) của mọi version — nguồn đã đổi
        $dir = Storage::disk('local')->path('inline_aug');
        if (is_dir($dir)) {
            foreach ($template->versions as $v) {
                foreach (glob($dir . DIRECTORY_SEPARATOR . $v->id . '_*.docx') ?: [] as $g) {
                    @unlink($g);
                }
            }
        }

        // Đối chiếu lại placeholder với schema hiện tại
        $have = array_column($this->fields, 'key');
        $this->missingVars = array_values(array_diff($vars, $have));
        $this->orphanVars  = array_values(array_diff($have, $vars));

        $this->upload = null;
        ActivityLogger::log('config', "Thay file gốc .docx — biểu mẫu {$template->ma_bm}");

        $msg = 'Đã thay file gốc (bản cũ lưu ở .bak). File mới có ' . count($vars) . ' placeholder.';
        if ($this->missingVars) {
            $msg .= ' ' . count($this->missingVars) . ' placeholder chưa có ô — bấm "+ Thêm hết".';
        }
        if ($this->orphanVars) {
            $msg .= ' ' . count($this->orphanVars) . ' ô không còn trong file (sẽ để trắng khi xuất).';
        }
        session()->flash('success', $msg);
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

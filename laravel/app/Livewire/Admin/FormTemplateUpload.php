<?php

namespace App\Livewire\Admin;

use App\Models\DocumentCategory;
use App\Models\FormTemplate;
use App\Models\FormTemplateVersion;
use App\Services\DocConverterService;
use App\Services\TemplateService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Upload file .docx MẪU (đã gắn placeholder ${key}) → đọc placeholder thành ô nhập.
 * Không còn python/bóc cấu trúc: mỗi ${key} trong file là 1 field, điền web rồi
 * xuất lại đúng file gốc bằng PHPWord TemplateProcessor.
 */
class FormTemplateUpload extends Component
{
    use WithFileUploads;

    public $documentCategoryId = 0;
    public $maBm = '';
    public $tenBm = '';
    public $isRequired = false;

    #[Validate(['required', 'file', 'mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword', 'max:20480'])]
    public $docxFile = null;

    public $errorMessage = '';

    public function getDocumentCategoriesProperty()
    {
        return DocumentCategory::where('is_active', true)->orderBy('ten_muc')->get();
    }

    public function save(TemplateService $templates, DocConverterService $converter)
    {
        $this->errorMessage = '';
        $this->validate([
            'documentCategoryId' => 'required|exists:document_categories,id',
            'maBm'               => 'required|string|max:100',
            'tenBm'              => 'required|string|max:255',
            'docxFile'           => 'required|file|mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword|max:20480',
        ], [], [
            'documentCategoryId' => 'mục TL',
            'docxFile'           => 'file mẫu',
        ]);

        // Lưu file mẫu
        $path = $this->docxFile->store('docx-templates', 'local');
        $abs  = Storage::disk('local')->path($path);

        // Nếu là file .doc thì convert sang .docx
        $originalExtension = $this->docxFile->getClientOriginalExtension();
        if (strtolower($originalExtension) === 'doc') {
            try {
                $abs = $converter->convertDocToDocx($abs);
                // Cập nhật lại path trong storage
                $newPath = str_replace('.doc', '.docx', $path);
                $path = $newPath;
            } catch (\Throwable $e) {
                Storage::disk('local')->delete($path);
                $this->errorMessage = 'Không thể convert file .doc sang .docx: ' . $e->getMessage() 
                    . ' Vui lòng mở file trong Word và Save As định dạng .docx rồi thử lại.';
                return;
            }
        }

        // Đọc placeholder ${key}
        try {
            $vars = $templates->getVariables($abs);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            $this->errorMessage = 'Không đọc được file .docx: ' . $e->getMessage();
            return;
        }

        if (empty($vars)) {
            Storage::disk('local')->delete($path);
            $this->errorMessage = 'File chưa có placeholder nào. Mở file trong Word, gõ ${ten_bien} '
                . '(vd ${ho_ten}, ${ngay}) vào các ô cần điền rồi upload lại.';
            return;
        }

        $fields = $templates->fieldsFromVariables($vars);

        $template = FormTemplate::updateOrCreate(
            ['ma_bm' => $this->maBm],
            [
                'document_category_id' => $this->documentCategoryId,
                'ten_bm'               => $this->tenBm,
                'file_goc_path'        => $path,
                'trang_thai'           => 'active',
                'is_required'          => $this->isRequired,
            ]
        );

        $latest = $template->versions()->first();
        FormTemplateVersion::create([
            'form_template_id' => $template->id,
            'version'          => $latest ? $latest->version + 1 : 1,
            'schema_json'      => ['fields' => $fields],
            'duyet_boi'        => auth()->id(),
            'ghi_chu'          => 'Tạo từ placeholder file mẫu (' . count($fields) . ' ô)',
        ]);

        session()->flash('success', "Đã đọc {$this->maBm}: tìm thấy " . count($fields)
            . ' ô nhập. Đặt lại Nhãn/Kiểu cho dễ dùng.');

        return $this->redirect(route('admin.form-templates.review', $template->id), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.form-template-upload');
    }
}

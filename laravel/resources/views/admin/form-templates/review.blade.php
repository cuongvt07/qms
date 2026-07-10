<x-layouts.app>
    <x-slot name="title">Duyệt schema - {{ $template->ma_bm }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="mb-3">
            <a href="{{ route('admin.form-templates.index') }}" class="text-sm text-teal-600 hover:text-teal-800">← Danh sách biểu mẫu</a>
        </div>
        @livewire('admin.template-config', ['templateId' => $template->id])
    </div>
</x-layouts.app>

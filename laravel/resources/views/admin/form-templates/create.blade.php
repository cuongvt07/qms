<x-layouts.app>
    <x-slot name="title">Upload biểu mẫu mới</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('admin.form-templates.index') }}" class="text-sm text-teal-600 hover:text-teal-800">← Danh sách biểu mẫu</a>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @livewire('admin.form-template-upload')
        </div>
    </div>
</x-layouts.app>

<x-layouts.app>
    <x-slot name="title">Nhập liệu biểu mẫu</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('dashboard') }}" class="text-sm text-teal-600 hover:text-teal-800">← Về Dashboard</a>
        </div>

        @livewire('dynamic-form-renderer', ['versionId' => $versionId, 'ngayNhap' => $date])
    </div>
</x-layouts.app>

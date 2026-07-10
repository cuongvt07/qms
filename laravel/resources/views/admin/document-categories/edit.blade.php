<x-layouts.app>
    <x-slot name="title">Chỉnh sửa mục tài liệu</x-slot>

    <div class="max-w-2xl">
        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('admin.document-categories.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Quay lại</a>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Chỉnh sửa mục tài liệu (TL)</h1>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.document-categories.update', $documentCategory) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên mục <span class="text-red-500">*</span></label>
                    <input type="text" name="ten_muc" value="{{ old('ten_muc', $documentCategory->ten_muc) }}" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                    @error('ten_muc') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                    <textarea name="mo_ta" rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">{{ old('mo_ta', $documentCategory->mo_ta) }}</textarea>
                    @error('mo_ta') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="inline-flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $documentCategory->is_active))
                               class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <span class="text-sm text-gray-700">Đang sử dụng</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-4 py-2 bg-teal-600 text-white rounded-md text-sm font-medium hover:bg-teal-700">
                        Cập nhật
                    </button>
                    <a href="{{ route('admin.document-categories.index') }}"
                       class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>

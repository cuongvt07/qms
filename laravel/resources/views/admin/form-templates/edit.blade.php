<x-layouts.app>
    <x-slot name="title">Chỉnh sửa biểu mẫu</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('admin.form-templates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Danh sách biểu mẫu</a>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Chỉnh sửa biểu mẫu</h1>
        <p class="text-sm text-gray-400 font-mono mb-6">{{ $template->ma_bm }}</p>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.form-templates.update', $template) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mục tài liệu (TL) <span class="text-red-500">*</span></label>
                    <select name="document_category_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($cat->id == $template->document_category_id)>{{ $cat->ten_muc }}</option>
                        @endforeach
                    </select>
                    @error('document_category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mã biểu mẫu <span class="text-red-500">*</span></label>
                        <input type="text" name="ma_bm" value="{{ old('ma_bm', $template->ma_bm) }}"
                               class="w-full rounded-lg border-gray-300 text-sm font-mono focus:border-teal-500">
                        @error('ma_bm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                        <select name="trang_thai" class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500">
                            <option value="draft" @selected($template->trang_thai === 'draft')>Nháp</option>
                            <option value="active" @selected($template->trang_thai === 'active')>Hoạt động</option>
                            <option value="archived" @selected($template->trang_thai === 'archived')>Lưu trữ</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tên biểu mẫu <span class="text-red-500">*</span></label>
                    <input type="text" name="ten_bm" value="{{ old('ten_bm', $template->ten_bm) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-teal-500">
                    @error('ten_bm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="inline-flex items-center gap-2">
                        <input type="hidden" name="is_required" value="0">
                        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $template->is_required))
                               class="rounded border-gray-300 text-red-500 focus:ring-red-200">
                        <span class="text-sm text-gray-700">Bắt buộc (cảnh báo đỏ khi quên nhập trong ngày)</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-5 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700">Lưu</button>
                    <a href="{{ route('admin.form-templates.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Hủy</a>
                </div>
            </form>
        </div>

        {{-- Sửa field: sang Duyệt schema --}}
        <div class="bg-teal-50 border border-teal-200 rounded-xl p-4 mt-5 flex items-center gap-4">
            <div class="flex-1">
                <p class="font-medium text-teal-800 text-sm">Sửa các field (xóa field không cần điền, đổi kiểu, thứ tự)</p>
                <p class="text-xs text-teal-600 mt-0.5">Việc thêm/xóa/sửa field nằm ở màn Duyệt schema và sẽ tạo phiên bản mới.</p>
            </div>
            <a href="{{ route('admin.form-templates.review', $template) }}"
               class="shrink-0 px-4 py-2 bg-white border border-teal-300 text-teal-700 rounded-lg text-sm font-medium hover:bg-teal-100">Duyệt schema →</a>
        </div>
    </div>
</x-layouts.app>

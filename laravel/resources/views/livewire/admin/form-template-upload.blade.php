<div class="max-w-2xl">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Upload biểu mẫu mới</h2>
    <p class="text-sm text-gray-500 mb-5">
        Mở file Word gốc, gõ <code class="bg-gray-100 px-1 rounded">${'{ten_bien}'}</code> vào các ô cần điền
        (vd <code class="bg-gray-100 px-1 rounded">${'{ho_ten}'}</code>, <code class="bg-gray-100 px-1 rounded">${'{ngay}'}</code>),
        lưu lại rồi upload. Hệ thống đọc các placeholder đó thành ô nhập.
    </p>

    @if($errorMessage)
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">{{ $errorMessage }}</div>
    @endif

    <form wire:submit="save">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mục tài liệu (TL) *</label>
                <select wire:model="documentCategoryId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-400 text-sm">
                    <option value="0">-- Chọn mục --</option>
                    @foreach($this->documentCategories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->ten_muc }}</option>
                    @endforeach
                </select>
                @error('documentCategoryId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mã biểu mẫu *</label>
                <input type="text" wire:model="maBm" placeholder="VD: BM.01_QTQL.07"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-400 text-sm font-mono">
                @error('maBm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tên biểu mẫu *</label>
                <input type="text" wire:model="tenBm" placeholder="VD: Báo cáo công việc hằng ngày"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-400 text-sm">
                @error('tenBm') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="isRequired" class="w-4 h-4 rounded text-red-500 border-gray-300">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Biểu mẫu bắt buộc</span>
                        <p class="text-xs text-gray-400">Dashboard cảnh báo khi ngày đó chưa nhập.</p>
                    </div>
                </label>
            </div>

            <div x-data="{ uploading: false, progress: 0 }"
                 x-on:livewire-upload-start="uploading = true; progress = 0"
                 x-on:livewire-upload-finish="uploading = false; progress = 100"
                 x-on:livewire-upload-error="uploading = false"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <label class="block text-sm font-medium text-gray-700 mb-1">File mẫu .docx (đã gắn ${'{...}'}) *</label>
                <input type="file" wire:model="docxFile" accept=".docx"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                @error('docxFile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <div x-show="uploading" x-cloak class="mt-2">
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                        <span>Đang tải file lên…</span><span x-text="progress + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-teal-500 h-2 rounded-full transition-all duration-150" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="px-6 py-2.5 bg-teal-600 text-white rounded-lg text-sm font-medium hover:bg-teal-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="save">Đọc placeholder & tạo ô nhập</span>
                <span wire:loading wire:target="save">Đang đọc…</span>
            </button>
        </div>
    </form>
</div>

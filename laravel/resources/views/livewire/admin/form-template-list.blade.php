<div>
    {{-- Tiêu đề + hành động --}}
    <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Quản lý biểu mẫu (BM)</h1>
        <a href="{{ route('admin.form-templates.create') }}"
           class="px-3 py-2 bg-teal-600 text-white rounded-lg text-sm font-medium hover:bg-teal-700">
            + Upload biểu mẫu
        </a>
    </div>

    {{-- Bộ lọc --}}
    <div class="flex flex-wrap items-center gap-2 mb-3">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="q" placeholder="Tìm mã BM hoặc tên biểu mẫu…"
                   class="w-full border border-gray-300 rounded-xl text-sm pl-9 pr-3 py-2 focus:border-teal-500 focus:ring-teal-500">
        </div>
        <select wire:model.live="catId" class="border border-gray-300 rounded-xl text-sm px-3 py-2 focus:border-teal-500 max-w-[240px]">
            <option value="">Tất cả mục TL</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->ten_muc }} ({{ $c->form_templates_count }})</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="border border-gray-300 rounded-xl text-sm px-3 py-2 focus:border-teal-500">
            <option value="">Mọi trạng thái</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
        @if($q !== '' || $catId !== '' || $status !== '')
            <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-teal-600 px-2 py-2">✕ Xóa lọc</button>
        @endif
    </div>

    <p class="text-xs text-gray-400 mb-2">{{ $templates->total() }} biểu mẫu</p>

    {{-- Bảng --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mã BM</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên biểu mẫu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mục TL</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ver</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 whitespace-nowrap">{{ $template->ma_bm }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $template->ten_bm }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $template->documentCategory->ten_muc ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($template->versions->first())v{{ $template->versions->first()->version }}
                                @else<span class="text-gray-400">—</span>@endif
                            </td>
                            <td class="px-4 py-3">
                                @switch($template->trang_thai)
                                    @case('active')<span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Active</span>@break
                                    @case('draft')<span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">Draft</span>@break
                                    @case('archived')<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Archived</span>@break
                                @endswitch
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.form-templates.review', $template) }}" class="text-xs text-teal-600 hover:text-teal-800">Đối chiếu</a>
                                <a href="{{ route('admin.form-templates.edit', $template) }}" class="text-xs text-gray-500 hover:text-gray-700">Sửa</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Không có biểu mẫu nào khớp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $templates->links() }}
        </div>
    </div>
</div>

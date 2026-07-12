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

    <p class="text-xs text-gray-400 mb-2">{{ $total }} biểu mẫu · {{ $grouped->count() }} mục TL</p>

    {{-- Nhóm theo Mục TL --}}
    <div class="space-y-4">
        @forelse($grouped as $catName => $items)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                {{-- Tiêu đề mục --}}
                <div class="flex items-center gap-2 px-4 py-2.5 bg-gray-50 border-b border-gray-200">
                    <svg class="w-4 h-4 text-teal-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9A2.25 2.25 0 0019.5 6.75h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                    <h2 class="text-sm font-bold text-gray-700">{{ $catName === '~ Chưa phân mục' ? 'Chưa phân mục' : $catName }}</h2>
                    <span class="text-xs text-gray-400">({{ $items->count() }})</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach($items as $template)
                                @php $ver = $template->versions->first(); @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-500 whitespace-nowrap align-middle">{{ $template->ma_bm }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-800 align-middle">
                                        {{ $template->ten_bm }}
                                        @if($ver)<span class="ml-1 text-xs text-gray-400">v{{ $ver->version }}</span>@endif
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        @switch($template->trang_thai)
                                            @case('active')<span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Active</span>@break
                                            @case('draft')<span class="px-2 py-0.5 rounded-full text-xs bg-yellow-100 text-yellow-700">Draft</span>@break
                                            @case('archived')<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Archived</span>@break
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap align-middle">
                                        <div class="inline-flex items-center gap-2">
                                            @if($ver)
                                                <a href="{{ route('forms.register', $ver->id) }}"
                                                   class="inline-flex items-center gap-1 text-xs bg-teal-600 text-white rounded-lg px-3 py-1.5 font-semibold hover:bg-teal-700" title="Sang tab nhập liệu">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
                                                    Nhập
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-300" title="Chưa có phiên bản để nhập">Nhập</span>
                                            @endif
                                            <a href="{{ route('admin.form-templates.review', $template) }}" class="text-xs text-teal-600 hover:text-teal-800">Đối chiếu</a>
                                            <a href="{{ route('admin.form-templates.edit', $template) }}" class="text-xs text-gray-500 hover:text-gray-700">Sửa</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-10 text-center text-gray-400">Không có biểu mẫu nào khớp bộ lọc.</div>
        @endforelse
    </div>
</div>

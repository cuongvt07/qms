<div class="max-w-3xl mx-auto pb-10">
    @php
        $iconPaths = [
            'chart'  => '<path d="M5 20V10M12 20V4M19 20v-7"/>',
            'wrench' => '<path d="M14 4l6 6-9 9H5v-6z"/><path d="M12 6l6 6"/>',
            'thermo' => '<path d="M12 2v14"/><circle cx="12" cy="19" r="3"/><path d="M9 6h3"/>',
            'shield' => '<path d="M12 3l7 4v5c0 4-3 7-7 9-4-2-7-5-7-9V7z"/><path d="M9 12l2 2 4-4"/>',
            'users'  => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-4 3-6 7-6s7 2 7 6"/>',
            'cart'   => '<path d="M6 6h15l-2 9H8z"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M6 6 5 3H3"/>',
            'doc'    => '<path d="M6 3h9l3 3v15H6z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
        ];
        $summary = $this->summary;
        $today   = now()->toDateString();
        $open    = $this->openCategory
            ? collect($this->categoriesWithForms)->firstWhere('category_id', $this->openCategory)
            : null;
    @endphp

    {{-- ── Thanh trên: chào + tiến độ + ngày ── --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-800 truncate">Chào, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($ngayHienTai)->locale('vi')->isoFormat('dddd, DD/MM/YYYY') }}
            </p>
        </div>

        {{-- Vòng tiến độ --}}
        <div class="ml-auto shrink-0 flex items-center gap-2">
            <div class="relative w-12 h-12">
                <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#e5e7eb" stroke-width="4"/>
                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#0d7d8a" stroke-width="4"
                            stroke-linecap="round" stroke-dasharray="{{ 97.4 }}"
                            stroke-dashoffset="{{ 97.4 * (1 - $summary['percent'] / 100) }}"/>
                </svg>
                <span class="absolute inset-0 grid place-items-center text-[11px] font-bold text-gray-700">{{ $summary['percent'] }}%</span>
            </div>
            <div class="text-xs text-gray-500 leading-tight">
                <div><span class="font-semibold text-gray-800">{{ $summary['done'] }}</span>/{{ $summary['total'] }} xong</div>
                @if($summary['todo'] > 0)
                    <div class="text-red-500">Còn {{ $summary['todo'] }} việc</div>
                @else
                    <div class="text-green-600">Hoàn tất 🎉</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Điều hướng ngày (gọn) --}}
    <div class="flex items-center gap-2 mb-6 text-sm">
        <button wire:click="changeDate('{{ \Carbon\Carbon::parse($ngayHienTai)->subDay()->toDateString() }}')"
                class="px-2.5 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">‹</button>
        <input type="date" wire:model.live="ngayHienTai"
               class="border border-gray-300 rounded-lg text-sm px-2 py-1 focus:border-teal-500">
        <button wire:click="changeDate('{{ \Carbon\Carbon::parse($ngayHienTai)->addDay()->toDateString() }}')"
                class="px-2.5 py-1 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">›</button>
        @if($ngayHienTai !== $today)
            <button wire:click="changeDate('{{ $today }}')"
                    class="px-3 py-1 bg-teal-50 border border-teal-300 rounded-lg text-teal-700 hover:bg-teal-100">Hôm nay</button>
        @endif
    </div>

    @if(! $open)
        {{-- ══════════ LAUNCHER: mỗi TL là một ô icon ══════════ --}}
        @if(count($this->categoriesWithForms) > 0)
            <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-x-3 gap-y-6">
                @foreach($this->categoriesWithForms as $cat)
                    <button wire:click="openCat({{ $cat['category_id'] }})" wire:key="tl-{{ $cat['category_id'] }}"
                            class="group flex flex-col items-center gap-2 focus:outline-none">
                        <div class="relative">
                            <div class="w-[68px] h-[68px] rounded-[20px] grid place-items-center text-white shadow-lg
                                        transition group-hover:scale-105 group-active:scale-95"
                                 style="background-color: {{ $cat['color'] }}; box-shadow: 0 8px 18px -6px {{ $cat['color'] }}66, inset 0 -8px 14px rgba(0,0,0,.12);">
                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths[$cat['icon']] ?? $iconPaths['doc'] !!}</svg>
                            </div>
                            {{-- Badge --}}
                            @if($cat['todo'] > 0)
                                <span class="absolute -top-1.5 -right-1.5 min-w-[22px] h-[22px] px-1.5 rounded-full bg-red-500 text-white
                                             text-xs font-bold grid place-items-center border-2 border-white shadow">{{ $cat['todo'] }}</span>
                            @else
                                <span class="absolute -top-1.5 -right-1.5 w-[22px] h-[22px] rounded-full bg-green-500 text-white
                                             grid place-items-center border-2 border-white shadow">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M4 12l5 5L20 6"/></svg>
                                </span>
                            @endif
                        </div>
                        <span class="text-[12px] font-semibold text-gray-700 text-center leading-tight line-clamp-2">{{ $cat['category_name'] }}</span>
                    </button>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">Chưa có biểu mẫu nào cho hôm nay.</p>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.form-templates.create') }}" class="text-teal-600 text-sm mt-2 inline-block">+ Thêm biểu mẫu</a>
                @endif
            </div>
        @endif
    @else
        {{-- ══════════ TASK LIST của 1 TL ══════════ --}}
        <div class="flex items-center gap-3 mb-4">
            <button wire:click="closeCat"
                    class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 grid place-items-center text-gray-600 hover:bg-gray-100">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
            </button>
            <div class="w-9 h-9 rounded-xl grid place-items-center text-white shrink-0" style="background-color: {{ $open['color'] }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths[$open['icon']] ?? $iconPaths['doc'] !!}</svg>
            </div>
            <div class="min-w-0">
                <h2 class="font-bold text-gray-800 truncate">{{ $open['category_name'] }}</h2>
                <p class="text-xs text-gray-500">{{ $open['done'] }}/{{ $open['total'] }} đã nộp hôm nay</p>
            </div>
        </div>

        <div class="space-y-2.5">
            @foreach($open['forms'] as $form)
                <a href="{{ route('forms.register', ['versionId' => $form['version_id']]) }}"
                   wire:key="bm-{{ $form['template_id'] }}"
                   class="flex items-center gap-3 bg-white border rounded-2xl p-3.5 shadow-sm hover:shadow transition
                          {{ $form['is_complete'] ? 'border-green-200' : ($form['is_required'] ? 'border-red-100' : 'border-gray-200') }}">
                    {{-- Tick trạng thái --}}
                    <span class="shrink-0 w-7 h-7 rounded-lg grid place-items-center border-2
                        {{ $form['is_complete'] ? 'bg-green-500 border-green-500' : 'border-gray-300' }}">
                        @if($form['is_complete'])
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><path d="M4 12l5 5L20 6"/></svg>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $form['ten_bm'] }}</p>
                        <p class="text-[11px] font-mono text-gray-400 truncate">
                            {{ $form['ma_bm'] }}
                            @if($form['is_required'])<span class="text-red-500 font-sans font-semibold ml-1">★ Bắt buộc</span>@endif
                        </p>
                    </div>

                    {{-- Trạng thái chữ --}}
                    <span class="shrink-0 text-xs font-semibold
                        {{ $form['is_complete'] ? 'text-green-600' : ($form['trang_thai'] === 'nhap_dang_do' ? 'text-amber-600' : 'text-gray-400') }}">
                        {{ $form['is_complete'] ? 'Xong' : ($form['trang_thai'] === 'nhap_dang_do' ? 'Đang dở' : 'Nhập →') }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>

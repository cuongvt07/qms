<div class="max-w-4xl mx-auto pb-16">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('admin.operations') }}" class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 grid place-items-center text-gray-600 hover:bg-gray-100 shrink-0" title="Về Điều hành">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-lg font-bold text-gray-800">Nhật ký hoạt động</h1>
            <p class="text-xs text-gray-400">Ghi lại thao tác người dùng theo thời gian & phiên đăng nhập</p>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="bg-white border border-gray-200 rounded-xl p-3 mb-4 flex flex-wrap items-center gap-2">
        <select wire:model.live="userId" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 outline-none focus:border-teal-500">
            <option value="">Mọi người dùng</option>
            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
        </select>
        <select wire:model.live="action" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 outline-none focus:border-teal-500">
            <option value="">Mọi hành động</option>
            @foreach($actions as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
        </select>
        <input type="date" wire:model.live="date" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 outline-none focus:border-teal-500">
        <input type="text" wire:model.live.debounce.400ms="q" placeholder="Tìm mô tả…" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 outline-none focus:border-teal-500 flex-1 min-w-[140px]">
        @if($session)
            <span class="text-xs bg-teal-50 text-teal-700 border border-teal-200 rounded-lg px-2 py-1 flex items-center gap-1">
                Phiên #{{ substr($session, 0, 8) }}…
                <button wire:click="$set('session','')" class="hover:text-teal-900">✕</button>
            </span>
        @endif
        <button wire:click="clearFilters" class="text-xs text-gray-500 hover:underline">Xoá lọc</button>
    </div>

    {{-- Danh sách gom theo ngày --}}
    @php $grouped = $logs->getCollection()->groupBy(fn ($l) => optional($l->created_at)->format('Y-m-d')); @endphp
    @php
        $map = [
            'login' => ['Đăng nhập', 'bg-green-50 text-green-700'],
            'logout' => ['Đăng xuất', 'bg-gray-100 text-gray-600'],
            'save' => ['Lưu', 'bg-teal-50 text-teal-700'],
            'copy' => ['Sao chép', 'bg-blue-50 text-blue-700'],
            'upload' => ['Đính kèm', 'bg-purple-50 text-purple-700'],
            'delete_attachment' => ['Xoá tệp', 'bg-red-50 text-red-600'],
            'export' => ['Tải .docx', 'bg-cyan-50 text-cyan-700'],
        ];
    @endphp
    @forelse($grouped as $day => $items)
        <div class="text-xs font-semibold text-gray-400 mt-4 mb-1.5 px-1">{{ \Carbon\Carbon::parse($day)->format('d/m/Y') }}</div>
        <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100 overflow-hidden">
            @foreach($items as $l)
                @php $a = $map[$l->action] ?? [$l->action, 'bg-gray-100 text-gray-600']; @endphp
                <div class="flex items-center gap-3 px-3.5 py-2.5 hover:bg-gray-50/60">
                    <span class="text-xs text-gray-400 font-mono w-12 shrink-0">{{ optional($l->created_at)->format('H:i') }}</span>
                    <span class="text-[11px] font-medium rounded-md px-2 py-0.5 shrink-0 {{ $a[1] }}">{{ $a[0] }}</span>
                    <span class="text-sm text-gray-700 flex-1 min-w-0 truncate" title="{{ $l->description }}">{{ $l->description }}</span>
                    <span class="text-xs text-gray-500 shrink-0 hidden sm:block">{{ $l->user?->name ?? '—' }}</span>
                    @if($l->session_id)
                        <button wire:click="filterSession('{{ $l->session_id }}')" title="Lọc theo phiên này" class="text-[10px] font-mono text-gray-300 hover:text-teal-600 shrink-0">#{{ substr($l->session_id, 0, 6) }}</button>
                    @endif
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-center text-gray-400 py-16 text-sm">Chưa có hoạt động nào khớp bộ lọc.</div>
    @endforelse

    <div class="mt-4">{{ $logs->links() }}</div>
</div>

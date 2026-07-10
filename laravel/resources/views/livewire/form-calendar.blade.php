<div class="max-w-2xl mx-auto">
    @php
        [$y, $m] = array_map('intval', explode('-', $thang));
        $first    = \Carbon\Carbon::create($y, $m, 1);
        $daysIn   = $first->daysInMonth;
        $offset   = $first->dayOfWeekIso - 1;      // 0=Thứ 2 … 6=CN
        $today    = now()->toDateString();
        $filled   = $this->filledDates;
        $countFilled = count($filled);
    @endphp

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 grid place-items-center text-gray-600 hover:bg-gray-100 shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-lg font-bold text-gray-800 truncate">{{ $template?->ten_bm }}</h1>
            <p class="text-xs text-gray-400 font-mono">{{ $template?->ma_bm }} · lịch nhập theo ngày</p>
        </div>
        <a href="{{ route('forms.register', $versionId) }}" class="shrink-0 text-sm border border-teal-300 text-teal-700 rounded-lg px-3 py-1.5 hover:bg-teal-50">Mở phiếu</a>
    </div>

    {{-- Điều hướng tháng --}}
    <div class="flex items-center justify-between mb-3">
        <button wire:click="prevMonth" class="w-9 h-9 rounded-lg border border-gray-200 grid place-items-center text-gray-500 hover:bg-gray-50">‹</button>
        <div class="flex items-center gap-2">
            <span class="text-base font-semibold text-gray-800">Tháng {{ $m }}/{{ $y }}</span>
            <button wire:click="thisMonth" class="text-xs text-teal-600 border border-teal-200 rounded-md px-2 py-0.5 hover:bg-teal-50">Nay</button>
        </div>
        <button wire:click="nextMonth" class="w-9 h-9 rounded-lg border border-gray-200 grid place-items-center text-gray-500 hover:bg-gray-50">›</button>
    </div>

    {{-- Lịch --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-3">
        <div class="grid grid-cols-7 gap-1.5 mb-1.5 text-center text-[11px] font-semibold text-gray-400">
            @foreach(['T2','T3','T4','T5','T6','T7','CN'] as $wd)<div>{{ $wd }}</div>@endforeach
        </div>
        <div class="grid grid-cols-7 gap-1.5">
            @for($i = 0; $i < $offset; $i++)<div></div>@endfor
            @for($d = 1; $d <= $daysIn; $d++)
                @php
                    $ds = sprintf('%04d-%02d-%02d', $y, $m, $d);
                    $isFilled = in_array($ds, $filled, true);
                    $isFuture = $ds > $today;
                    $isToday  = $ds === $today;
                @endphp
                <a href="{{ route('forms.register', ['versionId' => $versionId, 'date' => $ds]) }}"
                   wire:key="cal-{{ $ds }}"
                   @class([
                       'aspect-square rounded-xl grid place-items-center text-sm font-medium relative transition border',
                       'bg-green-50 border-green-300 text-green-700 hover:bg-green-100' => $isFilled,
                       'bg-red-50 border-red-200 text-red-500 hover:bg-red-100' => !$isFilled && !$isFuture,
                       'bg-gray-50 border-gray-100 text-gray-400 hover:bg-gray-100' => !$isFilled && $isFuture,
                       'ring-2 ring-teal-400' => $isToday,
                   ])>
                    {{ $d }}
                    @if($isFilled)
                        <svg class="absolute top-1 right-1 w-3 h-3 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 12l5 5L20 6"/></svg>
                    @endif
                </a>
            @endfor
        </div>
    </div>

    {{-- Chú thích --}}
    <div class="flex items-center justify-center gap-4 mt-3 text-xs text-gray-500">
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-green-100 border border-green-300"></span> đã điền ({{ $countFilled }})</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-50 border border-red-200"></span> chưa điền</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-50 border border-gray-200"></span> sắp tới</span>
    </div>
    <p class="text-center text-xs text-gray-400 mt-2">Bấm 1 ngày để mở phiếu ngày đó (xem data đã lưu hoặc nhập mới).</p>
</div>

<div>
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
        $kpi = $this->kpi;
        $today = now()->toDateString();
        $open = $this->openCat ? collect($this->board)->firstWhere('id', $this->openCat) : null;
    @endphp

    {{-- Header --}}
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Trung tâm điều hành</h1>
            <p class="text-sm text-gray-500">Theo dõi nhập liệu toàn trung tâm ·
                {{ \Carbon\Carbon::parse($ngay)->locale('vi')->isoFormat('dddd, DD/MM/YYYY') }}</p>
        </div>
        <div class="ml-auto flex items-center gap-2 text-sm">
            <button wire:click="changeDate('{{ \Carbon\Carbon::parse($ngay)->subDay()->toDateString() }}')" class="px-2.5 py-1.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">‹</button>
            <input type="date" wire:model.live="ngay" class="border border-gray-300 rounded-lg text-sm px-2.5 py-1.5 focus:border-teal-500">
            <button wire:click="changeDate('{{ \Carbon\Carbon::parse($ngay)->addDay()->toDateString() }}')" class="px-2.5 py-1.5 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50">›</button>
            @if($ngay !== $today)<button wire:click="changeDate('{{ $today }}')" class="px-3 py-1.5 bg-teal-50 border border-teal-300 rounded-lg text-teal-700">Hôm nay</button>@endif
        </div>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
        @foreach([
            ['Cần nhập hôm nay', $kpi['total'], '#0d7d8a', $kpi['total'] ? 100 : 0, 'trên '.count($this->board).' bộ tài liệu'],
            ['Đã hoàn thành', $kpi['done'].' / '.$kpi['total'], '#15803d', $kpi['percent'], $kpi['percent'].'% biểu mẫu'],
            ['Đang nhập dở', $kpi['doing'], '#b45309', $kpi['total'] ? round($kpi['doing']/$kpi['total']*100) : 0, 'lưu nháp, chưa xong'],
            ['Chưa nhập', $kpi['todo'], '#c02636', $kpi['total'] ? round($kpi['todo']/$kpi['total']*100) : 0, 'cần đôn đốc'],
        ] as [$cap,$val,$color,$pct,$sub])
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <div class="flex items-center gap-2 text-xs text-gray-500"><span class="w-1.5 h-4 rounded" style="background:{{ $color }}"></span>{{ $cap }}</div>
                <div class="text-3xl font-bold text-gray-800 mt-2 tabular-nums">{{ $val }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $sub }}</div>
                <div class="h-1.5 rounded bg-gray-100 mt-3 overflow-hidden"><div class="h-full rounded" style="width:{{ $pct }}%;background:{{ $color }}"></div></div>
            </div>
        @endforeach
    </div>

    {{-- Thẻ TL task-menu --}}
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Bộ tài liệu</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        @foreach($this->board as $c)
            <button wire:click="selectCat({{ $c['id'] }})" wire:key="opc-{{ $c['id'] }}"
                    @class([
                        'text-left bg-white rounded-xl border p-4 shadow-sm hover:shadow transition',
                        'border-teal-400 ring-2 ring-teal-100' => $open && $open['id']===$c['id'],
                        'border-gray-200' => !($open && $open['id']===$c['id']),
                    ])>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl grid place-items-center text-white shrink-0" style="background:{{ $c['color'] }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths[$c['icon']] ?? $iconPaths['doc'] !!}</svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[11px] font-mono text-gray-400">{{ $c['code'] }}</div>
                        <div class="font-semibold text-gray-800 text-sm truncate">{{ $c['name'] }}</div>
                    </div>
                    <div class="ml-auto text-right shrink-0">
                        <div class="text-lg font-bold text-gray-800 tabular-nums">{{ $c['done'] }}/{{ $c['total'] }}</div>
                        <div class="text-[11px] text-gray-400">đã nộp</div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden flex">
                        <div class="h-full" style="width:{{ $c['total']?round($c['done']/$c['total']*100):0 }}%;background:#15803d"></div>
                        <div class="h-full" style="width:{{ $c['total']?round($c['doing']/$c['total']*100):0 }}%;background:#b45309"></div>
                    </div>
                    @if($c['todo']>0)
                        <span class="text-[11px] font-semibold text-red-500">còn {{ $c['todo'] }}</span>
                    @else
                        <span class="text-[11px] font-semibold text-green-600">đủ</span>
                    @endif
                </div>
            </button>
        @endforeach
    </div>

    {{-- Chi tiết BM của TL đang chọn --}}
    @if($open)
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3.5 border-b border-gray-100">
                <div class="w-8 h-8 rounded-lg grid place-items-center text-white shrink-0" style="background:{{ $open['color'] }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $iconPaths[$open['icon']] ?? $iconPaths['doc'] !!}</svg>
                </div>
                <h3 class="font-semibold text-gray-800">{{ $open['name'] }}</h3>
                <span class="text-xs text-gray-400">{{ $open['total'] }} biểu mẫu</span>
                <a href="{{ route('admin.form-templates.create') }}" class="ml-auto text-sm bg-teal-600 text-white rounded-lg px-3 py-1.5 font-medium hover:bg-teal-700">+ Upload BM</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase px-5 py-2.5">Biểu mẫu</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase px-3 py-2.5">Bắt buộc</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase px-3 py-2.5">Đã nộp hôm nay</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase px-3 py-2.5">Trạng thái</th>
                            <th class="px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($open['forms'] as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-800">{{ $f['ten_bm'] }}</div>
                                    <div class="text-[11px] font-mono text-gray-400">{{ $f['ma_bm'] }}</div>
                                </td>
                                <td class="px-3 py-3">
                                    @if($f['is_required'])<span class="text-xs font-semibold text-red-600">★ Có</span>@else<span class="text-xs text-gray-400">—</span>@endif
                                </td>
                                <td class="px-3 py-3 text-gray-700 tabular-nums">
                                    <span class="text-green-600 font-semibold">{{ $f['done_count'] }}</span>
                                    @if($f['doing_count'])<span class="text-amber-600 ml-2">+{{ $f['doing_count'] }} dở</span>@endif
                                </td>
                                <td class="px-3 py-3">
                                    @if($f['status']==='done')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Có người nộp</span>
                                    @elseif($f['status']==='doing')
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Đang nhập</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-1 rounded-full"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Chưa nhập</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.form-templates.review', $f['template_id']) }}" class="text-xs text-teal-600 hover:text-teal-800 font-medium">Duyệt schema</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <p class="text-sm text-gray-400 text-center py-6">Chọn một bộ tài liệu ở trên để xem chi tiết biểu mẫu.</p>
    @endif
</div>

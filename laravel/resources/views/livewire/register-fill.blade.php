<div class="max-w-3xl mx-auto pb-28">
    @php
        $tpl = $this->version->formTemplate;
        $fields = $this->fields;
        $A = $active;
        $row = $rows[$A] ?? null;
    @endphp

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 grid place-items-center text-gray-600 hover:bg-gray-100 shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
        </a>
        <div class="min-w-0 flex-1" x-data="{ edit: false }">
            <div x-show="!edit" class="flex items-center gap-2 min-w-0">
                <h1 class="text-lg font-bold text-gray-800 truncate">{{ $title }}</h1>
                <button type="button" @click="edit=true; $nextTick(()=>$refs.ti.focus())" class="shrink-0 text-gray-400 hover:text-teal-600" title="Sửa tên">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                </button>
            </div>
            <div x-show="edit" x-cloak class="flex items-center gap-2">
                <input x-ref="ti" type="text" wire:model="title" @keydown.enter.prevent="edit=false; $wire.saveTitle()" @keydown.escape="edit=false"
                       class="flex-1 min-w-0 border border-teal-300 rounded-lg text-base font-semibold px-3 py-1.5 outline-none focus:ring-2 focus:ring-teal-100">
                <button type="button" @click="edit=false; $wire.saveTitle()" class="shrink-0 bg-teal-600 text-white rounded-lg px-3 py-1.5 text-sm font-semibold">Lưu</button>
            </div>
            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $tpl->ma_bm }} · phiếu theo ngày</p>
        </div>
        <a href="{{ route('forms.calendar', $versionId) }}"
           class="shrink-0 inline-flex items-center gap-1.5 text-sm border border-gray-300 text-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50" title="Xem lịch nhập theo ngày">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
            Lịch
        </a>
        <a href="{{ route('forms.inline', $versionId) }}"
           class="shrink-0 inline-flex items-center gap-1.5 text-sm border border-teal-300 text-teal-700 rounded-lg px-3 py-1.5 hover:bg-teal-50" title="Điền trực tiếp trên giao diện giống bản gốc">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
            Giống bản gốc
        </a>
    </div>

    @if(session('success'))<div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-2.5 rounded-xl mb-3 text-sm">⚠ {{ session('warning') }}</div>@endif
    @error('rows')<div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ $message }}</div>@enderror

    {{-- ── Tab các ngày ── --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 mb-1" style="scrollbar-width:thin">
        @foreach($rows as $i => $r)
            @php $done = ($r['trang_thai'] ?? '')==='hoan_thanh'; @endphp
            <button type="button" wire:click="setActive({{ $i }})" wire:key="tab-{{ $i }}-{{ $r['ngay'] }}"
                    @class([
                        'shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-t-xl border text-sm font-medium transition',
                        'bg-white border-gray-200 border-b-white text-teal-700 -mb-px' => $A===$i,
                        'bg-gray-50 border-transparent text-gray-500 hover:text-gray-700' => $A!==$i,
                    ])>
                @if($done)<span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>@endif
                {{ $r['ngay'] ? \Carbon\Carbon::parse($r['ngay'])->format('d/m') : 'Ngày ?' }}
            </button>
        @endforeach
        <button type="button" wire:click="addNewDay" class="shrink-0 flex items-center gap-1 px-3 py-2 rounded-t-xl text-sm font-medium text-teal-600 hover:bg-teal-50">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg> Ngày
        </button>
    </div>

    {{-- ── Nội dung 1 ngày = phiếu dọc theo bản gốc ── --}}
    @if($row)
    <div class="bg-white border border-gray-200 rounded-b-2xl rounded-tr-2xl shadow-sm p-4 md:p-5">
        {{-- Thanh ngày: chọn ngày + xóa + tải --}}
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-100">
            <span class="text-sm text-gray-500">Ngày:</span>
            <input type="date" wire:model.live="rows.{{ $A }}.ngay" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 focus:border-teal-500 outline-none">
            <button type="button" wire:click="openCopy" class="inline-flex items-center gap-1 text-xs border border-gray-300 text-gray-600 rounded-lg px-2.5 py-1.5 hover:bg-gray-50 shrink-0" title="Sao chép dữ liệu ngày này sang ngày khác">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Sao chép sang ngày…
            </button>
            @if(!empty($row['id']))
                <a href="{{ route('forms.export', $row['id']) }}" class="ml-auto inline-flex items-center gap-1 text-xs bg-teal-50 text-teal-700 border border-teal-200 rounded-lg px-2.5 py-1.5 font-medium hover:bg-teal-100">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg> Tải .docx
                </a>
            @else
                <span class="ml-auto text-xs text-gray-300">Lưu để tải</span>
            @endif
            <button type="button" wire:click="removeRow({{ $A }})" class="text-red-400 hover:text-red-600 shrink-0" title="Xóa ngày này">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg>
            </button>
        </div>

        {{-- ── Panel sao chép sang nhiều ngày ── --}}
        @if($showCopy)
        <div class="mb-4 border border-teal-200 bg-teal-50/50 rounded-xl p-3.5">
            <div class="flex items-center gap-2 mb-2.5">
                <span class="text-sm font-semibold text-gray-700">Sao chép dữ liệu ngày <b>{{ \Carbon\Carbon::parse($row['ngay'])->format('d/m/Y') }}</b> sang các ngày:</span>
                <input type="month" wire:model.live="copyMonth" class="ml-auto border border-gray-200 rounded-lg text-sm px-2 py-1">
            </div>
            <div class="flex flex-wrap gap-1.5 mb-2.5">
                @foreach($this->copyDays as $d)
                    @php $isSrc = $d === $row['ngay']; $sel = in_array($d, $copyDates, true); @endphp
                    <button type="button" @if(!$isSrc) wire:click="toggleCopyDate('{{ $d }}')" @endif
                        title="{{ \Carbon\Carbon::parse($d)->format('D d/m/Y') }}"
                        @class([
                            'text-xs rounded-lg w-9 py-1.5 border text-center transition',
                            'bg-gray-100 text-gray-300 border-gray-100 cursor-default' => $isSrc,
                            'bg-teal-600 text-white border-teal-600 font-semibold' => $sel && !$isSrc,
                            'bg-white text-gray-600 border-gray-200 hover:border-teal-400' => !$sel && !$isSrc,
                        ])>{{ \Carbon\Carbon::parse($d)->format('d') }}</button>
                @endforeach
            </div>
            <div class="flex items-center gap-3">
                <button type="button" wire:click="selectAllCopyDates" class="text-xs text-teal-700 hover:underline">Chọn tất cả</button>
                <button type="button" wire:click="clearCopyDates" class="text-xs text-gray-500 hover:underline">Bỏ chọn</button>
                <span class="text-xs text-gray-400">Đã chọn {{ count($copyDates) }} ngày</span>
                <button type="button" wire:click="copyToSelected" @disabled(!count($copyDates))
                    class="ml-auto bg-teal-600 text-white rounded-lg px-3.5 py-1.5 text-sm font-semibold hover:bg-teal-700 disabled:opacity-40">
                    Sao chép ({{ count($copyDates) }})
                </button>
                <button type="button" wire:click="$set('showCopy', false)" class="text-sm text-gray-500 px-2 py-1.5 hover:text-gray-700">Huỷ</button>
            </div>
            <p class="text-[11px] text-amber-600 mt-2">⚠ Ngày đã có dữ liệu sẽ bị GHI ĐÈ. Sau khi sao chép, bấm “Lưu tất cả” để lưu.</p>
        </div>
        @endif

        {{-- Các field theo đúng thứ tự bản gốc — lưới 2 cột (ngắn 1/2, dài full) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4 items-start">
            @foreach($fields as $field)
                @php
                    $key = $field['key']; $val = data_get($row, 'data.'.$key);
                    // Full chiều rộng: đoạn văn, bảng, và chọn 1 nhiều lựa chọn (>4)
                    $full = in_array($field['type'], ['textarea','repeatable_table'])
                            || (in_array($field['type'],['select','radio']) && count($field['options'] ?? []) > 4);
                @endphp

                @if($field['type'] !== 'repeatable_table')
                    <div class="{{ $full ? 'md:col-span-2' : '' }}">
                        <label class="block text-[13px] font-semibold text-gray-700 mb-2">
                            {{ $field['label'] }}@if($field['required'] ?? false)<span class="text-red-500 ml-0.5">*</span>@endif
                        </label>
                        @switch($field['type'])
                            @case('select')
                            @case('radio')
                                @php $opts = $field['options'] ?? []; @endphp
                                @if(count($opts) && count($opts) <= 4)
                                    <div class="grid gap-2" style="grid-template-columns:repeat({{ min(count($opts),3) }},minmax(0,1fr))">
                                        @foreach($opts as $opt)
                                            @php $active_o = (string)$val === (string)$opt; $lc = mb_strtolower($opt);
                                                 $tone = str_contains($lc,'không') ? 'crit' : ((str_contains($lc,'đạt')||str_contains($lc,'ok')) ? 'good':'accent'); @endphp
                                            <button type="button" wire:click="$set('rows.{{ $A }}.data.{{ $key }}', @js($opt))"
                                                @class(['px-3 py-2.5 rounded-xl border-2 text-sm font-semibold flex items-center justify-center gap-1.5 transition',
                                                    'border-gray-200 text-gray-600 bg-white' => !$active_o,
                                                    'bg-green-50 border-green-500 text-green-700' => $active_o && $tone==='good',
                                                    'bg-red-50 border-red-500 text-red-600' => $active_o && $tone==='crit',
                                                    'bg-teal-50 border-teal-500 text-teal-700' => $active_o && $tone==='accent'])>
                                                @if($active_o)<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 12l5 5L20 6"/></svg>@endif
                                                {{ $opt }}
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <select wire:model="rows.{{ $A }}.data.{{ $key }}" class="w-full border border-gray-300 rounded-xl text-[15px] px-3.5 py-2.5 bg-white focus:border-teal-500 outline-none">
                                        <option value="">— Chọn —</option>
                                        @foreach($opts as $o)<option value="{{ $o }}">{{ $o }}</option>@endforeach
                                    </select>
                                @endif
                                @break
                            @case('checkbox')
                                <label class="flex items-center gap-3 border border-gray-200 rounded-xl px-3.5 py-3 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" wire:model="rows.{{ $A }}.data.{{ $key }}" class="w-5 h-5 rounded-md text-teal-600 border-gray-300">
                                    <span class="text-[15px] text-gray-700">Có</span>
                                </label>
                                @break
                            @default
                                <x-dyn-input :model="'rows.'.$A.'.data.'.$key" :type="$field['type']" :placeholder="$field['label']" />
                        @endswitch
                    </div>
                @else
                    {{-- Bảng nhiều dòng (full width) --}}
                    @php $cols = $field['columns'] ?? []; $trows = data_get($row, 'tables.'.$key, []);
                          $hasStt = collect($cols)->contains(fn($c)=>\App\Livewire\RegisterFill::isSttCol($c)); @endphp
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-[13px] font-semibold text-gray-700">{{ $field['label'] }}
                                <span class="text-gray-400 font-normal">({{ count($trows) }} dòng)</span></label>
                        </div>
                        @if(count($trows))
                            {{-- PC: bảng --}}
                            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50"><tr>
                                        @unless($hasStt)<th class="px-2 py-2 text-xs text-gray-400 w-8">#</th>@endunless
                                        @foreach($cols as $col)<th class="px-2 py-2 text-left text-xs font-medium text-gray-500 {{ \App\Livewire\RegisterFill::isSttCol($col)?'w-10 text-center':'min-w-[110px]' }}">{{ $col['label'] }}</th>@endforeach
                                        <th class="w-8"></th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($trows as $ri => $tr)
                                            <tr wire:key="tr-{{ $A }}-{{ $key }}-{{ $ri }}">
                                                @unless($hasStt)<td class="px-2 py-1.5 text-gray-400 text-xs">{{ $ri+1 }}</td>@endunless
                                                @foreach($cols as $col)
                                                    @if(\App\Livewire\RegisterFill::isSttCol($col))
                                                        <td class="px-2 py-1.5 text-center text-gray-500 font-medium">{{ $ri+1 }}</td>
                                                    @else
                                                        <td class="px-2 py-1.5"><x-dyn-input :model="'rows.'.$A.'.tables.'.$key.'.'.$ri.'.'.$col['key']" :type="$col['type']??'text'" :options="$col['options']??[]" compact /></td>
                                                    @endif
                                                @endforeach
                                                <td class="px-2 py-1.5 text-center"><button type="button" wire:click="removeTableRow('{{ $key }}',{{ $ri }})" class="text-red-400 hover:text-red-600">✕</button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- Mobile: thẻ từng dòng --}}
                            <div class="md:hidden space-y-3">
                                @foreach($trows as $ri => $tr)
                                    <div class="border border-gray-200 rounded-xl p-3" wire:key="trc-{{ $A }}-{{ $key }}-{{ $ri }}">
                                        <div class="flex items-center justify-between mb-2"><span class="text-xs font-semibold text-gray-400">Dòng {{ $ri+1 }}</span>
                                            <button type="button" wire:click="removeTableRow('{{ $key }}',{{ $ri }})" class="text-red-400 hover:text-red-600 text-sm">✕ Xóa</button></div>
                                        <div class="space-y-2">
                                            @foreach($cols as $col)
                                                @continue(\App\Livewire\RegisterFill::isSttCol($col))
                                                <div><label class="block text-xs text-gray-500 mb-1">{{ $col['label'] }}</label>
                                                    <x-dyn-input :model="'rows.'.$A.'.tables.'.$key.'.'.$ri.'.'.$col['key']" :type="$col['type']??'text'" :options="$col['options']??[]" /></div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <button type="button" wire:click="addTableRow('{{ $key }}')" class="w-full mt-2 border-2 border-dashed border-gray-300 text-teal-600 rounded-xl py-2 text-sm font-medium hover:border-teal-400 hover:bg-teal-50">+ Thêm dòng</button>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Thanh lưu ── --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-200 px-4 z-20" style="padding-bottom:calc(env(safe-area-inset-bottom) + .75rem);padding-top:.75rem">
        <div class="max-w-3xl mx-auto flex items-center gap-3">
            <span class="text-sm text-gray-500">{{ count($rows) }} ngày</span>
            <button type="button" wire:click="saveAll" wire:loading.attr="disabled" wire:target="saveAll"
                    class="ml-auto bg-teal-600 text-white rounded-xl px-6 py-3 text-sm font-bold hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                <span wire:loading.remove wire:target="saveAll" class="flex items-center gap-2">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 12l5 5L20 6"/></svg> Lưu tất cả
                </span>
                <span wire:loading wire:target="saveAll">Đang lưu…</span>
            </button>
        </div>
    </div>
</div>

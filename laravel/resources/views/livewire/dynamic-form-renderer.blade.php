<div class="max-w-2xl mx-auto pb-28">
    {{-- Header --}}
    <div class="mb-4">
        <h2 class="text-lg font-bold text-gray-800 leading-tight">{{ $this->version->formTemplate->ten_bm }}</h2>
        <p class="text-xs text-gray-400 font-mono mt-0.5">
            {{ $this->version->formTemplate->ma_bm }} · v{{ $this->version->version }} ·
            {{ \Carbon\Carbon::parse($ngayNhap)->format('d/m/Y') }}
        </p>
    </div>

    @if($isSaved)
        <div class="bg-teal-50 border border-teal-200 text-teal-700 px-4 py-2.5 rounded-xl mb-4 text-sm flex items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12l5 5L20 6"/></svg>
            Đã lưu nháp — bạn có thể tiếp tục sau.
        </div>
    @endif

    <form wire:submit="submit" novalidate>
        <div class="space-y-4">
            @foreach($this->fields as $field)
                @php $key = $field['key']; $val = data_get($data, $key); @endphp
                <div class="bg-white rounded-2xl border border-gray-200 p-4 shadow-sm">

                    {{-- ══════ FIELD THƯỜNG ══════ --}}
                    @if($field['type'] !== 'repeatable_table')
                        <label class="block text-[13px] font-semibold text-gray-700 mb-2">
                            {{ $field['label'] }}
                            @if($field['required'] ?? false)<span class="text-red-500 ml-0.5">*</span>@endif
                        </label>

                        @switch($field['type'])
                            {{-- chọn 1 giá trị: nút tick lớn (Đạt/Không đạt tô màu) --}}
                            @case('select')
                            @case('radio')
                                @php $opts = $field['options'] ?? []; @endphp
                                @if(count($opts) && count($opts) <= 4)
                                    <div class="grid gap-2" style="grid-template-columns:repeat({{ min(count($opts),3) }},minmax(0,1fr))">
                                        @foreach($opts as $opt)
                                            @php
                                                $lc = mb_strtolower($opt);
                                                $active = (string) $val === (string) $opt;
                                                $tone = str_contains($lc,'không') ? 'crit' : (str_contains($lc,'đạt') || str_contains($lc,'ok') ? 'good' : 'accent');
                                            @endphp
                                            <button type="button" wire:click="$set('data.{{ $key }}', @js($opt))"
                                                @class([
                                                    'px-3 py-3 rounded-xl border-2 text-[15px] font-semibold flex items-center justify-center gap-1.5 transition',
                                                    'border-gray-200 text-gray-600 bg-white' => !$active,
                                                    'bg-green-50 border-green-500 text-green-700' => $active && $tone==='good',
                                                    'bg-red-50 border-red-500 text-red-600' => $active && $tone==='crit',
                                                    'bg-teal-50 border-teal-500 text-teal-700' => $active && $tone==='accent',
                                                ])>
                                                @if($active)<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M4 12l5 5L20 6"/></svg>@endif
                                                {{ $opt }}
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <x-dyn-input :model="'data.'.$key" type="select" :options="$opts" />
                                @endif
                                @break

                            {{-- checkbox: hàng bấm lớn --}}
                            @case('checkbox')
                                <label class="flex items-center gap-3 border border-gray-200 rounded-xl px-3.5 py-3 cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" wire:model="data.{{ $key }}" class="w-5 h-5 rounded-md text-teal-600 border-gray-300 focus:ring-teal-200">
                                    <span class="text-[15px] text-gray-700">{{ $field['label'] }}</span>
                                </label>
                                @break

                            @default
                                <x-dyn-input :model="'data.'.$key" :type="$field['type']" :placeholder="$field['label']" />
                        @endswitch

                        @error('data.' . $key)<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror

                    {{-- ══════ BẢNG LẶP: PC = bảng, mobile = thẻ ══════ --}}
                    @else
                        @php $cols = $field['columns'] ?? []; $rows = $tableData[$key] ?? []; @endphp
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-[13px] font-semibold text-gray-700">
                                {{ $field['label'] }}
                                @if($field['required'] ?? false)<span class="text-red-500 ml-0.5">*</span>@endif
                                <span class="text-gray-400 font-normal ml-1">({{ count($rows) }} dòng)</span>
                            </label>
                        </div>

                        @if(count($rows))
                            {{-- Desktop: bảng ngang --}}
                            <div class="hidden md:block overflow-x-auto rounded-xl border border-gray-100">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-2 py-2 text-left text-xs font-medium text-gray-400 w-8">#</th>
                                            @foreach($cols as $col)<th class="px-2 py-2 text-left text-xs font-medium text-gray-500">{{ $col['label'] }}</th>@endforeach
                                            <th class="w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($rows as $ri => $row)
                                            <tr>
                                                <td class="px-2 py-1.5 text-gray-400 text-xs">{{ $ri + 1 }}</td>
                                                @foreach($cols as $col)
                                                    <td class="px-2 py-1.5">
                                                        <x-dyn-input :model="'tableData.'.$key.'.'.$ri.'.'.$col['key']" :type="$col['type'] ?? 'text'" :options="$col['options'] ?? []" compact />
                                                    </td>
                                                @endforeach
                                                <td class="px-2 py-1.5 text-center">
                                                    <button type="button" wire:click="removeTableRow('{{ $key }}', {{ $ri }})" class="text-red-400 hover:text-red-600">✕</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile: mỗi dòng là 1 thẻ --}}
                            <div class="md:hidden space-y-3">
                                @foreach($rows as $ri => $row)
                                    <div class="rounded-xl border border-gray-200 p-3 bg-gray-50/60">
                                        <div class="flex items-center justify-between mb-2.5">
                                            <span class="text-xs font-semibold text-gray-500">Dòng {{ $ri + 1 }}</span>
                                            <button type="button" wire:click="removeTableRow('{{ $key }}', {{ $ri }})"
                                                    class="text-xs text-red-500 flex items-center gap-1">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg> Xóa
                                            </button>
                                        </div>
                                        <div class="space-y-2.5">
                                            @foreach($cols as $col)
                                                <div>
                                                    <label class="block text-[11px] font-medium text-gray-400 mb-1">{{ $col['label'] }}</label>
                                                    <x-dyn-input :model="'tableData.'.$key.'.'.$ri.'.'.$col['key']" :type="$col['type'] ?? 'text'" :options="$col['options'] ?? []" />
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 italic py-2">Chưa có dòng nào.</p>
                        @endif

                        <button type="button" wire:click="addTableRow('{{ $key }}')"
                                class="w-full mt-3 border-2 border-dashed border-gray-300 text-teal-600 rounded-xl py-3 text-sm font-semibold hover:border-teal-400 hover:bg-teal-50/50">
                            + Thêm dòng
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    </form>

    {{-- ══════ Thanh nộp cố định đáy ══════ --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-200 px-4 py-3 z-20">
        <div class="max-w-2xl mx-auto flex items-center gap-2.5">
            <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft"
                    class="flex-1 border-2 border-gray-200 rounded-xl py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 disabled:opacity-50">
                <span wire:loading.remove wire:target="saveDraft">Lưu nháp</span>
                <span wire:loading wire:target="saveDraft">Đang lưu…</span>
            </button>
            <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                    class="flex-[1.6] bg-teal-600 text-white rounded-xl py-3 text-sm font-bold hover:bg-teal-700 disabled:opacity-50 flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 12l5 5L20 6"/></svg>
                    Hoàn thành &amp; nộp
                </span>
                <span wire:loading wire:target="submit">Đang xử lý…</span>
            </button>
            @if($submissionId)
                <a href="{{ route('forms.export', $submissionId) }}"
                   class="shrink-0 w-12 grid place-items-center border-2 border-gray-200 rounded-xl py-3 text-gray-500 hover:text-teal-600 hover:border-teal-300" title="Xuất .docx">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16"/></svg>
                </a>
            @endif
        </div>
    </div>
</div>

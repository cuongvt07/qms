@props(['table', 'a', 'fieldMap' => [], 'hiddenKeys' => []])
{{-- Component chung: dựng lại 1 BẢNG từ cấu trúc .docx (giữ gộp cột/hàng, tiêu đề cột+hàng).
     $table = ['rows'=>[[cell,...]], 'headerRows'=>int, ...] ; cell = ['segments','colspan','rowspan','hasKey','skip'] --}}
<div class="overflow-x-auto rounded-xl border border-gray-200">
    <table class="min-w-full text-sm border-collapse">
        @foreach($table['rows'] as $ri => $cells)
            @php $isHead = $ri < $table['headerRows']; @endphp
            <tr>
                @foreach($cells as $c)
                    @continue(!empty($c['skip']))
                    @php
                        $cs = $c['colspan'] > 1 ? $c['colspan'] : null;
                        $rs = $c['rowspan'] > 1 ? $c['rowspan'] : null;
                    @endphp
                    @if($isHead)
                        <th @if($cs)colspan="{{ $cs }}"@endif @if($rs)rowspan="{{ $rs }}"@endif
                            class="border border-gray-200 bg-gray-100 text-gray-700 font-semibold px-2 py-1.5 text-center align-middle">
                            @foreach($c['segments'] as $s){{ $s['t'] ?? '' }}@endforeach
                        </th>
                    @elseif(!$c['hasKey'])
                        {{-- ô nhãn hàng / STT / tiêu đề trong thân bảng --}}
                        <th @if($cs)colspan="{{ $cs }}"@endif @if($rs)rowspan="{{ $rs }}"@endif
                            class="border border-gray-200 bg-gray-50 text-gray-700 font-medium px-2 py-1.5 text-left align-middle whitespace-nowrap">
                            @foreach($c['segments'] as $s){{ $s['t'] ?? '' }}@endforeach
                        </th>
                    @else
                        <td @if($cs)colspan="{{ $cs }}"@endif @if($rs)rowspan="{{ $rs }}"@endif
                            class="border border-gray-200 px-1.5 py-1 align-middle">
                            <div class="flex flex-wrap items-center gap-1">
                            @foreach($c['segments'] as $s)
                                @if(isset($s['t']))
                                    <span class="text-gray-600 text-xs">{{ $s['t'] }}</span>
                                @else
                                    @php $ck = $s['k']; @endphp
                                    @if(isset($hiddenKeys[$ck]))
                                        {{-- ô đã ẩn ở bản gốc -> để trống --}}
                                    @elseif(\Illuminate\Support\Str::startsWith($ck, 'chk_'))
                                        <input type="checkbox" wire:model="rows.{{ $a }}.data.{{ $ck }}"
                                               class="w-5 h-5 rounded text-teal-600 border-gray-300">
                                    @else
                                        @php $cf = $fieldMap[$ck] ?? ['type'=>'text','label'=>$ck]; $cdk = \App\Livewire\RegisterFill::dateKind($cf); @endphp
                                        @if(in_array($cdk, ['day','month','year']))
                                            @php $mx = $cdk==='year'?4:2; $ph = ['day'=>'Ngày','month'=>'Tháng','year'=>'Năm'][$cdk]; @endphp
                                            <input type="text" inputmode="numeric" maxlength="{{ $mx }}" data-datekind="{{ $cdk }}" placeholder="{{ $ph }}"
                                                   wire:model="rows.{{ $a }}.data.{{ $ck }}" style="width:64px"
                                                   class="border border-gray-300 rounded-md text-sm px-2 py-1.5 focus:border-teal-500 outline-none">
                                        @elseif($cdk === 'vndate')
                                            <input type="text" inputmode="numeric" maxlength="10" data-datekind="vndate" placeholder="dd/mm/yyyy"
                                                   wire:model="rows.{{ $a }}.data.{{ $ck }}"
                                                   class="w-full min-w-[100px] border border-gray-300 rounded-md text-sm px-2 py-1.5 focus:border-teal-500 outline-none">
                                        @else
                                            <x-dyn-input :model="'rows.'.$a.'.data.'.$ck" :type="$cf['type'] ?? 'text'" :options="$cf['options'] ?? []" compact />
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                            </div>
                        </td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </table>
</div>

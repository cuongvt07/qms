<div class="max-w-3xl mx-auto">
    @php
        $typeLabels = [
            'text' => 'Chữ ngắn', 'textarea' => 'Đoạn văn', 'number' => 'Số', 'date' => 'Ngày',
            'select' => 'Chọn 1', 'radio' => 'Chọn 1 (nút)', 'checkbox' => 'Ô tích', 'repeatable_table' => 'Bảng nhiều dòng',
        ];
    @endphp

    <div class="flex items-start justify-between gap-3 mb-4">
        <div class="min-w-0">
            <h2 class="text-lg md:text-xl font-bold text-gray-800 truncate">{{ $template?->ten_bm }}</h2>
            <p class="text-sm text-gray-400 font-mono">{{ $template?->ma_bm }}</p>
        </div>
        <a href="{{ route('forms.export-template', $templateId) }}"
           class="shrink-0 text-sm border border-gray-300 rounded-lg px-3 py-2 hover:bg-gray-50">⬇ Tải file mẫu</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @error('fields')<div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl mb-4 text-sm">{{ $message }}</div>@enderror

    {{-- Placeholder có trong file nhưng chưa có ô --}}
    @if(!empty($missingVars))
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 mb-4">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm text-amber-800">File có {{ count($missingVars) }} placeholder chưa có ô nhập:
                    <span class="font-mono text-xs">{{ implode(', ', array_map(fn($v)=>'${'.$v.'}', array_slice($missingVars,0,6))) }}{{ count($missingVars)>6?'…':'' }}</span>
                </p>
                <button wire:click="addMissing" class="shrink-0 text-xs bg-amber-600 text-white rounded-lg px-3 py-1.5 font-medium hover:bg-amber-700">+ Thêm hết</button>
            </div>
        </div>
    @endif

    <div class="flex items-center justify-between gap-2 mb-3">
        <p class="text-sm text-gray-500">Mỗi ô = 1 placeholder trong file. Đặt Nhãn/Kiểu; ô nào không phải nhập thì bấm 👁 để <b>ẩn</b>.</p>
        <button type="button" wire:click="hideJunk" class="shrink-0 text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 text-gray-600 hover:bg-gray-50 whitespace-nowrap">🚫 Ẩn ô rác</button>
    </div>

    <div class="space-y-2.5 mb-5">
        @forelse($fields as $i => $field)
            @php $isHidden = !empty($field['hidden']); @endphp
            <div class="bg-white border rounded-xl p-3 shadow-sm {{ $isHidden ? 'opacity-50 border-gray-200' : 'border-gray-200' }}">
                <div class="flex items-center gap-2">
                    <span class="shrink-0 w-6 h-6 grid place-items-center rounded-md bg-gray-100 text-gray-400 text-xs font-semibold">{{ $i+1 }}</span>
                    <input type="text" wire:model="fields.{{ $i }}.label" placeholder="Nhãn hiển thị (vd: Họ và tên)"
                           class="flex-1 min-w-0 border-gray-300 rounded-lg text-sm px-2.5 py-2 focus:border-teal-500 focus:ring-1 focus:ring-teal-200">
                    <select wire:model.live="fields.{{ $i }}.type" class="shrink-0 w-28 sm:w-36 border-gray-300 rounded-lg text-sm px-2 py-2 focus:border-teal-500">
                        @foreach($typeLabels as $t => $lbl)<option value="{{ $t }}">{{ $lbl }}</option>@endforeach
                    </select>
                    <div class="shrink-0 flex items-center">
                        <button type="button" wire:click="moveUp({{ $i }})" class="text-gray-300 hover:text-gray-600 px-0.5">↑</button>
                        <button type="button" wire:click="moveDown({{ $i }})" class="text-gray-300 hover:text-gray-600 px-0.5">↓</button>
                        <button type="button" wire:click="toggleHidden({{ $i }})" title="{{ $isHidden ? 'Hiện lại (cho nhập)' : 'Ẩn (không cho nhập)' }}"
                                class="px-1 {{ $isHidden ? 'text-teal-500 hover:text-teal-700' : 'text-gray-300 hover:text-gray-600' }}">
                            @if($isHidden)
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.4 5.1A9.5 9.5 0 0112 5c5 0 9 4.5 9 7 0 .9-.7 2.2-1.9 3.4M6.3 6.3C3.9 7.7 3 9.9 3 12c0 0 4 7 9 7 1.2 0 2.3-.3 3.3-.7"/></svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="2.5"/></svg>
                            @endif
                        </button>
                        <button type="button" wire:click="removeField({{ $i }})" class="text-red-300 hover:text-red-600 px-1">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                @if($isHidden)<p class="text-[11px] text-teal-600 mt-1 ml-8">🚫 Ẩn — không hiện khi nhập, xuất để trắng.</p>@endif

                <div class="flex items-center gap-3 mt-2 ml-8">
                    {{-- key placeholder + copy --}}
                    <div x-data="{ copied: false }" class="flex items-center gap-1.5">
                        <code class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5">${{ '{' }}{{ $field['key'] }}{{ '}' }}</code>
                        <button type="button" title="Copy placeholder"
                                @click="navigator.clipboard.writeText('${{ '{' }}{{ $field['key'] }}{{ '}' }}'); copied=true; setTimeout(()=>copied=false,1200)"
                                class="text-xs text-teal-600 hover:text-teal-800">
                            <span x-show="!copied">copy</span><span x-show="copied" class="text-green-600">✓ đã copy</span>
                        </button>
                    </div>
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-gray-500">
                        <input type="checkbox" wire:model="fields.{{ $i }}.required" class="rounded text-teal-600 border-gray-300">
                        Bắt buộc
                    </label>
                </div>

                @if(in_array($field['type'], ['select','radio']))
                    <div class="mt-3 pt-3 border-t border-gray-100 ml-8">
                        <p class="text-xs font-medium text-gray-500 mb-2">Các lựa chọn</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($field['options'] ?? [] as $o => $opt)
                                <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-lg pl-2 pr-1 py-1">
                                    <input type="text" wire:model="fields.{{ $i }}.options.{{ $o }}" placeholder="lựa chọn" class="w-24 border-0 bg-transparent text-sm p-0 focus:ring-0">
                                    <button type="button" wire:click="removeOption({{ $i }},{{ $o }})" class="text-red-300 hover:text-red-600 text-xs">✕</button>
                                </div>
                            @endforeach
                            <button type="button" wire:click="addOption({{ $i }})" class="text-xs text-teal-600 border border-dashed border-teal-300 rounded-lg px-2.5 py-1.5">+ Lựa chọn</button>
                        </div>
                    </div>
                @endif

                @if($field['type'] === 'repeatable_table')
                    <div class="mt-3 pt-3 border-t border-gray-100 ml-8">
                        <p class="text-xs font-medium text-gray-500 mb-1">Cột của bảng (trong Word đặt <span class="font-mono">${{ '{' }}{{ $field['key'] }}{{ '}' }}</span> trên 1 dòng, mỗi cột 1 placeholder)</p>
                        <div class="space-y-2">
                            @foreach($field['columns'] ?? [] as $c => $col)
                                <div class="flex flex-wrap items-center gap-2 pl-2.5 border-l-2 border-teal-100">
                                    <input type="text" wire:model="fields.{{ $i }}.columns.{{ $c }}.key" placeholder="key cột (${...})" class="w-32 border-gray-300 rounded-lg text-sm px-2 py-1.5 font-mono">
                                    <input type="text" wire:model="fields.{{ $i }}.columns.{{ $c }}.label" placeholder="Tên cột" class="flex-1 min-w-[120px] border-gray-300 rounded-lg text-sm px-2 py-1.5">
                                    <button type="button" wire:click="removeColumn({{ $i }},{{ $c }})" class="text-red-300 hover:text-red-600">✕</button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" wire:click="addColumn({{ $i }})" class="text-xs text-teal-600 hover:text-teal-800 mt-2">+ Thêm cột</button>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">Chưa có ô nào.</div>
        @endforelse
    </div>

    <div class="sticky bottom-0 bg-white/95 backdrop-blur border border-gray-200 rounded-xl p-3 flex items-center gap-2">
        <span class="text-sm text-gray-500 shrink-0">{{ count($fields) }} ô</span>
        <input type="text" wire:model="ghi_chu" placeholder="Ghi chú (tùy chọn)" class="flex-1 min-w-0 border-gray-300 rounded-lg text-sm px-3 py-2 focus:border-teal-500">
        <button wire:click="save" wire:loading.attr="disabled"
                class="shrink-0 px-5 py-2.5 bg-teal-600 text-white rounded-lg text-sm font-bold hover:bg-teal-700 disabled:opacity-50">
            <span wire:loading.remove wire:target="save">Lưu cấu hình</span>
            <span wire:loading wire:target="save">Đang lưu…</span>
        </button>
    </div>
</div>

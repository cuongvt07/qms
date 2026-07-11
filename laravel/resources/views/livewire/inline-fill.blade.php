<div class="max-w-5xl mx-auto pb-28">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('forms.register', $versionId) }}" class="w-9 h-9 rounded-xl border border-gray-200 bg-gray-50 grid place-items-center text-gray-600 hover:bg-gray-100 shrink-0" title="Quay lại dạng phiếu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>
        </a>
        <div class="min-w-0 flex-1">
            <h1 class="text-lg font-bold text-gray-800 truncate">{{ $template?->ten_bm }}</h1>
            <p class="text-xs text-gray-400 font-mono">{{ $template?->ma_bm }} · điền trực tiếp trên bản gốc</p>
        </div>
        <a href="{{ route('forms.register', $versionId) }}" class="shrink-0 inline-flex items-center gap-1.5 text-sm border border-gray-300 text-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50" title="Chuyển về dạng phiếu">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h10"/></svg> Dạng phiếu
        </a>
        <input type="date" wire:model="ngay" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 shrink-0">
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-xl px-3 py-2 mb-3">
        Đây là bản gốc — gõ vào các ô <span style="background:rgba(255,241,158,.6);border-bottom:1px solid #b9bcc4" class="px-1">tô vàng</span>, tích vào ô vuông ngay trên trang. Bấm <b>Lưu</b> rồi <b>Tải .docx</b>.
    </div>

    {{-- docx-preview render ở đây; Livewire KHÔNG đụng vào (wire:ignore) --}}
    <div class="qf-doc rounded-2xl overflow-hidden ring-1 ring-black/10">
        <div wire:ignore id="qf-doc-root" class="qf-scroll">
            <div class="qf-loading">Đang tải bản gốc…</div>
        </div>
    </div>

    {{-- Thanh lưu --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-200 px-4 z-20" style="padding-bottom:calc(env(safe-area-inset-bottom) + .75rem);padding-top:.75rem">
        <div class="max-w-5xl mx-auto flex items-center gap-3">
            @if($submissionId)
                <a href="{{ route('forms.inline-export', $submissionId) }}"
                   class="inline-flex items-center gap-1.5 text-sm bg-teal-50 text-teal-700 border border-teal-200 rounded-xl px-4 py-2.5 font-medium hover:bg-teal-100">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg> Tải .docx
                </a>
            @endif
            <span id="qf-status" class="text-xs shrink-0">@if($savedAt)<span style="color:#16a34a">✓ Đã lưu {{ $savedAt }}</span>@else<span class="text-gray-400">Tự lưu khi nhập</span>@endif</span>
            <button type="button" onclick="window.QFInline && window.QFInline.save()" wire:loading.attr="disabled" wire:target="save"
                    class="ml-auto bg-teal-600 text-white rounded-xl px-6 py-3 text-sm font-bold hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 12l5 5L20 6"/></svg> Lưu
                </span>
                <span wire:loading wire:target="save">Đang lưu…</span>
            </button>
        </div>
    </div>
</div>

@assets
    <script src="{{ asset('vendor/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/docx-preview.min.js') }}"></script>
    <script src="{{ asset('js/inline-fill.js') }}?v=5"></script>
    <style>
        /* Nền xám + tờ giấy do docx-preview dựng (section.docx) */
        .qf-doc{background:#54565a}
        .qf-scroll{overflow-x:auto;padding:22px 14px;min-height:200px}
        @media(max-width:640px){.qf-scroll{padding:10px 6px}}
        .qf-loading,.qf-err{color:#e5e7eb;text-align:center;padding:40px 12px;font-family:system-ui,sans-serif;font-size:14px}
        .qf-err{color:#fca5a5}
        #qf-doc-root .docx-wrapper{background:transparent;padding:0}
        #qf-doc-root section.docx{margin:0 auto 22px;box-shadow:0 2px 5px rgba(0,0,0,.3),0 10px 34px rgba(0,0,0,.22)}
        #qf-doc-root .qf-in{font:inherit;color:#1a4bd6;font-weight:500;border:0;border-bottom:1px solid #b9bcc4;
            background:rgba(255,241,158,.42);min-width:60px;padding:0 3px;border-radius:2px 2px 0 0}
        #qf-doc-root .qf-in:focus{outline:none;background:#fff3b0;border-bottom-color:#e0a500}
        #qf-doc-root .qf-sm{min-width:2.4ch;width:2.4ch;padding:0 2px;text-align:center;box-sizing:content-box}
        #qf-doc-root .qf-date{min-width:120px}
        #qf-doc-root .qf-chk{width:15px;height:15px;accent-color:#1a4bd6;vertical-align:middle;cursor:pointer;margin:0 1px}
        #qf-doc-root .qf-stt{font-weight:600}
        #qf-doc-root td{position:relative}
        #qf-doc-root .qf-del{position:absolute;right:-20px;top:50%;transform:translateY(-50%);opacity:0;
            border:0;background:#fee2e2;color:#dc2626;border-radius:50%;width:18px;height:18px;line-height:16px;
            text-align:center;padding:0;cursor:pointer;font-size:11px;transition:opacity .15s}
        #qf-doc-root tr:hover .qf-del{opacity:1}
        #qf-doc-root .qf-addrow{margin:6px 0 14px}
        #qf-doc-root .qf-addrow button{border:1px dashed #9aa0ad;background:#fafafa;color:#374151;border-radius:7px;
            padding:4px 12px;font-family:system-ui,sans-serif;font-size:12.5px;font-weight:600;cursor:pointer}
        #qf-doc-root .qf-addrow button:hover{background:#f0f0f0;border-color:#6b7280}
    </style>
@endassets

@script
    <script>
        (function () {
            function boot() {
                if (!window.QFInline || !window.docx) return setTimeout(boot, 40);
                window.QFInline.init({
                    rootId: 'qf-doc-root',
                    docxUrl: @json($docxUrl),
                    fields: @json($fields),
                    vals: @json($vals ?: (object)[]),
                    wire: $wire,
                });
            }
            boot();
        })();
    </script>
@endscript

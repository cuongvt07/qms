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
        @if($config)
            <a href="{{ route('forms.inline', $versionId) }}" class="shrink-0 inline-flex items-center gap-1.5 text-sm border border-teal-300 text-teal-700 rounded-lg px-3 py-1.5 hover:bg-teal-50" title="Quay lại màn điền">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12l5 5L20 6"/></svg> Xong, về điền
            </a>
        @else
            <a href="{{ route('forms.register', $versionId) }}" class="shrink-0 inline-flex items-center gap-1.5 text-sm border border-gray-300 text-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50" title="Chuyển về dạng phiếu">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h10"/></svg> Dạng phiếu
            </a>
            <a href="{{ route('forms.inline-config', $versionId) }}" class="shrink-0 inline-flex items-center gap-1.5 text-sm border border-gray-300 text-gray-600 rounded-lg px-3 py-1.5 hover:bg-gray-50" title="Cấu hình ẩn ô không cần điền">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> Cấu hình
            </a>
            <input type="date" wire:model="ngay" class="border border-gray-200 rounded-lg text-sm px-2.5 py-1.5 shrink-0">
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ session('success') }}</div>
    @endif

    @if($config)
        <div class="bg-blue-50 border border-blue-200 text-blue-700 text-xs rounded-xl px-3 py-2 mb-3">
            <b>Cấu hình ẩn ô:</b> di chuột (PC) hoặc chạm (điện thoại) vào ô rồi bấm nút <b class="text-red-600">✕</b> ở góc trên phải để <b>ẩn</b> ô không cần điền; bấm <b class="text-green-600">＋</b> để hiện lại. Xong bấm <b>Lưu cấu hình</b>.<br>
            <b>Thêm ô nhập:</b> bấm <b>Ô cùng dòng</b> rồi bấm trúng đoạn chữ → ô nằm ngay sau chỗ bấm; hoặc bấm <b>Ô dòng dưới</b> rồi bấm vào một dòng (vd "CHỦ NHIỆM KHOA") → ô nằm ở <b>dòng ngay dưới</b> dòng đó (dùng cho ô tên dưới chữ ký). Ô đã thêm có nút <b>🗑</b> để xoá. Cấu hình áp dụng cho mọi người điền biểu mẫu này.
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-xl px-3 py-2 mb-3">
            Đây là bản gốc — gõ vào các ô <span style="background:rgba(255,241,158,.6);border-bottom:1px solid #b9bcc4" class="px-1">tô vàng</span>, tích vào ô vuông ngay trên trang. Bấm <b>Lưu</b> rồi <b>Tải .docx</b>.
        </div>
    @endif

    {{-- docx-preview render ở đây; Livewire KHÔNG đụng vào (wire:ignore) --}}
    <div class="qf-doc rounded-2xl overflow-hidden ring-1 ring-black/10">
        <div wire:ignore id="qf-doc-root" class="qf-scroll">
            <div class="qf-loading">Đang tải bản gốc…</div>
        </div>
    </div>

    {{-- Thanh lưu --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-200 px-4 z-20" style="padding-bottom:calc(env(safe-area-inset-bottom) + .75rem);padding-top:.75rem">
        <div class="max-w-5xl mx-auto flex items-center gap-3">
            @if($config)
                <button type="button" id="qf-add-inline" onclick="window.QFInline && window.QFInline.toggleAdd('inline')"
                        class="qf-addbtn inline-flex items-center gap-1.5 text-sm border border-indigo-300 text-indigo-700 rounded-xl px-3 py-2.5 font-medium hover:bg-indigo-50" title="Thêm ô ngay sau đoạn chữ (cùng dòng)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Ô cùng dòng</span>
                </button>
                <button type="button" id="qf-add-below" onclick="window.QFInline && window.QFInline.toggleAdd('below')"
                        class="qf-addbtn inline-flex items-center gap-1.5 text-sm border border-indigo-300 text-indigo-700 rounded-xl px-3 py-2.5 font-medium hover:bg-indigo-50" title="Thêm ô ở dòng dưới dòng chữ (vd ô tên dưới chữ ký)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                    <span>Ô dòng dưới</span>
                </button>
                <span class="text-sm text-gray-500">Đang ẩn <b id="qf-cfg-count" class="text-red-600">{{ count($inlineHidden) }}</b> ô</span>
                <button type="button" onclick="window.QFInline && window.QFInline.saveConfig()" wire:loading.attr="disabled" wire:target="saveConfig"
                        class="ml-auto bg-teal-600 text-white rounded-xl px-6 py-3 text-sm font-bold hover:bg-teal-700 disabled:opacity-50 flex items-center gap-2">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 12l5 5L20 6"/></svg> Lưu cấu hình
                </button>
            @else
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
            @endif
        </div>
    </div>
</div>

@assets
    <script src="{{ asset('vendor/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/docx-preview.min.js') }}"></script>
    <script src="{{ asset('js/inline-fill.js') }}?v=16"></script>
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
        #qf-doc-root .qf-del{position:absolute;right:1px;top:1px;opacity:0;
            border:0;background:#fee2e2;color:#dc2626;border-radius:50%;width:18px;height:18px;line-height:16px;
            text-align:center;padding:0;cursor:pointer;font-size:11px;transition:opacity .15s}
        #qf-doc-root tr:hover .qf-del{opacity:1}
        #qf-doc-root .qf-addrow{margin:6px 0 14px}
        #qf-doc-root .qf-addrow button{border:1px dashed #9aa0ad;background:#fafafa;color:#374151;border-radius:7px;
            padding:4px 12px;font-family:system-ui,sans-serif;font-size:12.5px;font-weight:600;cursor:pointer}
        #qf-doc-root .qf-addrow button:hover{background:#f0f0f0;border-color:#6b7280}
        /* Chế độ cấu hình ẩn ô */
        #qf-doc-root .qf-cfg{position:relative;display:inline-block}
        #qf-doc-root .qf-cfg .qf-x{position:absolute;top:-9px;right:-9px;width:17px;height:17px;line-height:15px;
            border-radius:50%;background:#dc2626;color:#fff;border:1.5px solid #fff;font-size:11px;font-weight:700;
            text-align:center;padding:0;cursor:pointer;opacity:0;transition:opacity .12s;z-index:6;box-shadow:0 1px 3px rgba(0,0,0,.3)}
        #qf-doc-root .qf-cfg:hover .qf-x{opacity:1}
        @media(hover:none){#qf-doc-root .qf-cfg .qf-x{opacity:1}}
        #qf-doc-root .qf-cfg-hidden{opacity:.45}
        #qf-doc-root .qf-cfg-hidden .qf-in,#qf-doc-root .qf-cfg-hidden .qf-chk{filter:grayscale(1);text-decoration:line-through}
        #qf-doc-root .qf-cfg-hidden .qf-x{background:#16a34a;opacity:1}
        /* Ô do người dùng thêm: viền tím + nút xoá */
        #qf-doc-root .qf-cfg-added .qf-in{background:rgba(199,210,254,.55);border-bottom-color:#6366f1}
        #qf-doc-root .qf-cfg-added .qf-trash{position:absolute;bottom:-9px;right:-9px;width:17px;height:17px;line-height:15px;
            border-radius:50%;background:#fff;border:1.5px solid #c7d2fe;font-size:10px;text-align:center;padding:0;cursor:pointer;
            opacity:0;transition:opacity .12s;z-index:6;box-shadow:0 1px 3px rgba(0,0,0,.25)}
        #qf-doc-root .qf-cfg-added:hover .qf-trash{opacity:1}
        @media(hover:none){#qf-doc-root .qf-cfg-added .qf-trash{opacity:1}}
        /* Chế độ đang thêm ô: con trỏ chữ thập + làm nổi vùng bấm */
        #qf-doc-root.qf-adding{cursor:crosshair}
        #qf-doc-root.qf-adding section.docx{outline:2px dashed #6366f1;outline-offset:-2px}
        .qf-addbtn.on{background:#4f46e5;border-color:#4f46e5;color:#fff}
        #qf-toast{position:fixed;left:50%;bottom:86px;transform:translateX(-50%);z-index:60;
            background:rgba(17,24,39,.94);color:#fff;font-family:system-ui,sans-serif;font-size:13px;font-weight:600;
            padding:9px 16px;border-radius:999px;box-shadow:0 4px 16px rgba(0,0,0,.3);opacity:0;transition:opacity .2s;pointer-events:none;max-width:90vw;text-align:center}
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
                    config: @json($config),
                    hidden: @json($inlineHidden),
                    added: @json($inlineAdded),
                    wire: $wire,
                });
            }
            boot();
        })();
    </script>
@endscript

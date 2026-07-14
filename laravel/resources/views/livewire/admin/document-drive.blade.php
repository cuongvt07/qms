<div class="max-w-6xl mx-auto">
<div x-data="driveApp(@js(csrf_token()), @js(route('admin.drive.chunk')), @js(route('admin.drive.finalize')))"
     @keydown.escape.window="closeMenu(); dlg.show=false; conf.show=false"
     @scroll.window="closeMenu()">
    @php
        // Thư mục = vàng; MỌI loại tệp = cùng màu xanh (teal) cho đồng nhất.
        $fileTeal = '#0d9488';
        $kindMeta = [
            'folder' => ['#f59e0b', 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'],
            'image'  => [$fileTeal, 'M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 16l5-5 4 4 3-3 6 6'],
            'pdf'    => [$fileTeal, 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
            'word'   => [$fileTeal, 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
            'excel'  => [$fileTeal, 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
            'file'   => [$fileTeal, 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
        ];
    @endphp

    {{-- Header + breadcrumb --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <svg width="24" height="24" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9A2.25 2.25 0 0 0 19.5 6.75h-5.379a1.5 1.5 0 0 1-1.06-.44z"/></svg>
        <nav class="flex items-center gap-1 text-lg font-bold text-gray-800 flex-wrap">
            <button type="button" wire:click="exitDrive" class="hover:text-teal-600">Ổ tài liệu</button>
            @if($this->category)
                <span class="text-gray-300">/</span>
                <button type="button" wire:click="goTo" class="hover:text-teal-600">{{ $this->category->ten_muc }}</button>
                @foreach($this->breadcrumb as $c)
                    <span class="text-gray-300">/</span>
                    <button type="button" wire:click="goTo({{ $c['id'] }})" class="hover:text-teal-600 font-semibold">{{ $c['name'] }}</button>
                @endforeach
                @if($specialForms)
                    <span class="text-gray-300">/</span>
                    <button type="button" wire:click="openForms" class="hover:text-teal-600 font-semibold">Biểu mẫu</button>
                    @if($this->formTemplate)
                        <span class="text-gray-300">/</span>
                        <span class="text-gray-600 font-semibold">{{ $this->formTemplate->ma_bm }}</span>
                    @endif
                @endif
            @endif
        </nav>
        @if(! $this->category)
            <button type="button" @click="openDialog('drive', {title:'Ổ tài liệu mới', value:''})"
                    class="ml-auto inline-flex items-center gap-1.5 text-sm bg-teal-600 text-white rounded-lg px-3 py-2 font-medium hover:bg-teal-700 shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                Ổ mới
            </button>
        @endif
    </div>

    @if(session('drive_msg'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ session('drive_msg') }}</div>
    @endif

    @if(! $this->category)
        {{-- ROOT: danh sách ổ --}}
        <p class="text-sm text-gray-500 mb-3">Mỗi <b>Mục tài liệu</b> là một ổ chứa. Bấm để mở, chuột phải để đổi tên/xoá.</p>
        <div class="qf-dgrid">
            @foreach($this->drives as $d)
                @php $ditem = ['id'=>$d->id, 'name'=>$d->ten_muc, 'type'=>'drive', 'isFolder'=>true]; @endphp
                <button type="button" wire:click="openCategory({{ $d->id }})" wire:key="drive-{{ $d->id }}"
                        @contextmenu="openMenu($event, 'drive', @js($ditem))"
                        @touchstart.passive="lpStart($event, 'drive', @js($ditem))" @touchend="lpCancel()" @touchmove="lpCancel()"
                        @dragover.prevent="$el.classList.add('qf-drop-hi')"
                        @dragleave.prevent="$el.classList.remove('qf-drop-hi')"
                        @drop.prevent.stop="$el.classList.remove('qf-drop-hi'); add($event.dataTransfer.files, {categoryId: {{ $d->id }}, folderId: null})"
                        class="group flex flex-col items-center gap-2 p-4 bg-white border border-gray-200 rounded-2xl hover:border-teal-400 hover:shadow-sm text-center">
                    <svg width="48" height="48" class="w-12 h-12" fill="#f59e0b" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span class="text-sm font-medium text-gray-700 line-clamp-2">{{ $d->ten_muc }}</span>
                    <span class="text-[11px] text-gray-400">{{ $d->file_count }} tệp</span>
                </button>
            @endforeach
            <button type="button" @click="openDialog('drive', {title:'Ổ tài liệu mới', value:''})"
                    class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-500 hover:border-teal-400 hover:text-teal-600">
                <svg width="36" height="36" class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                <span class="text-sm font-medium">Ổ mới</span>
            </button>
        </div>
    @else
        @if($specialForms && ! $formTemplateId)
            {{-- Thư mục ảo BIỂU MẪU: danh sách biểu mẫu của ổ --}}
            <p class="text-sm text-gray-500 mb-3">File mẫu và tệp đính kèm của các biểu mẫu trong ổ này (chỉ xem/tải).</p>
            <div class="rounded-2xl border border-gray-200 bg-white p-3 min-h-[220px]">
                @if($this->forms->isEmpty())
                    <div class="grid place-items-center py-16 text-gray-400 text-sm">Ổ này chưa gắn biểu mẫu nào.</div>
                @else
                    <div class="qf-dgrid">
                        @foreach($this->forms as $t)
                            <button type="button" wire:click="openForm({{ $t->id }})" wire:key="ft-{{ $t->id }}"
                                    class="flex flex-col items-center gap-1.5 p-3 border border-gray-100 rounded-xl hover:border-teal-300 hover:bg-gray-50 text-center">
                                <svg width="44" height="44" class="w-11 h-11" fill="#0d9488" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                <span class="text-[11px] font-mono text-gray-400">{{ $t->ma_bm }}</span>
                                <span class="text-xs font-medium text-gray-700 line-clamp-2">{{ $t->ten_bm }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif($specialForms && $this->formTemplateId)
            {{-- File của 1 biểu mẫu: file mẫu + tệp đính kèm --}}
            @php $ff = $this->formFiles; @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-3 min-h-[220px]">
                @if(empty($ff))
                    <div class="grid place-items-center py-16 text-gray-400 text-sm">Chưa có file mẫu hay tệp đính kèm.</div>
                @else
                    <div class="qf-dgrid">
                        @foreach($ff as $f)
                            @php [$fcol,$fpath] = $kindMeta[$f['kind']] ?? $kindMeta['file']; @endphp
                            <a href="{{ $f['url'] }}" target="_blank" wire:key="ff-{{ $loop->index }}"
                               class="flex flex-col items-center gap-2 p-3 border border-gray-100 rounded-xl hover:border-teal-300 hover:bg-gray-50 text-center">
                                @if($f['image'])
                                    <img src="{{ $f['url'] }}" width="44" height="44" class="w-11 h-11 object-cover rounded" alt="">
                                @else
                                    <svg width="44" height="44" class="w-11 h-11" fill="none" stroke="{{ $fcol }}" stroke-width="1.5" viewBox="0 0 24 24"><path d="{{ $fpath }}"/></svg>
                                @endif
                                <span class="text-xs font-medium text-gray-700 line-clamp-2 break-words">{{ $f['name'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
        {{-- TRONG Ổ (bình thường) --}}
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <button type="button" @click="openDialog('folder', {title:'Thư mục mới', value:'Thư mục mới'})"
                    class="inline-flex items-center gap-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg px-3 py-2 hover:bg-gray-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M12 10v6M9 13h6"/></svg>
                Thư mục mới
            </button>
            <button type="button" @click="$refs.up.click()"
                    class="inline-flex items-center gap-1.5 text-sm bg-teal-600 text-white rounded-lg px-3 py-2 font-medium hover:bg-teal-700">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15V3m0 0l-4 4m4-4l4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Tải lên
            </button>
            <span x-show="busy" x-cloak class="text-xs text-teal-600">Đang tải lên…</span>
            <span class="ml-auto text-xs text-gray-400">Kéo-thả tệp vào vùng dưới · chuột phải để có thêm tuỳ chọn</span>
        </div>

        {{-- (Tiến độ upload hiển thị ở widget nổi góc dưới phải) --}}
        <div @dragover.prevent="over=true" @dragleave.prevent="over=false"
             @drop.prevent="over=false; add($event.dataTransfer.files)"
             @contextmenu="if($event.target===$el || $event.target.dataset.blank!==undefined) openMenu($event, 'blank', null)"
             :class="over ? 'ring-2 ring-teal-400 bg-teal-50/40' : ''"
             class="min-h-[320px] rounded-2xl border border-gray-200 bg-white p-3 transition">
            <input x-ref="up" type="file" @change="add($event.target.files); $event.target.value=''" multiple class="hidden">

            @php $showForms = ! $folderId; @endphp{{-- Thư mục "Biểu mẫu" luôn có sẵn ở gốc mọi ổ --}}
            @if(! $showForms && $this->items->isEmpty())
                <div data-blank class="grid place-items-center py-20 text-center text-gray-400" @contextmenu="openMenu($event, 'blank', null)">
                    <svg width="56" height="56" class="w-14 h-14 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <p class="text-sm">Thư mục trống. Kéo-thả tệp vào đây, bấm <b>Tải lên</b>, hoặc chuột phải.</p>
                </div>
            @else
                <div class="qf-dgrid">
                    @if($showForms)
                        <button type="button" wire:click="openForms"
                                class="flex flex-col items-center gap-1.5 p-3 border border-teal-200 bg-teal-50/50 rounded-xl hover:border-teal-400 text-center">
                            <svg width="44" height="44" class="w-11 h-11" fill="#0d9488" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            <span class="text-xs font-semibold text-teal-700">Biểu mẫu</span>
                            <span class="text-[10px] text-gray-400">{{ $this->forms->count() ? $this->forms->count().' biểu mẫu' : 'File mẫu & đính kèm' }}</span>
                        </button>
                    @endif
                    @foreach($this->items as $it)
                        @php
                            [$col,$d] = $kindMeta[$it->kind()] ?? $kindMeta['file'];
                            $item = ['id'=>$it->id, 'name'=>$it->name, 'isFolder'=>$it->isFolder(), 'type'=>$it->type, 'url'=>route('admin.drive.file', $it->id)];
                        @endphp
                        <div wire:key="doc-{{ $it->id }}"
                             @contextmenu="openMenu($event, 'item', @js($item))"
                             @touchstart.passive="lpStart($event, 'item', @js($item))" @touchend="lpCancel()" @touchmove="lpCancel()"
                             @if($it->isFolder())
                                 @dragover.prevent="$el.classList.add('qf-drop-hi')"
                                 @dragleave.prevent="$el.classList.remove('qf-drop-hi')"
                                 @drop.prevent.stop="$el.classList.remove('qf-drop-hi'); add($event.dataTransfer.files, {folderId: {{ $it->id }}})"
                             @endif
                             class="relative group border border-gray-100 rounded-xl p-3 hover:border-teal-300 hover:bg-gray-50">
                            @if($it->isFolder())
                                <button type="button" wire:click="openFolder({{ $it->id }})" class="w-full flex flex-col items-center gap-2 text-center">
                                    <svg width="44" height="44" class="w-11 h-11" fill="{{ $col }}" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                    <span class="text-xs font-medium text-gray-700 line-clamp-2 break-words">{{ $it->name }}</span>
                                </button>
                            @else
                                <a href="{{ route('admin.drive.file', $it->id) }}" target="_blank" class="w-full flex flex-col items-center gap-2 text-center">
                                    @if($it->isImage())
                                        <img src="{{ route('admin.drive.file', $it->id) }}" width="44" height="44" class="w-11 h-11 object-cover rounded" alt="">
                                    @else
                                        <svg width="44" height="44" class="w-11 h-11" fill="none" stroke="{{ $col }}" stroke-width="1.5" viewBox="0 0 24 24"><path d="{{ $d }}"/></svg>
                                    @endif
                                    <span class="text-xs font-medium text-gray-700 line-clamp-2 break-words">{{ $it->name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $it->humanSize() }}</span>
                                </a>
                            @endif
                            <button type="button" @click.stop.prevent="openMenu($event, 'item', @js($item))"
                                    class="qf-kebab absolute top-1 right-1 w-7 h-7 grid place-items-center rounded-full text-gray-400 hover:bg-gray-200">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif{{-- /nhánh thư mục ảo Biểu mẫu --}}
    @endif

    {{-- Nền mờ cho bottom-sheet (mobile) --}}
    <div x-show="menu.show && menu.mobile" x-cloak @click="closeMenu()" class="fixed inset-0 bg-black/30 z-40"></div>

    {{-- ===== Menu chuột phải / bottom-sheet ===== --}}
    <div x-ref="menu" x-show="menu.show" x-cloak @click.outside="closeMenu()"
         :class="menu.mobile ? 'qf-ctx-sheet' : 'qf-ctx-pop'"
         :style="menu.mobile ? '' : ('position:fixed;left:'+menu.x+'px;top:'+menu.y+'px')"
         class="z-50 bg-white text-sm">
        <div x-show="menu.mobile" class="px-4 pt-3 pb-2 border-b border-gray-100">
            <p class="text-xs text-gray-400 truncate" x-text="menu.type==='blank' ? 'Thư mục hiện tại' : (menu.item && menu.item.name)"></p>
        </div>
        <template x-if="menu.type==='drive'">
            <div><button type="button" @click="mOpen()" class="qf-mi">Mở</button></div>
        </template>
        <template x-if="menu.type==='blank'">
            <div>
                <button type="button" @click="mNewFolder()" class="qf-mi">Thư mục mới</button>
                <button type="button" @click="mUpload()" class="qf-mi">Tải tệp lên</button>
            </div>
        </template>
        <template x-if="menu.type==='item'">
            <div>
                <button type="button" @click="mOpen()" class="qf-mi" x-text="menu.item && menu.item.isFolder ? 'Mở' : 'Xem'"></button>
                <button type="button" x-show="menu.item && !menu.item.isFolder" @click="mDownload()" class="qf-mi">Tải xuống</button>
                <button type="button" @click="mRename()" class="qf-mi">Đổi tên</button>
                <div class="border-t border-gray-100 my-1"></div>
                <button type="button" @click="askDelete(menu.item)" class="qf-mi qf-mi-danger">Xoá</button>
            </div>
        </template>
    </div>

    {{-- ===== Dialog tạo/đổi tên ===== --}}
    <div x-show="dlg.show" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/30 p-4" @click.self="dlg.show=false">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-5">
            <h3 class="text-base font-bold text-gray-800 mb-3" x-text="dlg.title"></h3>
            <input x-ref="dlgInput" type="text" x-model="dlg.value" @keydown.enter="submitDialog()"
                   class="w-full border border-gray-300 rounded-xl text-sm px-3 py-2.5 focus:border-teal-500 focus:ring-2 focus:ring-teal-100 outline-none" placeholder="Nhập tên…">
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" @click="dlg.show=false" class="px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100">Huỷ</button>
                <button type="button" @click="submitDialog()" class="px-4 py-2 text-sm bg-teal-600 text-white rounded-lg font-medium hover:bg-teal-700">Xong</button>
            </div>
        </div>
    </div>

    {{-- ===== Xác nhận xoá ===== --}}
    <div x-show="conf.show" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/30 p-4" @click.self="conf.show=false">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-5">
            <h3 class="text-base font-bold text-gray-800 mb-1.5">Xoá</h3>
            <p class="text-sm text-gray-600 mb-4">Xoá <b x-text="conf.item && conf.item.name"></b><span x-show="conf.item && conf.item.isFolder"> và toàn bộ bên trong</span>? Không thể hoàn tác.</p>
            <div class="flex justify-end gap-2">
                <button type="button" @click="conf.show=false" class="px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100">Huỷ</button>
                <button type="button" @click="doDelete()" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">Xoá</button>
            </div>
        </div>
    </div>
    {{-- ===== Widget tiến trình upload (nổi góc dưới phải) ===== --}}
    <div x-show="items.length" x-cloak
         class="fixed bottom-4 right-4 z-50 w-80 max-w-[92vw] bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden">
        <div class="px-3.5 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700" x-text="uploadTitle()"></span>
            <button type="button" @click="items = items.filter(x => x.err)" class="text-gray-400 hover:text-gray-600 w-6 h-6 grid place-items-center rounded-full hover:bg-gray-200">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="max-h-64 overflow-y-auto divide-y divide-gray-50">
            <template x-for="(it, idx) in items" :key="idx">
                <div class="px-3.5 py-2">
                    <div class="flex items-center gap-2 text-xs mb-1">
                        <span class="truncate flex-1 text-gray-700" :class="it.err && 'text-red-600'" x-text="it.name"></span>
                        <span class="shrink-0 text-gray-400" x-text="it.err ? 'Lỗi' : it.pct + '%'"></span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all" :class="it.err ? 'bg-red-400' : 'bg-teal-500'" :style="'width:' + (it.err ? 100 : it.pct) + '%'"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>{{-- /driveApp --}}
</div>{{-- /root --}}

@assets
    <script src="{{ asset('js/drive-upload.js') }}?v=5"></script>
    <style>
        .qf-dgrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}
        @media(min-width:640px){.qf-dgrid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(min-width:768px){.qf-dgrid{grid-template-columns:repeat(4,minmax(0,1fr))}}
        @media(min-width:1024px){.qf-dgrid{grid-template-columns:repeat(6,minmax(0,1fr))}}
        /* Menu ngữ cảnh: popup (desktop) / bottom-sheet (mobile) */
        .qf-ctx-pop{position:fixed;width:11rem;border:1px solid #e5e7eb;border-radius:.6rem;box-shadow:0 10px 30px rgba(0,0,0,.18);padding:.25rem 0}
        .qf-ctx-sheet{position:fixed;left:0;right:0;bottom:0;width:100%;border-top-left-radius:1.1rem;border-top-right-radius:1.1rem;box-shadow:0 -6px 24px rgba(0,0,0,.18);padding:.35rem 0 calc(env(safe-area-inset-bottom) + .5rem)}
        .qf-mi{display:block;width:100%;text-align:left;padding:.5rem .9rem;color:#374151;background:none;border:0;cursor:pointer}
        .qf-mi:hover{background:#f9fafb}
        .qf-mi-danger{color:#dc2626}
        .qf-mi-danger:hover{background:#fef2f2}
        .qf-ctx-sheet .qf-mi{padding:.95rem 1.25rem;font-size:1rem}
        /* Nút ⋮: mobile luôn hiện, desktop chỉ khi rê chuột */
        .qf-kebab{opacity:1}
        @media(min-width:768px){.qf-kebab{opacity:0}.group:hover .qf-kebab{opacity:1}}
        /* Highlight thư mục/ổ khi kéo file vào */
        .qf-drop-hi{outline:2px solid #14b8a6 !important;outline-offset:-2px;background:#f0fdfa !important}
    </style>
@endassets

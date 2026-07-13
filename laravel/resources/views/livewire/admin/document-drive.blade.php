<div class="max-w-6xl mx-auto" x-data>
    @php
        $kindMeta = [
            'folder' => ['#f59e0b', 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z'],
            'image'  => ['#0ea5e9', 'M3 5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 16l5-5 4 4 3-3 6 6'],
            'pdf'    => ['#dc2626', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
            'word'   => ['#2563eb', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
            'excel'  => ['#16a34a', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
            'file'   => ['#6b7280', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6'],
        ];
    @endphp

    {{-- Header + breadcrumb --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9A2.25 2.25 0 0 0 19.5 6.75h-5.379a1.5 1.5 0 0 1-1.06-.44z"/></svg>
        <nav class="flex items-center gap-1 text-lg font-bold text-gray-800 flex-wrap">
            <button type="button" wire:click="exitDrive" class="hover:text-teal-600">Ổ tài liệu</button>
            @if($this->category)
                <span class="text-gray-300">/</span>
                <button type="button" wire:click="goTo" class="hover:text-teal-600">{{ $this->category->ten_muc }}</button>
                @foreach($this->breadcrumb as $c)
                    <span class="text-gray-300">/</span>
                    <button type="button" wire:click="goTo({{ $c['id'] }})" class="hover:text-teal-600 font-semibold">{{ $c['name'] }}</button>
                @endforeach
            @endif
        </nav>
    </div>

    @if(session('drive_msg'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ session('drive_msg') }}</div>
    @endif
    @error('uploads.*')<div class="bg-red-50 border border-red-200 text-red-600 px-4 py-2.5 rounded-xl mb-3 text-sm">{{ $message }}</div>@enderror

    @if(! $this->category)
        {{-- ROOT: danh sách ổ (mỗi Mục TL = 1 ổ) --}}
        <p class="text-sm text-gray-500 mb-3">Mỗi <b>Mục tài liệu</b> là một ổ chứa. Bấm để mở.</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($this->drives as $d)
                <button type="button" wire:click="openCategory({{ $d->id }})" wire:key="drive-{{ $d->id }}"
                        class="group flex flex-col items-center gap-2 p-4 bg-white border border-gray-200 rounded-2xl hover:border-teal-400 hover:shadow-sm text-center">
                    <svg class="w-12 h-12 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <span class="text-sm font-medium text-gray-700 line-clamp-2">{{ $d->ten_muc }}</span>
                    <span class="text-[11px] text-gray-400">{{ $d->file_count }} tệp</span>
                </button>
            @endforeach
            <button type="button" @click="let n=prompt('Tên ổ tài liệu mới:'); if(n) $wire.createDrive(n)"
                    class="flex flex-col items-center justify-center gap-2 p-4 border-2 border-dashed border-gray-300 rounded-2xl text-gray-500 hover:border-teal-400 hover:text-teal-600">
                <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                <span class="text-sm font-medium">Ổ mới</span>
            </button>
        </div>
    @else
        {{-- TRONG Ổ: toolbar + lưới thư mục/file, kéo-thả upload --}}
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <button type="button" @click="let n=prompt('Tên thư mục mới:'); if(n) $wire.createFolder(n)"
                    class="inline-flex items-center gap-1.5 text-sm border border-gray-300 text-gray-700 rounded-lg px-3 py-2 hover:bg-gray-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M12 10v6M9 13h6"/></svg>
                Thư mục mới
            </button>
            <button type="button" @click="$refs.up.click()"
                    class="inline-flex items-center gap-1.5 text-sm bg-teal-600 text-white rounded-lg px-3 py-2 font-medium hover:bg-teal-700">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15V3m0 0l-4 4m4-4l4 4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                Tải lên
            </button>
            <span wire:loading wire:target="uploads" class="text-xs text-teal-600">Đang tải lên…</span>
            <span class="ml-auto text-xs text-gray-400">Kéo-thả tệp vào vùng dưới để tải lên</span>
        </div>

        <div x-data="{ over:false }"
             @dragover.prevent="over=true" @dragleave.prevent="over=false"
             @drop.prevent="over=false; if($event.dataTransfer.files.length){ $refs.up.files=$event.dataTransfer.files; $refs.up.dispatchEvent(new Event('change',{bubbles:true})); }"
             :class="over ? 'ring-2 ring-teal-400 bg-teal-50/40' : ''"
             class="min-h-[300px] rounded-2xl border border-gray-200 bg-white p-3 transition">
            <input x-ref="up" type="file" wire:model="uploads" multiple class="hidden">

            @if($this->items->isEmpty())
                <div class="grid place-items-center py-20 text-center text-gray-400">
                    <svg class="w-14 h-14 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <p class="text-sm">Thư mục trống. Kéo-thả tệp vào đây hoặc bấm <b>Tải lên</b>.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5">
                    @foreach($this->items as $it)
                        @php [$col,$d] = $kindMeta[$it->kind()] ?? $kindMeta['file']; @endphp
                        <div wire:key="doc-{{ $it->id }}" x-data="{ menu:false }" class="relative group border border-gray-100 rounded-xl p-3 hover:border-teal-300 hover:bg-gray-50">
                            @if($it->isFolder())
                                <button type="button" wire:click="openFolder({{ $it->id }})" class="w-full flex flex-col items-center gap-2 text-center">
                                    <svg class="w-11 h-11" fill="{{ $col }}" viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                    <span class="text-xs font-medium text-gray-700 line-clamp-2 break-words">{{ $it->name }}</span>
                                </button>
                            @else
                                <a href="{{ route('admin.drive.file', $it->id) }}" target="_blank" class="w-full flex flex-col items-center gap-2 text-center">
                                    @if($it->isImage())
                                        <img src="{{ route('admin.drive.file', $it->id) }}" class="w-11 h-11 object-cover rounded" alt="">
                                    @else
                                        <svg class="w-11 h-11" fill="none" stroke="{{ $col }}" stroke-width="1.5" viewBox="0 0 24 24"><path d="{{ $d }}"/></svg>
                                    @endif
                                    <span class="text-xs font-medium text-gray-700 line-clamp-2 break-words">{{ $it->name }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $it->humanSize() }}</span>
                                </a>
                            @endif

                            {{-- Menu ⋮ --}}
                            <div class="absolute top-1 right-1">
                                <button type="button" @click="menu=!menu" class="w-6 h-6 grid place-items-center rounded-full text-gray-400 hover:bg-gray-200 opacity-0 group-hover:opacity-100 md:opacity-0" :class="menu&&'opacity-100'">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
                                </button>
                                <div x-show="menu" x-cloak @click.outside="menu=false" class="absolute right-0 mt-1 w-36 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 text-sm">
                                    @unless($it->isFolder())
                                        <a href="{{ route('admin.drive.file', $it->id) }}?dl=1" class="block px-3 py-1.5 hover:bg-gray-50 text-gray-700">Tải xuống</a>
                                    @endunless
                                    <button type="button" @click="menu=false; let n=prompt('Đổi tên:', @js($it->name)); if(n) $wire.renameNode({{ $it->id }}, n)" class="block w-full text-left px-3 py-1.5 hover:bg-gray-50 text-gray-700">Đổi tên</button>
                                    <button type="button" @click="menu=false" wire:click="deleteNode({{ $it->id }})" wire:confirm="Xoá &quot;{{ $it->name }}&quot;?{{ $it->isFolder() ? ' (Xoá cả bên trong)' : '' }}" class="block w-full text-left px-3 py-1.5 hover:bg-red-50 text-red-600">Xoá</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

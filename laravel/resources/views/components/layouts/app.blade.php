<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'QMS Forms' }}</title>

    {{-- PWA: cài lên màn hình chính, chạy như app --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d7d8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="QMS Forms">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" href="/icon-192.png">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
      [x-cloak]{display:none!important}
      /* Không bao giờ cho trang cuộn ngang trên mobile; bảng rộng tự cuộn trong khung .overflow-x-auto */
      html,body{max-width:100%;overflow-x:clip}
      img,svg,video,canvas{max-width:100%;height:auto}
    </style>
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () { navigator.serviceWorker.register('/sw.js').catch(function(){}); });
      }
    </script>
</head>
<body class="bg-gray-50 min-h-screen pb-16 md:pb-0">

    @php
        $nav = [
            ['route' => 'dashboard',                        'label' => 'Nhập liệu', 'icon' => 'pencil'],
            ['route' => 'admin.operations',                 'label' => 'Điều hành', 'icon' => 'chart'],
            ['route' => 'admin.form-templates.index',       'label' => 'Biểu mẫu',  'icon' => 'doc'],
            ['route' => 'admin.audit-log',                  'label' => 'Nhật ký',   'icon' => 'log'],
            ['route' => 'admin.drive',                      'label' => 'Tài liệu',  'icon' => 'folder'],
        ];
        $icon = function ($name, $cls = 'w-5 h-5') {
            $paths = [
                'pencil' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>',
                'chart'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
                'doc'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                'folder' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>',
                'log'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 12h7.5m-7.5 5.25h4.5M3.75 5.25A2.25 2.25 0 016 3h12a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0118 21H6a2.25 2.25 0 01-2.25-2.25V5.25z"/>',
            ];
            return '<svg class="'.$cls.'" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">'.($paths[$name] ?? '').'</svg>';
        };
    @endphp

    {{-- Thanh trên --}}
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 md:h-16 items-center">
                {{-- Brand --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-teal-700">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-teal-600 text-white text-sm font-bold">Q</span>
                    <span class="text-base md:text-lg">QMS Forms</span>
                </a>

                @auth
                    {{-- Link ngang: chỉ PC --}}
                    <div class="hidden md:flex items-center gap-1">
                        @foreach($nav as $item)
                            @php $active = request()->routeIs($item['route']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition
                                      {{ $active ? 'bg-teal-50 text-teal-700 font-medium' : 'text-gray-600 hover:bg-gray-100 hover:text-teal-600' }}">
                                {!! $icon($item['icon'], 'w-4 h-4') !!}
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>

                    {{-- User --}}
                    <div class="flex items-center gap-2 md:gap-3">
                        <span class="hidden sm:inline text-sm text-gray-600 max-w-[9rem] truncate">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Đăng xuất"
                                    class="flex items-center gap-1 text-sm text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                                <span class="hidden md:inline">Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    @auth
    {{-- Thanh tab dưới kiểu iOS: kính mờ, trong suốt, nằm ngang --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40
                bg-white/70 backdrop-blur-xl border-t border-gray-200/60"
         style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="flex items-stretch justify-around">
            @foreach($nav as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex-1 flex flex-col items-center justify-center gap-1 pt-2 pb-1.5 text-[10px] font-medium transition
                          {{ $active ? 'text-teal-600' : 'text-gray-500 active:text-gray-700' }}">
                    {!! $icon($item['icon'], 'w-6 h-6') !!}
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
    @endauth

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto mt-4 px-4">
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>

@php
    $u    = auth()->user();
    $ini  = mb_strtoupper(mb_substr(trim($u->name ?? 'N'), 0, 1));
    $nav  = function ($route) { return request()->routeIs($route) ? ' on' : ''; };
@endphp
<button class="qs-burger" type="button" onclick="document.body.classList.toggle('qs-open')" aria-label="Mở menu">☰</button>
<div class="qs-mask" onclick="document.body.classList.remove('qs-open')"></div>

<aside class="qs-side">
    <div class="qs-brand">
        <div class="qs-logo">QMS</div>
        <div><b>Bệnh viện TWQĐ 108</b><span>Hệ thống quản lý chất lượng</span></div>
    </div>

    <nav class="qs-nav">
        <div class="qs-group">Theo dõi vận hành</div>
        <a class="qs-item{{ $nav('env.page') }}" href="{{ route('env.page') }}">
            <span class="qs-ic">🌡</span> Nhiệt độ, độ ẩm &amp; vệ sinh
        </a>
        <a class="qs-item{{ $nav('dev.page') }}" href="{{ route('dev.page') }}">
            <span class="qs-ic">🛠</span> Khử nhiễm trang thiết bị
        </a>
        <a class="qs-item{{ $nav('waste.page') }}" href="{{ route('waste.page') }}">
            <span class="qs-ic">🗑</span> Nhật ký xử lý rác thải
        </a>

        <div class="qs-group">Hồ sơ &amp; tài liệu</div>
        <a class="qs-item" href="{{ route('dashboard') }}"><span class="qs-ic">📋</span> Biểu mẫu &amp; nhắc việc</a>
        <a class="qs-item" href="{{ route('admin.drive') }}"><span class="qs-ic">🗂</span> Ổ tài liệu</a>
        <a class="qs-item" href="{{ route('admin.form-templates.index') }}"><span class="qs-ic">📄</span> Quản lý biểu mẫu</a>

        <div class="qs-group">Quản trị</div>
        <a class="qs-item" href="{{ route('admin.operations') }}"><span class="qs-ic">⚙</span> Trung tâm điều hành</a>
        <a class="qs-item" href="{{ route('admin.audit-log') }}"><span class="qs-ic">🕘</span> Nhật ký hoạt động</a>
    </nav>

    <div class="qs-foot">
        <div class="qs-user">
            <div class="qs-ava">{{ $ini }}</div>
            <div><b>{{ $u->name ?? 'Người dùng' }}</b><span>{{ $u->email ?? '' }}</span></div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="qs-out" type="submit">Đăng xuất</button>
        </form>
    </div>
</aside>

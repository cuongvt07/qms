<?php

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ghi nhật ký đăng nhập / đăng xuất
        Event::listen(Login::class, fn () => ActivityLogger::log('login', 'Đăng nhập hệ thống'));
        Event::listen(Logout::class, fn () => ActivityLogger::log('logout', 'Đăng xuất'));
    }
}

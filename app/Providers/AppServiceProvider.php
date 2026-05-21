<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use App\Repositories\Contracts\AttendanceRepository;
use App\Repositories\Eloquent\EloquentAttendanceRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AttendanceRepository::class, EloquentAttendanceRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $view->with('schoolSetting', SchoolSetting::query()->first());
        });
    }
}

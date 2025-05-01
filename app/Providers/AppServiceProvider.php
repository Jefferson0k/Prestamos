<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\TrackUserActivity;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider{
    public function register(): void{

    }
    public function boot(): void{
        Route::middleware('auth')->group(function () {
            Route::middleware(TrackUserActivity::class)->group(function () {
            });
        });
    }
}

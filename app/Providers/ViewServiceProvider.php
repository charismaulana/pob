<?php

namespace App\Providers;

use App\Services\RamesaApiService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share API connection status with all views
        View::composer('layouts.app', function ($view) {
            $apiConnected = Cache::remember('ramesa_api_status', 60, function () {
                try {
                    $api = app(RamesaApiService::class);
                    return $api->checkConnection();
                } catch (\Exception $e) {
                    return false;
                }
            });

            $view->with('apiConnected', $apiConnected);
        });
    }
}

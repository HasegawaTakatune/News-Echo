<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // bind our AI and social abstractions so they can be injected
        $this->app->bind(\App\Services\AIService::class, \App\Services\GeminiAIService::class);
        $this->app->singleton(\App\Services\SocialService::class, function ($app) {
            return new \App\Services\SocialService();
        });
        $this->app->singleton(\App\Services\SettingService::class, function ($app) {
            return new \App\Services\SettingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

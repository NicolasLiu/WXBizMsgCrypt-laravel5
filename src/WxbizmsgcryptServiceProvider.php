<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class WxbizmsgcryptServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/wxbizmsgcrypt.php' => config_path('wxbizmsgcrypt.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['wxbizmsgcrypt'] = $this->app->share(function ($app) {
            return new Toastr(
            	config('wxbizmsgcrypt.token'),
            	config('wxbizmsgcrypt.encodingAesKey'),
            	config('wxbizmsgcrypt.corpId')
            );
        });
    }
}

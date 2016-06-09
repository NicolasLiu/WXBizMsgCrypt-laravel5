<?php

namespace Nicolasliu\Wxbizmsgcrypt;

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
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app['wxbizmsgcrypt'] = $this->app->share(function ($app) {
            return new WXBizMsgCrypt(
            	config('wxbizmsgcrypt.token'),
            	config('wxbizmsgcrypt.encodingAesKey'),
            	config('wxbizmsgcrypt.corpId')
            );
        });
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('Nicolasliu\Wxbizmsgcrypt');
    }
}

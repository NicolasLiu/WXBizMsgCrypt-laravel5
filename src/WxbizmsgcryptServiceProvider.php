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
        $this->app->singleton('wxcrypt', function() {
            return new WXBizMsgCrypt(
            	config('wxbizmsgcrypt.token'),
            	config('wxbizmsgcrypt.encodingAesKey'),
            	config('wxbizmsgcrypt.corpId')
            );
        });
    }
    
}

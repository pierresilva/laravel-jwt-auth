<?php


namespace pierresilva\JWTAuth;

use Illuminate\Support\ServiceProvider;

class JWTAuthServiceProvider extends ServiceProvider
{

    public function boot()
    {

        include __DIR__.'/Routes/api.php';
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'jwt');

    }

    public function register()
    {

        $this->app->make('pierresilva\JWTAuth\Controllers\JWTAuthController');

    }

}

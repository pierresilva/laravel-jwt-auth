<?php


namespace pierresilva\JWTAuth;

use Illuminate\Support\ServiceProvider;

class JWTAuthServiceProvider extends ServiceProvider
{

    public function boot()
    {

        include __DIR__.'/Routes/api.php';

    }

    public function register()
    {

        $this->app->make('pierresilva\JWTAuth\Controllers\JWTAuthController');

    }

}

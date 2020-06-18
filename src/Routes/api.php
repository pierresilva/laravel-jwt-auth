<?php

use Illuminate\Support\Facades\Route;

Route::post('api/jwt-auth/register', 'pierresilva\JWTAuth\Controllers\JWTAuthController@register');
Route::post('api/jwt-auth/login', 'pierresilva\JWTAuth\Controllers\JWTAuthController@login');
Route::post('api/jwt-auth/logout', 'pierresilva\JWTAuth\Controllers\JWTAuthController@logout');
Route::post('api/jwt-auth/refresh', 'pierresilva\JWTAuth\Controllers\JWTAuthController@refresh');
Route::get('api/jwt-auth/profile', 'pierresilva\JWTAuth\Controllers\JWTAuthController@profile');

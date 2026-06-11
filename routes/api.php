<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

route::get('/philippines/cities', [App\Http\Controllers\PhilippinesCityController::class, 'index']);

route::get('/weather/city/{city}', [App\Http\Controllers\WeatherCityController::class, 'show']);

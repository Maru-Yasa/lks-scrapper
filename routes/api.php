<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => 'v1'], function() {
    // Route::get('/scrap', 'App\Http\Controllers\API\ConfigController@scrap');

    // test route
    Route::get('/', function(){
        return response()->json([
            'status' => "success",
            'data' => [],
            'msg' => 'Hi!',
        ]);
    });

    // /config/* route
    Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'config'], function() {
        Route::get('/', 'App\Http\Controllers\API\ConfigController@getAll');
        Route::get('/{id}', 'App\Http\Controllers\API\ConfigController@getById');
        Route::post('/create', 'App\Http\Controllers\API\ConfigController@create');
        Route::post('/update', 'App\Http\Controllers\API\ConfigController@update');
        Route::delete('/delete', 'App\Http\Controllers\API\ConfigController@delete');
        Route::post('/run', 'App\Http\Controllers\API\ConfigController@run');

    });

    // /auth/* route
    Route::group(['prefix' => 'auth'], function(){
        // Not required authorization
        Route::post('/login', 'App\Http\Controllers\API\AuthController@login');
        Route::post('/register', 'App\Http\Controllers\API\AuthController@register');

        // Required authorization
        Route::group(['middleware' => 'auth:sanctum'], function(){
            Route::get('/logout', 'App\Http\Controllers\API\AuthController@logout');
            Route::get('/user', 'App\Http\Controllers\API\AuthController@getUser');
            Route::post('/update','App\Http\Controllers\API\AuthController@update');
        });
    });



});



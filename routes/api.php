<?php
use Illuminate\Support\Facades\Route;

/**
 * Try to use methods from BaseController (App\Http\Controllers\BaseController)
 *
 * GET getAll -> route-name ?+ params (for 'where')
 * GET getOne -> route-name/{id} ?+ params (for 'where')
 * POST createOne -> route-name + data
 * PUT updateOne -> route-name/{id} + data
 * DELETE deleteOne -> route-name/{id}
 */

Route::group(['middleware' => 'language'], function () {

    //FOR USER
    Route::post('login', 'User\\AuthController@login');
    Route::post('register', 'User\\AuthController@register');
    Route::post('refresh-token', 'User\\AuthController@refreshToken');

    Route::post('message', 'Message\\MessageController@createOne');

    Route::group(['middleware' => 'jwt-auth:user'], function () {
        Route::get('user', 'User\\UserController@getOne');
        Route::put('user/{id}', 'User\\UserController@updateOne');

        Route::post('logout', 'User\\AuthController@logout');
        Route::post('change-password', 'User\\AuthController@changePassword');
    });
    //FOR USER

    //FOR ADMIN
    Route::prefix('admin')->group(function () {
        Route::post('login', 'Admin\\User\\AuthController@login');
        Route::post('refresh-token', 'Admin\\User\\AuthController@refreshToken');

        Route::group(['middleware' => 'jwt-auth:admin'], function () {
            Route::get('user', 'Admin\\User\\AdminController@getOne');
            Route::post('logout', 'Admin\\User\\AuthController@logout');
        });
    });
    //FOR ADMIN
});

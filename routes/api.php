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

    Route::group(['middleware' => 'jwt-auth:user'], function () {
        Route::get('user', 'User\\UserController@getOne');
        Route::put('user/{id}', 'User\\UserController@updateOne');

        Route::post('logout', 'User\\AuthController@logout');
        Route::post('change-password', 'User\\AuthController@changePassword');

        Route::resource('posts', 'PostController')->middleware('check-user-action');
        Route::resource('categories', 'CategoryController')->middleware('check-user-action');
    });
    //FOR USER

    //FOR ADMIN
    Route::prefix('admin')->group(function () {
        Route::post('register', 'Admin\\User\\AuthController@register');
        Route::post('login', 'Admin\\User\\AuthController@login');
        Route::post('refresh-token', 'Admin\\User\\AuthController@refreshToken');

        Route::group(['middleware' => 'jwt-auth:admin'], function () {
            Route::get('user', 'Admin\\User\\AdminController@getOne');
            Route::put('user/{id}', 'Admin\\User\\AdminController@updateOneForAdmin');
            Route::post('logout', 'Admin\\User\\AuthController@logout');

            Route::get('posts', 'PostController@index');
            Route::get('post/{id}', 'PostController@show');
            Route::post('post', 'PostController@store');
            Route::put('post/{id}', 'PostController@updateOneForAdmin');
            Route::delete('post/{id}', 'PostController@deleteOneForAdmin');
        });
    });
    //FOR ADMIN
});

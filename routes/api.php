<?php
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

    Route::get('posts', 'PostController@getAll');
    Route::get('post/{id}', 'PostController@getOne');
    Route::post('post', 'PostController@createOne');
    Route::put('post/{id}', 'PostController@updateOne');
    Route::delete('post/{id}', 'PostController@deleteOne');

    Route::group(['middleware' => 'jwt-auth:user'], function () {
        Route::get('user', 'User\\UserController@getOne');
        Route::put('user/{id}', 'User\\UserController@updateOne');

        Route::post('logout', 'User\\AuthController@logout');
        Route::post('change-password', 'User\\AuthController@changePassword');

        Route::get('posts', 'PostController@getAll');
        Route::get('post/{id}', 'PostController@getOne');
        Route::post('post', 'PostController@createOne');
        Route::put('post/{id}', 'PostController@updateOne');
        Route::delete('post/{id}', 'PostController@deleteOne');
    });
    //FOR USER

    //FOR ADMIN
    Route::prefix('admin')->group(function () {
        Route::post('login', 'Admin\\User\\AuthController@login');
        Route::post('refresh-token', 'Admin\\User\\AuthController@refreshToken');

        Route::get('posts', 'PostController@getAll');
        Route::get('post/{id}', 'PostController@getOne');
        Route::post('post', 'PostController@createOne');
        Route::put('post/{id}', 'PostController@updateOneForAdmin');
        Route::delete('post/{id}', 'PostController@deleteOneForAdmin');

        Route::group(['middleware' => 'jwt-auth:admin'], function () {
            Route::get('user', 'Admin\\User\\AdminController@getOne');
            Route::post('logout', 'Admin\\User\\AuthController@logout');

            Route::get('posts', 'PostController@getAll');
            Route::get('post/{id}', 'PostController@getOne');
            Route::post('post', 'PostController@createOne');
            Route::put('post/{id}', 'PostController@updateOneForAdmin');
            Route::delete('post/{id}', 'PostController@deleteOneForAdmin');
        });
    });
    //FOR ADMIN
});

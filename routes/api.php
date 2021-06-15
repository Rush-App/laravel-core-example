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

//    Route::resource('posts', 'PostController');

    Route::group(['middleware' => 'jwt-auth:user'], function () {
        Route::post('logout', 'User\\AuthController@logout');

        Route::resource('posts', 'PostController')->middleware('core.check-user-action');
        Route::resource('categories', 'CategoryController')->middleware('check-user-action');
    });
    //FOR USER
});

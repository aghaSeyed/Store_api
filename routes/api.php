<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});
Route::group(['namespace'=>'api','middleware'=>'auth:api'], function (){


    Route::group(['prefix' => 'user'], function (){
        Route::group(['namespace' => 'User'], function (){
            Route::post('register' , 'LoginController@register');
            Route::post('verify' , 'LoginController@verify');
            Route::post('login' , 'LoginController@login');
            Route::get('logout' , 'LoginController@logout');
            Route::apiResource('profile' , 'UserController');
//            Route::get('getUserData' , 'LoginController@getUserData');
            Route::group(['middleware'=>'auth:api'], function (){
                Route::apiResource('cart' , 'cartController')->except('create');
            });

        });
    });


    Route::group(['prefix' => 'products'], function (){
        Route::group(['namespace' => 'products'], function (){
            Route::get('latest' , 'ProductController@latest');
            Route::get('search' , 'ProductController@search');
            Route::get('categories' , 'ProductController@categories');
            Route::get("category/{slug}", 'ProductController@getCategory');
            Route::get("{product}", 'ProductController@show');

        });
    });
});

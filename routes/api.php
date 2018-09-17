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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::patch('settings/profile', 'Settings\UpdateProfile');
    Route::patch('settings/password', 'Settings\UpdatePassword');
});

Route::group(['middleware' => 'auth:api', 'prefix'=> 'patients'], function () {
    Route::get('/', 'ResourceController@index');
    Route::get('/{id}', 'ResourceController@show');

});
Route::get('/amx/{id}', function ($id) {

    $cl = new \GuzzleHttp\Client();
    $respo = $cl->get(env('DCM_SERVER') . '/instances/' . $id . '/preview');

    $respo = $respo->getBody()->getContents();
//    $respo = "data:image/png;base64," . base64_encode($respo);

    return $respo;
});
Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});

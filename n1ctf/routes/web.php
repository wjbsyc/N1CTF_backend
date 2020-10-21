<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Auth::routes(['verify' => true]);
//Route::get('user/verify/{verification_code}', 'AuthController@verifyUser');
//Route::get('password/reset/', 'Auth\ResetPasswordController@showResetForm')->name('password.request');

Route::get('password/reset', function(){echo 'gg';})->name('password.reset');

Route::get('/home', 'HomeController@index')->name('home');

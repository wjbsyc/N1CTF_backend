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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('register','AuthController@register');
    Route::post('me', 'AuthController@me');
    //Route::post('password/email', 'Auth\ForgotPasswordController@getResetToken');
	Route::post('password/reset', 'Auth\ResetPasswordController@reset');
    Route::get('emailVerify/{verification_code}', 'AuthController@verifyUser');
    Route::post('resetPass', 'AuthController@resetPassword');
    Route::get('resendVerifyMail','AuthController@resendMail');

});

Route::group(['middleware'=>['auth:api','verified']] ,function($router)
{
    Route::post('createTeam','teamController@create_team');
    Route::get('teamInfo','teamController@getUserTeam');
    Route::post('joinTeam','teamController@join_team');
    Route::post('submit/challenge/{challenge}', 'ChallengeController@submitFlag');
    Route::get('challenges','ChallengeController@getQuestionsBelongsToClass');
    Route::get('/challenge/detail/{challenge}', 'ChallengeController@getQuestionDetail');
    Route::delete('/challenge/delete/{challenge}', 'ChallengeController@deleteChallenge');
    Route::get('/challenge/solvers/{challenge}', 'ChallengeController@getSolvers');
    Route::post('/challenge/edit/{challenge}', 'ChallengeController@editchallenge');
    Route::post('/notice/add','NoticeController@newnotice');
    Route::delete('/notice/delete/{id}','NoticeController@delete');
    Route::post('notice/edit/{id}','NoticeController@edit');
    Route::post('hint/add','hintController@newhint');
    Route::post('hint/edit/{id}','hintController@edithint');
    Route::delete('hint/delete/{id}','hintController@deletehint');
    Route::get('/game/over','timecontroller@over');
    Route::get('/game/start','timecontroller@GameStartNow');
    Route::post('/game/time','timecontroller@gamestart');
    Route::get('/updateScoreBoard','teamController@updateAllScore');
});

Route::get('team/{id}','teamController@show_team');
Route::post('/newchallenge', 'ChallengeController@newchallenge');
Route::post(env('ADMIN_ROUTE'),'AuthController@regadmin');
Route::get('/isAdmin','UserController@I_AM_ADMIN')->middleware('auth:api');
Route::get('/scoreboard', 'ChallengeController@ShowScoreBoard');
Route::get('/notice','NoticeController@showNotice');
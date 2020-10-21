<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Transformers\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Validator;

class ResetPasswordController extends Controller
{
/*
|--------------------------------------------------------------------------
| Password Reset Controller
|--------------------------------------------------------------------------
|
| This controller is responsible for handling password reset requests
| and uses a simple trait to include this behavior. You're free to
| explore this trait and override any methods you wish to tweak.
|
*/

use ResetsPasswords;

/**
 * Where to redirect users after resetting their password.
 *
 * @var string
 */
protected $redirectTo = '/home';

/**
 * Create a new controller instance.
 *
 * @return void
 */
public function __construct()
{
    $this->middleware('guest');
}

/**
* Reset the given user's password.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function reset(Request $request)
{
  $data = (json_decode($request->getContent(),true));
 // $this->validate($request, $this->rules(), $this->validationErrorMessages());
  $v=Validator::make($data,[
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
  ]);
  if ($v->fails()) {
        return response()->json([
            'code' => 400,
            'message' => $v->errors()->first(),
            'success' => false
          ]
    );
  }

  // Here we will attempt to reset the user's password. If it is successful we
  // will update the password on an actual user model and persist it to the
  // database. Otherwise we will parse the error and return the response.
  $response = $this->broker()->reset(
    $this->credentials($request), function ($user, $password) {
      $this->resetPassword($user, $password);
    }
  );
  //return '123';
  if ($request->wantsJson()) {
    if ($response == Password::PASSWORD_RESET) {
      return response()->json(['code'=>200,'success' =>true,'message' =>'Your password has been reset!']);//Json::response(null, trans('passwords.reset')));
    } else {
      //dd(trans($response));
      return response()->json(Json::response(400, trans($response), false));
    }
  }

  // If the password was successfully reset, we will redirect the user back to
  // the application's home authenticated view. If there is an error we can
  // redirect them back to where they came from with their error message.
  return $response == Password::PASSWORD_RESET
  ? $this->sendResetResponse($response)
  : $this->sendResetFailedResponse($request, $response);
}
}
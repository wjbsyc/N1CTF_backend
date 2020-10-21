<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\User;
use Mail;
use DB;
use Carbon\Carbon;
use Password;
use App\teams;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     * 要求附带email和password（数据来源users表）
     * 
     * @return void
     */
    public function __construct()
    {
        // 这里额外注意了：官方文档样例中只除外了『login』
        // 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
        // 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
        // 不过刷新一次作废
        $this->middleware('auth:api', ['except' => ['login','register','resetPassword','regadmin']]);
        // 另外关于上面的中间件，官方文档写的是『auth:api』
        // 但是我推荐用 『jwt.auth』，效果是一样的，但是有更加丰富的报错信息返回
    }
    public function register(Request $request){
        $request = (json_decode($request->getContent(),true));
        $valid = Validator::make($request, [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($valid->fails()) {
            return response()->json(array(
                'code' => 400,
                'message' => $valid->errors()->first(),
                'success' => false
            ));
        }
        $data = $request;
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'power' => bcrypt('user'),
        ]);
        // Mail::send('email.verify', ['name' => $name, 'verification_code' => $verification_code],
        //     function($mail) use ($email, $name, $subject){
        //         $mail->from(getenv('FROM_EMAIL_ADDRESS'), "From User/Company Name Goes Here");
        //         $mail->to($email, $name);
        //         $mail->subject($subject);
        // });
        $user->sendEmailVerificationNotification();
        return response()->json(['code' => 200,'success'=>true,'message' =>$user->name.':An email will be sent to your email address,Please Check!' ]);
    }

    public function resendMail()
    {
        $user = auth('api')->user();
        if($user->email_verified_at) return response()->json(['code' => 400,'success'=>false,'message' =>'Already Verified!' ]);
        $user->sendEmailVerificationNotification();
        return response()->json(['code' => 200,'success'=>true,'message' =>$user->name.':An email will be sent to your email address,Please Check!' ]);
    }

    public function regadmin(Request $request){
        $request = (json_decode($request->getContent(),true));
        //$request=$userdata->all();
            $v=Validator::make($request,[
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'confirmcode'=> ['required',Rule::in([env('ADMIN_CODE')]),],
            ]);
            if ($v->fails()) {
            return response()->json(array(
                'code' => 400,
                'message' => $v->errors()->first(),
                'success' => false
            ));
        }
        else{
                $re = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => bcrypt($request['password']),
                'power' => bcrypt('admin'),
                'api_token' =>\Str::random(60),
                'email_verified_at' => Carbon::now()
                ]);
                if($re) return response()->json(['code' => 200,'success'=>true,'message' =>'OK!' ]);
                else return response()->json(['code' => 400,'success'=>true,'message' =>$re]);
            }


    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['code'=>401,'success'=>false,'message' => 'Unauthorized']);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {

        $user = auth('api')->user();
        $team = $user->team;
        $user->team = $team ?? null;
        $userArray = $user->toArray();
        return response()->json($userArray);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     * 刷新token，如果开启黑名单，以前的token便会失效。
     * 值得注意的是用上面的getToken再获取一次Token并不算做刷新，两次获得的Token是并行的，即两个都可用。
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'code' => 200,
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
    public function verifyUser($verification_code)
    {
        $user = auth('api')->user();
        if(!$user)
        {
            return response()->json(['code'=>400,'success'=> false, 'error'=> "Please Login."]);
        }

        $check = DB::table('user_verifications')->where('token',$verification_code)->where('user_id',$user->id)->first();

        if(!is_null($check)){
           

            $user->markEmailAsVerified();

            DB::table('user_verifications')->where('token',$verification_code)->delete();

            return response()->json([
                'code' => 200,
                'success'=> true,
                'message'=> 'You have successfully verified your email address.'
            ]);
        }

        return response()->json(['code'=>400,'success'=> false, 'message'=> "Verification code is invalid."]);

    }
    public function resetPassword(Request $request)
    {
        $request = (json_decode($request->getContent(),true));
        $email = $request['email'] ?? '';
        $user = User::where('email',$email)->first();
        if (!$user) {
            $error_message = "Your email address was not found.";
            return response()->json(['success' => false, 'message' => $error_message,'code'=>400]);
        }

        try {
            Password::broker()->sendResetLink(['email' => $user->email]);
        } catch (\Exception $e) {
            //Return with error
            $error_message = $e->getMessage();
            return response()->json(['success' => false, 'message' => $error_message,'code'=>400]);
        }

        return response()->json([
            'code'=>200,'success' => true, 'message'=> 'A reset email has been sent! Please check your email.'
        ]);
    }

}
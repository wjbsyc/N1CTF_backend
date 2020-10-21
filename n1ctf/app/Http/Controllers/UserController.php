<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;
use Carbon\Carbon;
class UserController extends Controller
{


    public function I_AM_ADMIN()
    {
        return response()->json(['code'=>200,'isAdmin'=>User::isadmin()]);
    }
/*
    public function profile(){
        if(Auth::check()){
            $id=Auth::id();
            $userdata=User::find($id);
            //dd($userdata);
            return view('profile',['userdata'=>$userdata]);
        }
        else return redirect()->route('login');
    }

    public function updateAll()
    {
        $users = User::all();
        foreach ($users as $user => $v) {
            $users[$user]->updateScoreTime();
        }
        return redirect('scoreboard');
    }
*/

}

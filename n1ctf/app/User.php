<?php

namespace App;

use Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\EmailVerify;
use DB;

class User extends Authenticatable implements MustVerifyEmail,JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'power','score','time','email_verified_at','team_id','api_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','power'
    ];

   /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function isadmin()
    {
        $user = auth('api')->user();
        if ($user) {
            if (Hash::check('admin', $user->power)) return true;
            else return false;
        } else return false;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendEmailVerificationNotification()
    {
        $verification_code = \Str::random(32); //Generate verification code
        DB::table('user_verifications')->insert(['user_id'=>$this->id,'token'=>$verification_code]);
        $this->notify(new EmailVerify($verification_code));
    }


    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'api_token' => \Str::random(60),
        ])->save();
    }

    //关联模型
    public function team()
    {
        return $this->belongsTo('App\teams','teamid');
    }

    // public function challengePassed($challenge)
    // {
    //     return !!$this->challenges()->where('challengeid', $challenge)->count();
    // }

    // //返回某用户解决的题目
    // public static function solvedchallenges($userid)
    // {
    //     $user = User::find($userid);
    //     $challenges = $user->challenges()->get();
    //     $sorted = $challenges->sortByDesc('pivot.created_at');
    //     return $sorted->values();
    // }

    //用户得分
    // public static function userscore($id)
    // {
    //     $solveds = User::solvedchallenges($id);
    //     #echo $solveds;
    //     $totalScore = 0;
    //     foreach ($solveds as $solved) {
    //         $rank = $solved->pivot->rank;
    //         $bonus=0;
    //         if($rank == 1) $bonus = ($solved->score)*0.06;
    //         if($rank == 2) $bonus = ($solved->score)*0.03;
    //         if($rank == 3) $bonus = ($solved->score)*0.02;
    //         $totalScore +=($solved->score + $bonus);
    //     }
    //     return round($totalScore);
    // }
    // public function updateScoreTime()
    // {
    //     #$score=User::userscore($this->id);
    //     #$this->score = $score;
    //     $stime = User::solvedchallenges($this->id)->first();
    //     $this->time = $stime['pivot']['created_at'];
    //     $this->save();
    // }

    //计分板
    // public static function scoreboard()
    // {
    //     $scores = collect([]);
    //     #$sorted = User::orderBy('score','desc')->orderBy('time','asc')->get();
    //     $users = User::has('challenges')->get();
    //     foreach ($users as $user) {
    //         #echo $user;
    //         $id = $user->id;
    //         $name = $user->name;
    //         $totalScore = User::userscore($id);
    //         //$subs = User::solvedchallenges($id)->first();
    //         #$subs = collect([$subs]);
    //         #$time = $subs->first();
    //         $lastsubtime = $user->time;
    //         #pivot['created_at'];
    //         $scores->push(array('id' => $id, 'name' => $name, 'totalScore' => $totalScore, 'lastsubtime' => $lastsubtime));
    //         #echo $totalScore;
    //         #echo '<br>';
    //         #$user->put('totalScore',$totalScore);
    //     }
    //     //echo $scores;
    //     //$sorted = $scores->sortBy('lastsubtime')->sortByDesc('totalScore');

    //     $sorted = $scores->sort(
    //         function ($a, $b) {
    //             return ($b['totalScore'] - $a['totalScore']) ?: strcmp($a['lastsubtime'], $b['lastsubtime']);
    //         }
    //     );
    //     //echo $sorted;
        
    //     // $rank = 0;
    //     // foreach ($sorted as $sort => $v) {
    //     //     $temp = collect($sorted[$sort]);
    //     //     $temp->put('rank', ++$rank);
    //     //     $sorted[$sort] = $temp->toArray();
    //     // }
    //     return $sorted->values();
    // }

    //修改信息
    // public function ProfileEdit($userdata)
    // {
    //         $data = $userdata->all();
    //         $nullpass = (!$data['password'])&&(!$data['password_confirmation']);
    //         if ($userdata['name'] === Auth::user()->name) 
    //         {   
    //             if(!$nullpass)
    //             {
    //                 $v = Validator::make($data, [
    //                     'password' => 'required|string|min:6|confirmed',
    //                     'password_confirmation' => 'same:password',
    //                 ]);
    //             }
    //             else
    //             {
    //                 return redirect()->to('profile')->withErrors(['editsuccess' => 'Nothing Changed']);
    //             }
    //         } 
    //         else 
    //         {
    //             if(!$nullpass)
    //             {
    //                 $v = Validator::make($data, [
    //                     'name' => 'required|string|max:255|unique:users',
    //                     'password' => 'required|string|min:6|confirmed',
    //                     'password_confirmation' => 'same:password',
    //                 ]);
    //             }   
    //             else
    //             {
    //                 $v = Validator::make($data, [
    //                     'name' => 'required|string|max:255|unique:users',
    //                 ]);
    //             }
    //         }
    //         if ($v->fails()) {
    //             return redirect()->to('profile')->withErrors($v->errors());
    //         } else {
    //             $user->name = $userdata['name'];
    //             if(!$nullpass) $user->password = bcrypt($userdata['password']);
    //             if ($user->save()) {
    //                 return redirect()->to('profile')->withErrors(['editsuccess' => 'Edit Succseefully!']);
    //             } else return redirect()->to('profile')->withErrors(['editsuccess' => 'Unknown Error!']);
    //         }

    //     }
    
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class teams extends Model
{
   protected $fillable = [
        'name','time','team_token','nation'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
       'remember_token'
    ];

    public function members()
    {
        return $this->hasMany('App\User','teamid');
    }
    public function score()
    {
        return $this->hasOne('App\score','teamid');
    }
    public function challenges()
    {
        return $this->belongsToMany('App\challenge', 'challenge_teams', 'teamid', 'challengeid')->using('App\challenge_team')->withPivot('created_at','userid','rank');
    }

    public function challengePassed($challenge)
    {
        return !!$this->challenges()->where('challengeid', $challenge)->count();
    }

    //返回解决的题目
    public function teamSolvedChallenges()
    {
        $challenges = $this->challenges()->get();
        foreach ($challenges as $chall => $value) {
        	$uid = $challenges[$chall]['pivot']['user_id'];
        	$uname = App\User::find($uid)->name;
        	$challenges[$chall]['user_name'] = $uname;
        }
        $sorted = $challenges->sortByDesc('pivot.created_at');
        return $sorted->values();
    }



    public static function teamDetail($teamid)
    {
        $team = teams::find($teamid);
        if(!$team) return array([]);
        //dd($team);
        $challenges = $team->challenges()->get(['title','score','class']);
       // dd($challenges);
        $members = $team->members()->get(['name']);
        $nation = $team->nation;
        foreach ($challenges as $chall => $value) {
            $uid = $challenges[$chall]['pivot']['userid'];
            //dd($uid);
            $uname = User::find($uid)->name;
            $challenges[$chall]['user_name'] = $uname;
        }
        $sorted = $challenges->sortByDesc('pivot.created_at');
        return array('team'=>$team->name,'nation'=>$nation,'challs'=>$sorted->values(),'members'=>$members);
    }

    public static function solvedchallenges($teamid)
    {
        $team = teams::find($teamid);
        $challenges = $team->challenges()->where('info','!=','hide')->get();
        $sorted = $challenges->sortByDesc('pivot.created_at');
        return $sorted->values();
    }

    public static function teamscore($id)
    {
        $solveds = teams::solvedchallenges($id);
        #echo $solveds;
        $totalScore = 0;
        foreach ($solveds as $solved) {
            // $rank = $solved->pivot->rank;
            // $bonus=0;
            // if($rank == 1) $bonus = ($solved->score)*0.06;
            // if($rank == 2) $bonus = ($solved->score)*0.03;
            // if($rank == 3) $bonus = ($solved->score)*0.02;
            $totalScore +=($solved->score);
        }
        return round($totalScore);
    }


    public function updateScoreTime()
    {
        #$score=User::userscore($this->id);
        #$this->score = $score;
        $stime = teams::solvedchallenges($this->id)->first();
        $this->time = $stime['pivot']['created_at'];
        $this->save();
    }

    public static function updateScore()
    {
        $users = teams::has('challenges')->get();
        foreach ($users as $user) {
            $id = $user->id;
            $totalScore = teams::teamscore($id);
            $lastsubtime = $user->time;
            $teamscore = $user->score()->first();
            if(!$teamscore)
            {
                $teamscore = score::create(['score'=>0]);
                $teamscore->team()->associate($user);
            }
            $teamscore->score = $totalScore;
            $teamscore->lastsubtime = $lastsubtime;
            $teamscore->save();
        }
    }

    public static function scoreboard($page)
    {
        $scores = collect([]);
        $orders = DB::table('score')
            ->whereNotNull('teamid')
            ->where('score','>','0')
            ->orderByDesc('score')
            ->orderBy('lastsubtime')
            ->forPage($page,15)
            ->get();

        $rs = ($page-1)*15 +1;
        //var_dump($orders);
        foreach($orders as $order)
        {
             $team = teams::find($order->teamid);
             $id = $team->id;
             $name = $team->name;
             $nation = $team->nation;
             $solveds = $team->challenges()->get()->pluck('id');
             $scores->push(array('rank'=>$rs, 'id' => $id, 'name' => $name,'nation'=>$nation, 'totalScore' => $order->score, 'solveds' => $solveds ,'lastsubtime' => $order->lastsubtime));
             $rs++;
        }
        return  $scores->keyBy('rank')->all();
    }
}

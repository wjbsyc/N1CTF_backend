<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class challenge extends Model
{
    //
    protected $table = 'Challenges';
    protected $fillable = ['title', 'class', 'description', 'url', 'flag', 'info', 'score'];
    protected $hidden = ['flag'];

    public function teams()
    {
        return $this->belongsToMany('App\teams', 'challenge_teams', 'challengeid', 'teamid')->using('App\challenge_team')->withPivot('created_at','userid','rank');
    }

    public function hints()
    {
        return $this->hasMany('App\hint','challengeid');
    }

    //返回某题目解决的用户
    public static function solvedteams($challengeid)
    {
        $challenge = challenge::find($challengeid);
        if(!$challenge->count()) return [];
        $users = $challenge->teams()->get();
        $sorted = $users->sortBy('pivot.created_at');
        return $sorted->values();
    }
}
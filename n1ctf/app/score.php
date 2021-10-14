<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class score extends Model
{
    protected $table = 'score';
    protected $fillable = ['score', 'lastsubtime'];
    public function team()
    {
        return $this->belongsTo('App\teams','teamid');
    }
}

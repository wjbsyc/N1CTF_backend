<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
class challenge_team extends Pivot
{

    protected $table='challenge_teams';
    protected $fillable=['teamid','challengeid','rank','userid'];

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class hint extends Model
{
	protected $table = 'hint';
    protected $fillable = ['content', 'challengeid'];
    protected $hidden = ['remember_token'];
    public function challenge()
    {
        return $this->belongsTo('App\challenge','challengeid');
    }


}

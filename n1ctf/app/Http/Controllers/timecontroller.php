<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\time;
use App\Jobs\gamestart;
use Carbon\Carbon;
use App\User;
use App\challenge;
class timecontroller extends Controller
{

    public function over(){
    	if(User::isadmin()){
    	$challenges=challenge::where([])->update(['info' => 'over']);
    	DB::table('jobs')->truncate();
        return response()->json(['code'=>200,'success' => true,'message' => 'Game Over']);
    	}
    	else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }

    public function GameStartNow()
    {   
        if(User::isadmin()){
        $challenges=challenge::where([])->update(['info' => 'start']);
        DB::table('jobs')->truncate();
        return response()->json(['code'=>200,'success' => true,'message' => 'Game Start']);
        }
        else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }
    public function gamestart(Request $data){
    	//$min=$data['min'];
        $data = (json_decode($data->getContent(),true));
        if(User::isadmin()){
        $starttime = Carbon::createFromFormat('Y-m-d H:i:s', $data['starttime']);
        $endtime = Carbon::createFromFormat('Y-m-d H:i:s', $data['endtime']);
        $now = Carbon::now();
        if($starttime < $now) $starttime = $now;
        if($endtime < $starttime) $endtime = $starttime; 

    	$startjob =(new gamestart())->delay($starttime);
    	$job = (new time())->delay($endtime);
        DB::table('jobs')->truncate();
        dispatch($startjob);
        dispatch($job);
        $mess = 'Game will start at '.$starttime->toDateTimeString().' end at '.$endtime->toDateTimeString();
        return response()->json(['code'=>200,'success' => true,'message' => $mess ]);


    	}
    	else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }
}

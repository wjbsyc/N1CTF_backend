<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\teams;
use App\score;
use Validator;
use App\User;
class teamController extends Controller
{
    public function getUserTeam()
    {
    	$user = auth('api')->user();
    	if(!$user) return response()->json(['code'=>400,'message'=>'Please login']);
    	$team = $user->team;
    	if(!$team) return response()->json(['code'=>200,'team'=>null]);
    	else return response()->json(['code'=>200,'team'=>$team]);
    }

    public function create_team(Request $data)
    {
    	$request = (json_decode($data->getContent(),true));
    	$user = auth('api')->user();
    	if(!$user) return response()->json(['code'=>401,'success'=>flase,'message'=>'Please login']);
    	$team = $user->team;
    	//dd($team);
    	if($team) return response()->json(['code'=>400,'success'=>false,'message'=>'Already in a team']);
    	else
    	{
    		$v=Validator::make($request,[
            'name' => 'required|string|max:255|unique:teams',
            'flag'=>'required|boolean'
            ]);
            if ($v->fails()) {
	            return array(
	                'code' => 400,
	                'message' => $v->errors()->first(),
	                'success' => false
	            );
	        }
            $nation_china_mainland = $request['flag'] ? 'true':'false';
    		$team = teams::create(['name'=>$request['name'],'nation'=>$nation_china_mainland,'team_token'=>\Str::random(48)]);
            $score = score::create(['score'=>0]);
            $score->team()->associate($team);
    		$team->members()->save($user);
    		return response()->json(['code'=>200,'success'=>true,'message'=>'OK']);
    	}
    }

    public function join_team(Request $data)
    {
       	$request = (json_decode($data->getContent(),true));
    	$user = auth('api')->user();
    	if(!$user) return response()->json(['code'=>401,'success'=>flase,'message'=>'Please login']);
    	$team = $user->team;
    	if($team) return response()->json(['code'=>400,'success'=>false,'message'=>'Already in a team']);	
    	else
    	{
    		$team_token = $request['team_token'] ?? '';
    		$team = teams::where('team_token',$team_token)->first();
    		if(!$team)
    		{
    			return response()->json(['code'=>400,'success'=>false,'message'=>'Invalid Team Token']);
    		}
    		else
    		{
    			$team->members()->save($user);
    			return response()->json(['code'=>200,'success'=>true,'message'=>'OK']);
    		}
    	}
    }
    public function show_team($id)
    {
    	return response()->json(['code'=>200,'success'=>true,'team'=>teams::teamDetail($id)]);
    }

    public function updateAllScore()
    {
        if (User::isadmin()) {
            teams::updateScore();
            return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
        }
        else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }

}

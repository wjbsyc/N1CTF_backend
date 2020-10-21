<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\teams;
use Validator;

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
            'name' => 'required|string|max:255|unique:teams'
            #'nation'=>'required|string|max:255'
            ]);
            if ($v->fails()) {
	            return array(
	                'code' => 400,
	                'message' => $v->errors()->first(),
	                'success' => false
	            );
	        }
    		$team = teams::create(['name'=>$request['name'],'team_token'=>\Str::random(48)]);
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
}

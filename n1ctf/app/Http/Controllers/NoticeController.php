<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\notice;

class NoticeController extends Controller
{
    public function newnotice(Request $data)
    {
        if (User::isadmin()) {
            //$data->flash();
            $request = (json_decode($data->getContent(),true));
            $add = new notice;
            $r=$add->create(['content' => $request['content'] ?? '',
            				 'remember_token' => \Str::random(60),
            				  ]);
            if($r) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
        }
        else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }

    public function showNotice(){
    	$notices=notice::where([])->orderBy('created_at','desc')->get();
       // dd($notices);
    	return response()->json(['code'=>200,'success'=>true,'notice'=>$notices]);
    }


    public function delete($id){
    	if(User::isadmin()){
    		$notice=notice::find($id);
            $r = false;
    		if($notice) $r=$notice->delete();
    		if($r) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
    	}
    	else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }

    public function edit($id,Request $data){
    	if(User::isadmin()){
            $request = (json_decode($data->getContent(),true));
            $notice = notice::find($id);
            if(!$notice) return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
            $notice->content = $request['content'];
            if ($notice->save()) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
    	}
    	else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }
}

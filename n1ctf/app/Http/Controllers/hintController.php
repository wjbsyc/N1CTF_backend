<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Validator;
use App\hint;
class hintController extends Controller
{
   public function newhint(Request $request)
   {
   		if(User::isadmin())
   		{
   			$request = (json_decode($request->getContent(),true));
   			$v=Validator::make($request,[
                'challengeid' => 'required|exists:Challenges,id',
                'content' => 'required|string|max:255',
            ]);
            if ($v->fails()) {
                return response()->json(array(
                    'code' => 400,
                    'message' => $v->errors()->first(),
                    'success' => false
                ));
            }
            $add = new hint;
            $r=$add->create(['content' => $request['content'] ?? '',
            				  'challengeid' => $request['challengeid'],
            				 'remember_token' => \Str::random(60),
            				  ]);
            if($r) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
   		}
   		else return response()->json(['code'=>400,'success'=>false,'message'=>'permission denied!']);
   }

   public function edithint($id,Request $request)
   {
   		if(User::isadmin()){
            $request = (json_decode($request->getContent(),true));
            $hint = hint::find($id);
            if(!$hint) return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
            $hint->content = $request['content'] ?? '';
            if ($hint->save()) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
    	}
    	else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
   }

   public function deletehint($id)
   {
   		if (User::isadmin()) {
            $hint = hint::find($id);
            if(!$hint) return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
            $hint->challenge()->dissociate();
            if ($hint->delete()) {
                return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            } else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
        } else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);

   }
}

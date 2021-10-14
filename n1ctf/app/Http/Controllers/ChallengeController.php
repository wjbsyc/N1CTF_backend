<?php

namespace App\Http\Controllers;

use App\challenge;
use App\challenge_team;
use App\User;
use App\teams;
use App\Jobs\updatescore;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;

class ChallengeController extends Controller
{
    //

    public function newchallenge(Request $data)
    {
        if (User::isadmin()) {
            $request = (json_decode($data->getContent(),true));
            $v=Validator::make($request,[
                'title' => 'required|string|max:255',
                'class' => 'required|string|max:255',
                'flag' => 'required|string|max:255',
                'info' => Rule::in(['hide','start', 'over']),
            ]);
            if ($v->fails()) {
                return response()->json(array(
                    'code' => 400,
                    'message' => $v->errors()->first(),
                    'success' => false
                ));
            }
            $add = new challenge;
            $r=$add->create([
                'title' => $request['title'],
                'class' => $request['class'],
                'description' => $request['description'] ??'',
                'url' => $request['url'] ?? '',
                'flag' => $request['flag'],
                'info'=>$request['info'],
                'score' => $request['score'] ?? 1000,
            ]);
            if($r) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
        }
        else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }


    public function editchallenge($id, Request $data)
    {
        if (User::isadmin()) {
            $request = (json_decode($data->getContent(),true));
             $v=Validator::make($request,[
                //'id' => 'exists:Challenges',
                'title' => 'required|string|max:255',
                'class' => 'required|string|max:255',
                'flag' => 'required|string|max:255',
                'info' => Rule::in(['hide','start', 'over']),
            ]);
            if ($v->fails()) {
                return response()->json(array(
                    'code' => 400,
                    'message' => $v->errors()->first(),
                    'success' => false
                ));
            }
            $challenge = challenge::find($id);
            $challenge->title = $request['title'];
            $challenge->class = $request['class'];
            $challenge->description = $request['description'] ??'';
            $challenge->url = $request['url'] ?? '';
            $challenge->flag = $request['flag'];
            $challenge->info=$request['info'];
            $challenge->score = $request['score'] ?? 1000;
            if ($challenge->save()) return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
        }
        else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }

    public function delete($id)
    {
        if (User::isadmin()) {
            $challenge = challenge::find($id);
            $challenge->teams()->detach();
            if ($challenge->delete()) {
                return response()->json(['code'=>200,'success' => true,'message' => 'OK']);
            } else return response()->json(['code'=>400,'success' => false,'message' => 'ERROR!']);
        } else return response()->json(['code'=>400,'success' => false,'message' => 'Permission Denied']);
    }


    public function ShowScoreBoard(Request $request)
    {
        $page = $request->get('page') ?? 1;
        //dd($page);
        $page = $page >= 1 ? round($page):1;
        $users = teams::scoreboard($page);
        $team_count = teams::has('challenges')->count();
        return response()->json(['code'=>200,'success'=>true,'teams'=>$users,'total'=>$team_count]);
    }



//=====================
    // public function ShowScore()
    // {
    //     if (Auth::check()) {
    //         $id = Auth::id();
    //         return redirect('/userDetail#'.$id);
    //     } else return redirect()->route('login');
    // }

    // public function userDetail($id)
    // {
    //         $score = User::userscore($id);
    //         $challenges = User::solvedchallenges($id);
    //         $name=User::find($id)->name;
    //         //return $challenges;
    //         $re = array('name'=>$name ,'score' => $score, 'challenges' => $challenges);
    //         return $re;
    //         //return view('score', ['name'=>$name ,'score' => $score, 'challenges' => $challenges]);
    // }
//=========================
    //  api part

    /**
     * api method
     *
     * get questions belongs to a class
     *
     * @param Request $request
     * @return mixed
     */

    // public function submitsHistory(Request $data){
    //     $users=User::all();
    //     $sub=collect([]);
    //     foreach ($users as $user) {
    //         $sol=User::solvedchallenges($user->id);
            
    //         foreach ($sol as $s) {
    //             $test = array('name'=>$user->name,'challenge'=>$s['title'],'score'=>$s['score'],'class'=>$s['class'],'time'=>$s['pivot']['created_at']->toDateTimeString());
    //             $sub->push($test);
    //         }
    //     }
        
    //     return $sub->sortByDesc('time')->values();
    //     //return view('results',['challenges'=>$sub->sortByDesc('time')]);
    // }

    public function getQuestionsBelongsToClass(Request $request)
    {
        $user = auth('api')->user();
        $power=$user?$user->power:'no';
        $team = $user->team;
        if(!$team && !User::isadmin()) return response()->json(['code'=>400,'success'=>false,'message'=>'you need to create or join a team!']);
        //$request = (json_decode($request->getContent(),true));
        $class = $request->get('class') ?? null;
        if(!Hash::check('admin', $power))
        {            
            if($class)
            {   
                $challenges = challenge::where('class', $class)->where('info','!=','hide')
                ->select('id', 'title', 'score','class')
                ->get();
            }
            else
            {
                $challenges = challenge::where('info','!=','hide')
                ->select('id', 'title', 'score','class')
                ->get();
            }
        }
        else{
            if($class)
            {   
                $challenges = challenge::where('class', $class)
                ->select('id', 'title', 'score','class','info')
                ->get();
            }
            else
            {
                 $challenges = challenge::where([])
                ->select('id', 'title', 'score','class','info')
                ->get();
            }
             
        }
        foreach ($challenges as $challenge => $v) {
             $challenges[$challenge]->solversCount = $challenges[$challenge]->teams()->count();
        }

        //$user = Auth::guard('api')->user();
        if (!!$team) {
            $challenges->map(function ($challenge) use ($team){
                $challenge->passed = $team->challengePassed($challenge->id);
                return $challenge;
            });
        }

        return response()->json(['code'=>200,'success'=>true,'challs'=>$challenges]);
    }


    /**
     * api method
     *
     * get question detail
     *
     * @param challenge $challenge
     * @return array
     */
    public function getQuestionDetail(challenge $challenge)
    {
        $user = auth('api')->user();
        $power = !!$user ? Hash::check('admin', $user->power) : false;
        if($challenge->info != 'hide'||$power){
        $flag="Homura";
        if($power)
        {
            $flag=$challenge->flag;
        }
        return response()->json([
        'code'=>200,
        'success'=>true,
        'chal'=>[
            'title' => $challenge->title,
            'score'=>$challenge->score,
            'description' => $challenge->description,
            'url' => $challenge->url,
            'class' => $challenge->class,
            'solves' => $challenge->teams()->count(),
            'hints' =>$challenge->hints,
            'flag' => $flag
            //'power' => $power
        ]]);
        }
    }

    /**
     * api method
     *
     * validate flag
     *
     * @param challenge $challenge
     * @param Request $request
     * @return string
     */
    public function submitFlag(challenge $challenge, Request $request)
    {
        $user = auth('api')->user();
        //dd($user->id);
        $team = $user->team;
        $request = (json_decode($request->getContent(),true));
        if(!$team) return response()->json(['code'=>400,'success'=>false,'message'=>'Please join or create a team!']);
        if ($team->challengePassed($challenge->id)) {
            return response()->json(['code'=>400,'success'=>false,'message'=>'Already passed']);
        }
        if($challenge->info != 'start') return response()->json(['code'=>400,'success'=>false,'message'=>'Game Over!']);
        $dyn = ENV('DYN_FLAG');
        $token = $team->team_token;
        $dyn_prefix = env('DYN_FLAG_PREFIX','N1CTF');
        if (($challenge->flag === $request['flag'] || ($dyn && $dyn_prefix.'{'.hash('sha256',($challenge->flag).$token,false).'}' === $request['flag'])) && $challenge->info==='start') 
        {
            $id=$challenge->id;
            $c=challenge::find($id);       
            DB::beginTransaction();
            $count=challenge_team::where([['teamid','=',$team->id],['challengeid','=',$challenge->id]])->lockForUpdate()->count();
            if($count==0) 
            {   
                $cnt = $c->teams()->count();
                challenge_team::create(['teamid'=>$team->id,'userid' => $user->id, 'challengeid' => $challenge->id,'rank'=>($cnt+1)]);
            }
            DB::commit();
            $team->updateScoreTime();
            $cnt=$c->teams()->count();
            if($cnt)
            {   
                if( $cnt > 1 && $c->score >=0 )
                {
                    $an = $c->score;
                    $an1 = $an*(($cnt+8.0)/($cnt+9.0));
                    $c->score=round($an1);
                }
                $c->save();
            }
            DB::table('jobs')->where('queue','update')->delete();
            $updatejob = (new updatescore());
            dispatch($updatejob)->onQueue('update');
            return response()->json(['code'=>200,'success'=>true,'message'=>'true']);
        }
        return response()->json(['code'=>400,'success'=>false,'message'=>'false']);
    }

    /**
     * api method
     *
     * delete challenge
     *
     * @param challenge $challenge
     * @return string
     */
    public function deleteChallenge(challenge $challenge)
    {
        //$user =auth('api')->user();

        if (User::isadmin()) 
        {
            // 解除对应关系
            $challenge->teams()->detach();
            // 删除
            $res =  $challenge->delete() ? true : false;
            if($res) return response()->json(['code'=>200,'success'=>true,'message'=>'OK']);
            else return response()->json(['code'=>200,'success'=>false,'message'=>'FALSE']);
        } 
        else 
        {
            // 需要管理员权限
            return response()->json(['code'=>400,'success'=>false,'message'=>'Administrator permission is required']);
        }
    }


    /**
     * 判断管理员权限
     *
     * @param $power
     * @return mixed
     */
    public static function isAdmin($power)
    {
        return Hash::check('admin', $power);
    }


    public function getSolvers(challenge $challenge)
    {
        $users = $challenge->teams()->select('name')->get();
        $sorted = $users->sortBy('pivot.created_at');
        return response()->json(['code'=>200,'success'=>true,'solvers'=>$sorted->values()]);
    }
}
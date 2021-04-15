<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Wall;
use App\Models\WallLike;

class WallController extends Controller
{
    public function getAll(){
        $array = ['error'=>'', 'list'=>''];

        $user = auth()->user();

        $walls = Wall::all();

        foreach($walls as $wallKey => $wallValue){
            $walls[$wallKey]['likes'] = 0;
            $walls[$wallKey]['liked'] = false;

            $likes = WallLike::where('id_wall', $wallValue['id'])->count();
            $walls[$wallKey]['likes'] = $likes;

            $meLikes = WallLike::where('id_wall', $wallValue['id'])
            ->where('id_user', $user['id'])
            ->count();

            if($meLikes > 0){
                $walls[$wallKey]['liked'] = true;
            }
        }

        $array['list'] = $walls;
        return $array;
    }

    public function like($id){
        $array = ['error'=>''];

        $user = auth()->user();

        $meLikes = WallLike::where('id_wall', $id)
            ->where('id_user', $user['id'])
            ->count();

        if($meLikes > 0){
            WallLike::where('id_wall', $id)
            ->where('id_user', $user['id'])
            ->delete();

            $array['liked'] = false;
        }else{
            $newLike = new WallLike();
            $newLike->id_wall = $id;
            $newLike->id_user = $user['id'];
            $newLike->datecreated = now();
            $newLike->save();

            $array['liked'] = true;
        }
        $array['likes'] = WallLike::where('id_wall', $id)->count();
        return $array;
    }

    public function AddWall(Request $request){
        $array = ['error'=>''];

        $title = $request->input('title');
        $body = $request->input('body');

        $validator = Validator::make($request->all(), [
            'title'=> 'required',
            'body'=> 'required'
        ]);

        if(!$validator->fails()){
            $wall = new Wall();
            $wall->title = $title;
            $wall->body = $body;
            $wall->datecreated = now();
            $wall->save();

            $array['wall'] = $wall;
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }     

        return $array;   
    }

    public function UpdeteWall(Request $request, $id){
        $array = ['error'=>''];

        $title = $request->input('title');
        $body = $request->input('body');


        $wall = Wall::find($id);
        $wall->title = $title;
        $wall->body = $body;
        $wall->save();

        $array['wall'] = $wall;
        return $array;  

    }    

    public function RemoveWall($id){
        $array = ['error'=>''];
        
        $wall = Wall::find($id);
        $wall->delete();

        return $array;  

    }

}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\FoundAndLost;

class FoundAndLostController extends Controller
{
    public function getAll(){
        $array = ['error'=>''];

        $lost = FoundAndLost::where('status', 'LOST')
        ->orderBy('datecreated', 'DESC')
        ->orderBy('id', 'DESC')
        ->get();

        $recovered = FoundAndLost::where('status', 'RECOVERED')
        ->orderBy('datecreated', 'DESC')
        ->orderBy('id', 'DESC')
        ->get();

        foreach($lost as $lostKey => $lostValue){
            $lost[$lostKey]['datecreated'] = date('d/m/Y H:i:s', strtotime($lostValue['datecreated']));
            $lost[$lostKey]['photo'] = asset('storage/'.$lostValue['photo']);
        }

        foreach($recovered as $recoveredKey => $recoveredValue){
            $recovered[$recoveredKey]['datecreated'] = date('d/m/Y H:i:s', strtotime($recoveredValue['datecreated']));
            $recovered[$recoveredKey]['photo'] = asset('storage/'.$recoveredValue['photo']);
        }

        $array['lost'] = $lost;
        $array['recovered'] = $recovered;

        return $array;
    }

    public function insert(Request $request){
        $array = ['error'=>'', 'photo'=>[]];

        
        $validator = Validator::make($request->all(),[
            'description'=> 'required',
            'where'=> 'required',
            'photo'=>'required|file|mimes:jpg,png,jpeg'
        ]);
        
        $array['photo'] = $request->file('photo');
        if (!$validator->fails()) {
            $description = $request->input('description');
            $where = $request->input('where');
            $file = $request->file('photo')->store('public');
            $file = explode('public/', $file);
            $photo = $file[1];

            $newLost = new FoundAndLost();
            $newLost->status = 'LOST';
            $newLost->description = $description;
            $newLost->photo = $photo;
            $newLost->where = $where;
            $newLost->datecreated = now();
            $newLost->save();

            
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }


        return $array;
    }

    public function update($id, Request $request){
        $array = ['error'=>''];
        
        $status = $request->input('status');
        
        if($status && in_array($status,['lost','recovered'])){
            $item = FoundAndLost::find($id);
            if($item){
                $item->status = $status;
                $item->save();
            }else{
                $array['error'] = 'Item inválido.';
            }
        }else{
            $array['error'] = 'Status inválido.';
        }

        return $array;
    }
    public function getFoundAndLost(){
        $array = ['error'=>''];

        $lost = FoundAndLost::orderBy('datecreated', 'DESC')
        ->orderBy('id', 'DESC')
        ->get();

        

        foreach($lost as $lostKey => $lostValue){
            $lost[$lostKey]['datecreated_formate'] = date('d/m/Y H:i:s', strtotime($lostValue['datecreated']));
            $lost[$lostKey]['photo'] = asset('storage/'.$lostValue['photo']);
        }

        $array['list'] = $lost;

        return $array;
    }

    public function doneFoundAndLost($id){
        $array = ['error'=>''];
        $item = FoundAndLost::find($id);
        if($item){
            if ($item->status === "lost") {
                $array['error'] = $item->status;
                $item->status = "recovered";
                $array['list'] = $item->status;
                $item->save();
            }else{
                $array['error'] = $item->status;
                $item->status = "lost";
                $array['list'] = $item->status;
                $item->save();
            }
        }
        return $array;
    }
    public function setFoundAndLost(Request $request){
        $array = ['error'=>''];
        
        $validator = Validator::make($request->all(),[
            'description'=> 'required',
            'where'=> 'required',
            'photo'=>'required|file|mimes:jpg,png,jpeg'
        ]);
        
        if (!$validator->fails()) {
            $description = $request->input('description');
            $where = $request->input('where');
            $file = $request->file('photo')->store('public');
            $file = explode('public/', $file);
            $photo = $file[1];

            $newLost = new FoundAndLost();
            $newLost->status = 'lost';
            $newLost->description = $description;            
            $newLost->where = $where;
            $newLost->photo = $photo;
            $newLost->datecreated = now();
            $newLost->save();

            
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
        return $array;
    }
    public function updateFoundAndLost($id, Request $request){
        $array = ['error'=>''];
        $newLost = FoundAndLost::find($id);
        
        $validator = Validator::make($request->all(),[
            'description'=> 'required',
            'where'=> 'required'
        ]);
        
        if (!$validator->fails()) {
            $description = $request->input('description');
            $where = $request->input('where');
            if ($request->file('photo')) {
                $validator = Validator::make($request->all(),[
                    'photo'=>'required|file|mimes:jpg,png,jpeg'
                ]);
                if (!$validator->fails()) {
                    $file = $request->file('photo')->store('public');
                $file = explode('public/', $file);
                $photo = $file[1];
                $newLost->photo = $photo;
                }
            }

            $newLost->status = 'lost';
            $newLost->description = $description;            
            $newLost->where = $where;
            $newLost->save();

            
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
        return $array;
    }
    public function deleteFoundAndLost($id){
        $array = ['error'=>''];
        
        $found = FoundAndLost::find($id);
        $found->delete();

        return $array;  

    }
}

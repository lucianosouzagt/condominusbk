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
            $lost[$lostKey]['photp'] = asset('storage/'.$lostValue['photo']);
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
}

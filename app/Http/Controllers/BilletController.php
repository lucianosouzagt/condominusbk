<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\Billet;
use App\Models\Unit;

class BilletController extends Controller
{
    public function getAll(Request $request){
        $array = ['error'=>''];

        $property = $request->input('property');
        
        if($property){

            $user = auth()->user();
            $unit = Unit::where('id',$property)
            ->where('id_owner', $user['id'])
            ->count();
            
            if($unit > 0){
                $billets = Billet::where('id_unit', $property)->get();

                foreach($billets as $billetKey =>$billetValue){
                    $billets[$billetKey]['fileurl'] = asset('storage/'.$billetValue['fileurl']);
                }
                $array['list'] = $billets;
            }else{
                $array['error'] = 'Está propriedade é de outro usuário.';
            }

        }else{
            $array['error'] = 'Propriedade e obrigatoria.';
        }

        return $array;
    }

    public function getBillets(Request $request){
        $array = ['error'=>''];
        
        $billets = Billet::join('units','units.id','billets.id_unit')->select('billets.*','units.name')->orderBy('datecreated', 'desc')->get();

        foreach($billets as $billetKey =>$billetValue){
            $billets[$billetKey]['fileurl'] = asset('storage/billets/'.$billetValue['fileurl']);
        }
        $array['list'] = $billets;
       
        return $array;
    }

    public function AddBillet(Request $request){
        $array = ['error'=>'', 'file'=>[]];

        $validator = Validator::make($request->all(),[
            'unit'=> 'required',
            'title'=> 'required',
            'billet'=>'required|file|mimes:pdf'
        ]);
        
        $array['billet'] = $request->file('billet');
        if (!$validator->fails()) {
            $unit = $request->input('unit');
            $title = $request->input('title');
            $file = $request->file('billet')->store('public/billets');
            $file = explode('public/billets/', $file);
            $billet = $file[1];

            $newBillet = new Billet();
            $newBillet->id_unit = $unit;
            $newBillet->title = $title;
            $newBillet->fileurl = $billet;
            $newBillet->datecreated = now();
            $newBillet->save();

            
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
    
        return $array;
    }

    public function UpdateBillet(Request $request, $id){
        $array = ['error'=>'', 'file'=>[]];

        if($request->file('billet')){
            $validator = Validator::make($request->all(),[
                'unit'=> 'required',
                'title'=> 'required',
                'billet'=>'required|file|mimes:pdf'
            ]);
        }else{
            $validator = Validator::make($request->all(),[
                'unit'=> 'required',
                'title'=> 'required'
            ]);    
        }        
        
        if (!$validator->fails()) {
            $unit = $request->input('unit');
            $title = $request->input('title');
            if($request->file('billet')){
                $file = $request->file('billet')->store('public/billets');
                $file = explode('public/billets/', $file);
                $billet = $file[1];

                $newBillet = Billet::find($id);
                $newBillet->id_unit = $unit;
                $newBillet->title = $title;
                $newBillet->fileurl = $billet;
                $newBillet->datecreated = now();
                $newBillet->save();
            }else{
                $newBillet = Billet::find($id);
                $newBillet->id_unit = $unit;
                $newBillet->title = $title;
                $newBillet->datecreated = now();
                $newBillet->save();
            }
                        
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
    
        return $array;
    }

    public function RemoveBillet($id){
        $array = ['error'=>''];
        
        $billet = Billet::find($id);
        $billet->delete();

        return $array;  

    }
}

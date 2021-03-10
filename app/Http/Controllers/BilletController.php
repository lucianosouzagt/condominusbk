<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Storage;

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
}

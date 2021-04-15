<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\Doc;
class DocController extends Controller
{
    public function getAll(){
        $array = ['error'=>''];

        $docs = Doc::all();
        foreach($docs as $docKey =>$docValue){
            $docs[$docKey]['fileurl'] = asset('storage/documents/'.$docValue['fileurl']);
        }
        
        $array['list'] = $docs;

        return $array;
    }

    public function AddDoc(Request $request){
        $array = ['error'=>''];
        
        $validator = Validator::make($request->all(),[
            'title'=> 'required',
            'doc'=>'required|file|mimes:pdf'
        ]);
        
        if (!$validator->fails()) {
            $title = $request->input('title');
            $file = $request->file('doc')->store('public/documents');
            $file = explode('public/documents/', $file);
            $doc = $file[1];

            $newDoc = new Doc();
            $newDoc->title = $title;
            $newDoc->fileurl = $doc;
            $newDoc->datecreated = now();
            $newDoc->save();

            
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
    
        return $array;
    }

    public function UpdateDoc(Request $request, $id){
        $array = ['error'=>''];

        if($request->file('doc')){
            $validator = Validator::make($request->all(),[
                'title'=> 'required',
                'doc'=>'required|file|mimes:pdf'
            ]);
        }else{
            $validator = Validator::make($request->all(),[
                'title'=> 'required'
            ]);    
        }        
        
        if (!$validator->fails()) {
            $unit = $request->input('unit');
            $title = $request->input('title');
            if($request->file('doc')){
                $file = $request->file('doc')->store('public/documents');
                $file = explode('public/documents/', $file);
                $doc = $file[1];

                $newDoc = Doc::find($id);
                $newDoc->title = $title;
                $newDoc->fileurl = $doc;
                $newDoc->datecreated = now();
                $newDoc->save();
            }else{
                $newDoc = Doc::find($id);
                $newDoc->title = $title;
                $newDoc->datecreated = now();
                $newDoc->save();
            }
                        
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
    
        return $array;
    }

    public function RemoveDoc($id){
        $array = ['error'=>''];
        
        $doc = Doc::find($id);
        $doc->delete();

        return $array;  
    }

}

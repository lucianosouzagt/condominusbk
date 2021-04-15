<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\Area;
use App\Models\Unit;
use App\Models\Reservation;
use App\Models\AreaDisabledDay;

class ReservationController extends Controller
{
    public function getReservations(){
        $array = ['error'=>'', 'list'=> []];

        $areas = Area::where('allowed', 1)->get();

        /*        
        1 - Segunda
        2 - Terça
        3 - Quarta
        4 - Quinta
        5 - Sexta
        6 - Sabado
        0 - Domingo
        */
        $daysHelper = ['Seg', 'Ter', 'Quar', 'Quin', 'Sex', 'Sab','Dom'];

        foreach($areas as $area) {
            $dayList = explode(',', $area['days']);
            $dayGroups = [];
            foreach($dayList as $day){
                $dayGroups[] = $daysHelper[$day];
            }
            
            //Adicionando o time
            $start = date('H:i', strtotime($area['start_time']));
            $end = date('H:i', strtotime($area['end_time']));
            foreach($dayGroups as $dayKey => $dayValue){
                $dayGroups[$dayKey] .= ' '.$start.' às '.$end; 
            }
            
            $array['list'][] = [
                'id'=> $area['id'],
                'cover'=> asset('storage/'.$area['cover']),
                'title'=> $area['title'],
                'dates'=> $dayGroups,

            ];   

        }
        
        return $array;
    }
    public function setReservation($id, Request $request){
        $array = ['error'=>''];

        $validator = Validator::make($request->all(),[
            'date'=> 'required|date_format:Y-m-d',
            'time'=> 'required|date_format:H:i:s',
            'property'=> 'required'
        ]);
        if(!$validator->fails()){
            $date = $request->input('date');
            $time = $request->input('time');
            $property = $request->input('property');

            $unit = Unit::find($property);
            $area = Area::find($id);

            if ($unit && $area) {
                $can = true;
                $weekday = date('w',strtotime($date));
                
                //Verificar se está dentro da disponibilidade Padrão
                $allowedDays = explode(',', $area['days']);
                if (!in_array($weekday, $allowedDays)) {
                    
                    $can = false;
                }else{
                    $start = strtotime($area['start_time']);
                    $end = strtotime('-1 hour', strtotime($area['end_time']));
                    $revtime = strtotime($time);
                    if ($revtime < $start || $revtime > $end) {
                        $can = false;
                    }
                }

                //Verificar se está dentro dos DIsabledDays
                $existingDisabledDay = AreaDisabledDay::where('id_area', $id)
                ->where('day', $date)
                ->count();

                if ($existingDisabledDay > 0) {
                    $can = false;
                }

                //verificar se não existe outra reserva
                $existingReservation = Reservation::where('id_area', $id)
                ->where('reservation_date', $date.' '.$time)
                ->count();

                if ($existingReservation > 0) {
                    $can = false;
                }


                if ($can) {
                    $newReservation = new Reservation();
                    $newReservation->id_unit = $property;
                    $newReservation->id_area = $id;
                    $newReservation->reservation_date = $date.' '.$time;
                    $newReservation->datecreated = now();
                    $newReservation->save();
                    
                }else{
                    $array['error'] = 'Reserva não disponivel nesse horario';
                return $array;
                }
            }else{
                $array['error'] = 'Dados inválidos.';
                return $array;
            }
        }else{
            $array['error'] = $validator->errors()->first();
        }

        return $array;
    }

    public function getDisabledDates($id, Request $request){
        $array = ['error'=>'', 'list'=>[]];
        $area = Area::find($id);
        if($area){
            $disableDays = AreaDisabledDay::where('id_area', $id)->get();
            $dayList = [];
            foreach ($disableDays as $disableDay) {
                $array['list'][] = $disableDay['day'];
            }

            $allowedDays = explode(',', $area['days']);
            $offDays = [];
        for ($i=0; $i < 7; $i++) { 
                if (!in_array($i, $allowedDays)) {
                    $offDays[] = $i;
                }
            }
            $start = time();
            $end = strtotime('+3 months');

            for (
                $current = $start; 
                $current < $end; 
                $current = strtotime('+1 day', $current)
            ) { 
                $wd = date('w', $current);
                if(in_array($wd,$offDays)){
                    $array['list'][] = date('Y-m-d', $current);
                }
            }
        }else{
            $array['error'] = 'Area não existe';
        }

        return $array;
    }

    public function setDisabledDates($id, Request $request){
        $array = ['error'=>''];
        $date = $request->input('date');
        $area = Area::find($id);
        if($area){
            $validator = Validator::make($request->all(),[
                'date'=> 'required|date_format:Y-m-d'
            ]);
            if(!$validator->fails()){
                $dayExist = AreaDisabledDay::where('id_area',$id)->where('day', $date)->count();
                if($dayExist > 0 ){                
                    $array['error'] = 'Data já cadastrada.';
                    return $array;
                }else{
                    $disableDay = new AreaDisabledDay();
                    $disableDay->id_area = $id;
                    $disableDay->day = $date;
                    $disableDay->datecreated = now();
                    $disableDay->save();
                }
                
            }else{
                $array['error'] = $validator->errors()->first();
                return $array;
            }
        }else{
            $array['error'] = 'Area não existe.';
            return $array;
        }
        
        return $array;
    }

    public function getTimes($id, Request $request){
        $array = ['error'=>'', 'list'=> []];

        $validator = Validator::make($request->all(),[
                'date'=> 'required|date_format:Y-m-d'
        ]);

        if(!$validator->fails()){
            $date = $request->input('date');
            $area = Area::find($id);

            if($area){
                $can = true;
                //verificar se é dia disabled
                $existingDisabledDay = AreaDisabledDay::where('id_area', $id)
                ->where('day', $date)
                ->count();

                if ($existingDisabledDay > 0) {
                    $can = false;
                }

                //verifica se e dia permitido
                
                $allowedDays = explode(',', $area['days']);
                $wd = date('w', strtotime($date));
                if (!in_array($wd, $allowedDays)) {
                    $can = false;
                }

                if ($can) {
                    $start = strtotime($area['start_time']);
                    $end = strtotime($area['end_time']);
                    $times = [];

                    for(
                        $lastTime = $start;
                        $lastTime < $end;
                        $lastTime = strtotime('+1 hour', $lastTime)
                    ){
                        $times[] = $lastTime;
                    }

                    $timeList = [];
                    foreach ($times as $time){
                        $timeList[] = [
                            'id'=> date('H:i:s', $time),
                            'title'=> date('H:i',$time).' - '.date('H:i', strtotime('+1 hour', $time))
                        ];
                    }

                    //Removendo as reservas
                    $reservations = Reservation::where('id_area', $id)
                    ->whereBetween('reservation_date', [
                        $date.' 00:00:00',
                        $date.' 23:59:59'
                    ])->get();

                    $toRomove = [];
                    foreach($reservations as $reservation){
                        $time = date('H:i:s', strtotime($reservation['reservation_date']));
                        $toRomove[]=$time;
                    }

                    foreach($timeList as $timeItem){
                        if (!in_array($timeItem['id'], $toRomove)) {
                            $array['list'][] = $timeItem;
                        }
                    }
                    
                }
            }else{
                $array['error'] = 'Area não existe.';
                return $array;
            }
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
        return $array;
    }

    public function getMyReservations(Request $request){
        $array = ['error'=> '','list'=>[]];

        $validator = Validator::make($request->all(),[
            'property'=> 'required'
        ]);
        if(!$validator->fails()){
            $property = $request->input('property');
            $unit = Unit::find($property);
            if($unit){
                $reservations = Reservation::where('id_unit', $property)
                ->orderBy('reservation_date', 'DESC')
                ->get();

                foreach ($reservations as $reservation) {
                    $area = Area::find($reservation['id_area']);
                    $dateRev = date('d/m/Y H:i',strtotime($reservation['reservation_date']));
                    $afterTime = date('H:i', strtotime('+1 hour', strtotime($reservation['reservation_date'])));
                    $dateRev .= ' à '.$afterTime;

                    $array['list'][] = [
                        'id'=> $reservation['id'],
                        'id_area'=> $reservation['id_area'],
                        'title'=> $area['title'],
                        'cover'=> asset('storage/'.$area['cover']),
                        'datereserved'=> $dateRev
                    ];

                }
            }else{
                $array['error'] = 'Propriedade não existe.';
                return $array;
            }
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
        return $array;
    }

    public function delMyReservations($id, Request $request){
        $array = ['error'=> ''];

        $user = auth()->user();
        $reservation = Reservation::find($id);
        if($reservation){
            $unit = Unit::where('id',$reservation['id_unit'])
            ->where('id_owner', $user['id'])
            ->count();
            
            if($unit > 0){
                Reservation::find($id)->delete();
            }else{
                $array['error'] = 'Está reserva não é sua.';
                return $array;
            }

        }else{
            $array['error'] = 'Reserva não existe.';
            return $array;
        }

        return $array;
    }

    public function getAreas(Request $request){
        $array = ['error'=>''];
            
        $areas = Area::orderBy('title')->get();

        foreach($areas as $areaKey =>$areaValue){
            $areas[$areaKey]['cover'] = asset('storage/areas/'.$areaValue['cover']);
        }
        $array['list'] = $areas;

       
        return $array;
    }

    public function AddArea(Request $request){
        $array = ['error'=>''];
            
        $validator = Validator::make($request->all(),[
            'title'=> 'required',
            'cover'=>'required|file|mimes:png,jpg,jpeg',
            'days'=>'required',
            'start_time'=>'required|date_format:H:i:s',
            'end_time'=> 'required|date_format:H:i:s',
        ]);
        
        if (!$validator->fails()) {
            $title = $request->input('title');
            $file = $request->file('cover')->store('public/areas');
            $file = explode('public/areas/', $file);
            $cover = $file[1];
            $days = $request->input('days');
            $start_time = $request->input('start_time');
            $end_time = $request->input('end_time');

            $newArea = new Area();
            $newArea->title = $title;
            $newArea->cover = $cover;
            $newArea->days = $days;
            $newArea->start_time = $start_time;
            $newArea->end_time = $end_time;
            $newArea->datecreated = now();
            $newArea->save();

            
        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }

       
        return $array;
    }

    public function UpdateArea(Request $request, $id){
        $array = ['error'=>'', 'cover'=>''];
            
        if($request->file('cover')){
            $validator = Validator::make($request->all(),[
                'title'=> 'required',
                'cover'=>'required|file|mimes:png,jpg,jpeg',
                'days'=>'required',
                'start_time'=>'required|date_format:H:i:s',
                'end_time'=> 'required|date_format:H:i:s',
            ]);
        }else{
            $validator = Validator::make($request->all(),[
                'title'=> 'required',
                'days'=>'required',
                'start_time'=>'required|date_format:H:i:s',
                'end_time'=> 'required|date_format:H:i:s',
            ]);
        }        
        if (!$validator->fails()) {
            $title = $request->input('title');
            $days = $request->input('days');
            $start_time = $request->input('start_time');
            $end_time = $request->input('end_time');
            if($request->file('cover')){
                $file = $request->file('cover')->store('public/areas');
                $file = explode('public/areas/', $file);
                $cover = $file[1];

                $newArea = Area::find($id);
                Storage::delete('areas/' + $newArea['cover']);
                $array['cover'] = $newArea['cover'];
                $newArea->title = $title;
                $newArea->cover = $cover;
                $newArea->days = $days;
                $newArea->start_time = $start_time;
                $newArea->end_time = $end_time;
                $newArea->datecreated = now();
                $newArea->save();
            }else{
                $newArea = Area::find($id);
                $newArea->title = $title;
                $newArea->days = $days;
                $newArea->start_time = $start_time;
                $newArea->end_time = $end_time;
                $newArea->datecreated = now();
                $newArea->save();
            }

        }else{
            $array['error'] = $validator->errors()->first();
            return $array;
        }
       
        return $array;
    }

    public function DisabledArea($id){
        $array = ['error'=>''];

        $area = Area::find($id);
        if($area){
            if($area['allowed'] == 0){
                $area->allowed = 1;
            }else{
                $area->allowed = 0;
            }
            $area->save();
        }else{
            $array['error'] = 'Area não existe.';
            return $array;
        }
        return $array;
    }

    public function RemoveArea($id){
        $array = ['error'=>''];
        
        $area = Area::find($id);
        $area->delete();

        return $array;  

    }
}



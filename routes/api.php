<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BilletController;
use App\Http\Controllers\DocController;
use App\Http\Controllers\FoundAndLostController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WallController;
use App\Http\Controllers\WarningController;


Route::get('/ping', function(){
    return ['pong'=>true];
});

Route::get('/401', [AuthController::class, 'unauthorized'])->name('login');

Route::post('/auth/login',[AuthController::class, 'login']);
Route::post('/auth/register',[AuthController::class, 'register']);

Route::middleware('auth:api')->group(function(){
    Route::post('/auth/validate', [AuthController::class, 'validateToken']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    //Mural de avisos
    Route::get('/walls', [WallController::class, 'getAll']);
    Route::post('/wall/{id}/like', [WallController::class, 'like']);

    //Documentos
    Route::get('/docs', [DocController::class, 'getAll']);
    

    //Livro de ocorrencias
    Route::get('/warnings', [WarningController::class, 'getWarnings']);
    Route::post('/warning', [WarningController::class, 'setWarning']);
    Route::post('/warning/file', [WarningController::class, 'addWarningFile']);

    //Boletos
    Route::get('/billets', [BilletController::class, 'getAll']);

    //Achados e Perdidos
    Route::get('/foundandlost', [FoundAndLostController::class, 'getAll']);
    Route::post('/foundandlost', [FoundAndLostController::class, 'insert']);
    Route::put('/foundandlost/{id}', [FoundAndLostController::class, 'update']);

    //Unidade
    Route::get('/unit/{id}', [UnitController::class, 'getInfo']);
    Route::post('/unit/{id}/addperson', [UnitController::class, 'addPerson']);
    Route::post('/unit/{id}/addvehicle', [UnitController::class, 'addVehicle']);
    Route::post('/unit/{id}/addpet', [UnitController::class, 'addPet']);
    Route::post('/unit/{id}/removeperson', [UnitController::class, 'removePerson']);
    Route::post('/unit/{id}/removevehicle', [UnitController::class, 'removeVehicle']);
    Route::post('/unit/{id}/removepet', [UnitController::class, 'removePet']);

    //Reservas

    Route::get('/reservations',[ReservationController::class, 'getReservations']);
    Route::post('/reservation/{id}',[ReservationController::class, 'setReservation']);

    Route::get('/reservations/{id}/disableddates',[ReservationController::class, 'getDisabledDates']);
    Route::post('/reservations/{id}/disableddates',[ReservationController::class, 'setDisabledDates']);
    Route::get('/reservations/{id}/times',[ReservationController::class, 'getTimes']);

    Route::get('/myreservations',[ReservationController::class, 'getMyReservations']);
    Route::delete('/myreservation/{id}',[ReservationController::class, 'delMyReservations']);    
});

//Sistema Web
Route::prefix('admin')->group(function () { 
    Route::post('/auth/login',[AuthController::class, 'loginWeb']);
    Route::post('/auth/register',[AuthController::class, 'registerWeb']);

    Route::middleware('auth:api')->group(function(){
        Route::post('/auth/validate', [AuthController::class, 'validateTokenWeb']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        //Mural de avisos
        Route::get('/walls', [WallController::class, 'getAll']); 
        Route::post('/wall', [WallController::class, 'AddWall']);
        Route::put('/wall/{id}', [WallController::class, 'UpdeteWall']);
        Route::delete('/wall/{id}', [WallController::class, 'RemoveWall']);

        //Boletos
        Route::get('/billets', [BilletController::class, 'getBillets']);
        Route::post('/billet', [BilletController::class, 'AddBillet']);
        Route::put('/billet/{id}', [BilletController::class, 'UpdateBillet']);
        Route::delete('/billet/{id}', [BilletController::class, 'RemoveBillet']);

        //Documentos
        Route::get('/docs', [DocController::class, 'getAll']);
        Route::post('/doc', [DocController::class, 'AddDoc']);
        Route::post('/doc/{id}', [DocController::class, 'UpdateDoc']);
        Route::delete('/doc/{id}', [DocController::class, 'RemoveDoc']);

        //Areas
        Route::get('/areas', [ReservationController::class, 'getAreas']);
        Route::post('/area', [ReservationController::class, 'AddArea']);
        Route::post('/area/{id}', [ReservationController::class, 'UpdateArea']);
        Route::post('/area/{id}/disabled', [ReservationController::class, 'DisabledArea']);
        Route::delete('/area/{id}', [ReservationController::class, 'RemoveArea']);
        Route::get('/area/{id}/disableddates',[ReservationController::class, 'getDisabledDates']);
        Route::post('/area/{id}/disableddates',[ReservationController::class, 'setDisabledDates']);
    });
});
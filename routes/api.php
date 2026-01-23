<?php

use App\Http\Controllers\FullRentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ObjectController;
use App\Http\Controllers\RentAssigmentController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);
Route::middleware('auth:sanctum')->post('/logout',[UserController::class,'logout']);
Route::middleware('auth:sanctum')->post('/changePassword', [UserController::class, 'changePassword']);

Route::middleware('auth:sanctum')->get('/messages',[MessageController::class,'index']);
Route::middleware('auth:sanctum')->post('/messages/{receiver}',[MessageController::class,'store']);
Route::middleware('auth:sanctum')->get('/messages/{partner}',[MessageController::class,'show']);

Route::middleware('auth:sanctum')->get('/object',[ObjectController::class,'index']);
Route::middleware('auth:sanctum')->get('/object/{objects}',[ObjectController::class,'show']);
Route::middleware('auth:sanctum')->post('/object',[ObjectController::class,'store']);
Route::middleware('auth:sanctum')->put('/object/{id}',[ObjectController::class,'update']);
Route::middleware('auth:sanctum')->delete('/object/{objects}',[ObjectController::class,'destroy']);

Route::middleware('auth:sanctum')->post('/rentAssigment',[RentAssigmentController::class,'store']);
Route::middleware('auth:sanctum')->get('/rentAssigment',[RentAssigmentController::class,'index']);
Route::middleware('auth:sanctum')->get('/rentAssigment/{rentAssigment}',[RentAssigmentController::class,'show']);
Route::middleware('auth:sanctum')->put('/rentAssigment/{rentAssigment}',[RentAssigmentController::class,'update']);
Route::middleware('auth:sanctum')->delete('/rentAssigment/{rentAssigment}',[RentAssigmentController::class,'destroy']);

Route::middleware('auth:sanctum')->get('/fullRent',[FullRentController::class,'index']);
Route::middleware('auth:sanctum')->get('/fullRent/{fullRent}',[FullRentController::class,'show']);
Route::middleware('auth:sanctum')->post('/fullRent',[FullRentController::class,'store']);
Route::middleware('auth:sanctum')->put('/fullRent/{fullRent}',[FullRentController::class,'update']);
Route::middleware('auth:sanctum')->delete('/fullRent/{fullRent}',[FullRentController::class,'destroy']);
Route::middleware('auth:sanctum')->put('/fullRent/accept/{fullRent}',[FullRentController::class,'accept']);
Route::middleware('auth:sanctum')->put('fullRent/confirmPaid/{fullRent}',[FullRentController::class,'confirmPaid']);

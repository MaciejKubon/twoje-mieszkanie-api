<?php

use App\Http\Controllers\MessageController;
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

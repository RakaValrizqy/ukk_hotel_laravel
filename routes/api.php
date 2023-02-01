<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\RoomController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//user
Route::get('/user', [UserController::class, 'show']);
Route::get('/user/{id}', [UserController::class, 'detail']);
Route::post('/user', [UserController::class, 'register']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::delete('/user/{id}', [UserController::class, 'destroy']);
Route::post('/user/image/{id}', [UserController::class, 'uploadImage']);

//room type
Route::get('/roomtype', [RoomTypeController::class, 'show']);
Route::get('/roomtype/{id}', [RoomTypeController::class, 'detail']);
Route::post('/roomtype', [RoomTypeController::class, 'store']);
Route::put('/roomtype/{id}', [RoomTypeController::class, 'update']);
Route::delete('/roomtype/{id}', [RoomTypeController::class, 'destroy']);
Route::post('/roomtype/image/{id}', [RoomTypeController::class, 'uploadImage']);

//room
Route::get('/room', [RoomController::class, 'show']);
Route::post('/room', [RoomController::class, 'store']);
Route::get('/room/{id}', [RoomController::class, 'detail']);
Route::put('/room/{id}', [RoomController::class, 'update']);
Route::delete('/room/{id}', [RoomController::class, 'destroy']);
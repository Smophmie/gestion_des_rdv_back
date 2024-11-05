<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(UserController::class)->group(function () {
    Route::post('/register', 'register');
    Route::post('/login', 'login');
  });


Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', 'index');
    Route::get('/user/{id}', 'show');
    Route::post('/logout', [UserController::class, 'logout']);
});

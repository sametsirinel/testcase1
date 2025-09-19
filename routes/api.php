<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthorizationController::class, "login"]);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

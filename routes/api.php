<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthorizationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthorizationController::class, "login"]);

Route::prefix('articles')->group(function () {
    Route::get("/", [ArticleController::class,"index"]);
    Route::group([
        "prefix" => "{article}"
    ], function () {
        Route::get("/", [ArticleController::class,"show"]);

        Route::group([
            "middleware" => [
                "auth:sanctum"
            ]
        ], function () {
            Route::get("/comments", [ArticleController::class,"comments"]);
            Route::post("/comments", [ArticleController::class,"storeComments"])->middleware("userRateLimiter");
        });
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

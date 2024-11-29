<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post("/users", [UserController::class, 'register']);
Route::post("/users/login", [UserController::class, 'login']);

Route::middleware(ApiAuthMiddleware::class)->group(function () {
    Route::get("/users/current", [UserController::class, 'currentUser']);
    Route::patch("/users/current", [UserController::class, 'update']);

    Route::post("/contacts", [ContactController::class, 'store']);
    Route::get("/contacts/{contact}", [ContactController::class, 'show']);
    Route::patch("/contacts/{contact}", [ContactController::class, 'update']);
});

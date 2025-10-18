<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Organization routes
Route::middleware('auth:web')->group(function () {
    Route::post('/organization', [OrganizationController::class, 'store']);
    Route::get('/organization/check', [OrganizationController::class, 'check']);
});

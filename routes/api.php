<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\BillController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\GroupController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function(){
    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function(){
        Route::get('/', [UserController::class, 'user'])->middleware('auth:api');
        Route::post('/', [UserController::class, 'updateProfile'])->middleware('auth:api');
        Route::patch('/activegroup', [UserController::class, 'setActiveGroup'])->middleware('auth:api');
    });

    Route::group(['prefix' => 'auth'], function(){
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::resource('groups', GroupController::class)
        ->except(['edit', 'create'])
        ->middleware('auth:api');

    Route::resource('bills', BillController::class)
        ->except(['edit', 'create'])
        ->middleware('auth:api');

    Route::resource('categories', CategoryController::class)
        ->only(['index'])
        ->middleware('auth:api');
});
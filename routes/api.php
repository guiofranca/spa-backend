<?php

use App\Http\Controllers\Api\v1\AuthJwtController;
use App\Http\Controllers\Api\v1\BillController;
use App\Http\Controllers\Api\v1\CategoryController;
use App\Http\Controllers\Api\v1\GroupController;
use App\Http\Controllers\Api\v1\GroupInvitationController;
use App\Http\Controllers\Api\v1\GroupMemberController;
use App\Http\Controllers\Api\v1\RegisterController;
use App\Http\Controllers\Api\v1\SettleController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function(){
    Route::group(['prefix' => 'user', 'middleware' => 'auth:api'], function(){
        Route::get('/', [UserController::class, 'user']);
        Route::patch('/', [UserController::class, 'updateProfile']);
        Route::patch('/activegroup', [UserController::class, 'setActiveGroup']);
    });
    
    Route::group(['prefix' => 'auth'], function ($router) {    
        Route::post('login', [AuthJwtController::class, 'login'])->middleware('throttle:3,10');
        Route::post('logout', [AuthJwtController::class, 'logout']);
        Route::post('refresh', [AuthJwtController::class, 'refresh']);
        Route::get('me', [AuthJwtController::class, 'me']);
        Route::post('register', [RegisterController::class, 'register']);
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

    Route::resource('group_invitation', GroupInvitationController::class)
        ->except(['edit','create'])
        ->middleware('auth:api');
    
    Route::resource('group_members', GroupMemberController::class)
        ->only(['update', 'destroy'])
        ->middleware('auth:api');

    Route::resource('settles', SettleController::class)
        ->except(['edit','create'])
        ->middleware('auth:api');
});
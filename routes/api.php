<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\LinkController;

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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::apiResource('links', LinkController::class)
    ->middleware('auth:api');

Route::get('feed', [LinkController::class, 'feed'])
    ->middleware('auth:api');

Route::get('links/name/{name}', [LinkController::class, 'showByName'])
    ->where('name', '[a-zA-Z0-9\-\_]+')
    ->middleware('auth:api');

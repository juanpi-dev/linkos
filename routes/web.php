<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\LinkController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
});

Route::get(
    '/{name}',
    [LinkController::class, 'go']
)->where('name', '[A-Za-z0-9_\-\p{S}]+');

Route::get(
    '/{name}.{extension}',
    [LinkController::class, 'showImage']
)->where([
    'name' => '[A-Za-z0-9_\-]+',
    'extension' => '(?:jpg|jpeg|png|gif)',
]);

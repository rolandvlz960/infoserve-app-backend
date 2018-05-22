<?php

use Illuminate\Http\Request;

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

Route::post('login', 'API\UsuariosController@login');

Route::get('productos', 'API\ProductosController@index');
Route::get('productos/{id}/foto', 'API\ProductosController@foto');

Route::get('clientes', 'API\ClientesController@index');

Route::get('test', 'API\TestController@test');
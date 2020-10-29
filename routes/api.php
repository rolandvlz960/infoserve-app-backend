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
Route::get('usuarios', 'API\UsuariosController@getAll');

/*
 *
 */
Route::get('depositos', 'API\DepositosController@index');

Route::get('productos', 'API\ProductosController@index');
Route::get('productos/{id}/stocks', 'API\ProductosController@stocks');
Route::get('productos/{id}/foto', 'API\ProductosController@foto');
Route::post('productos/inc/{id}', 'API\ProductosController@incQtt');
Route::post('productos/dec/{id}', 'API\ProductosController@decQtt');
Route::get('productos-barcode', 'API\BarcodeController@index');
Route::post('productos-barcode/save', 'API\BarcodeController@save');

Route::get('clientes', 'API\ClientesController@index');
Route::get('monedas', 'API\MonedasController@index');
Route::get('cambio', 'API\CambioController@index');

Route::get('ciudades', 'API\CiudadController@ciudades');
Route::get('paises', 'API\CiudadController@paises');

Route::post('pedidos', 'API\PedidosController@send');

Route::get('gerentes', 'API\GerentesController@index');
Route::post('descuentos/open', 'API\DescuentoController@open');
Route::post('descuentos/check', 'API\DescuentoController@checkDescuento');
Route::post('descuentos/cancel', 'API\DescuentoController@dropDescuento');

Route::post('notas', 'API\NotaController@save');
Route::post('notas/reprint', 'API\NotaController@reprint');
Route::get('notas/{doc}', 'API\NotaController@findByDoc');
Route::get('notas/fotodoc/{doc}/{num}', 'API\NotaController@fotodocByDoc');

Route::get('printers', 'API\PrintersController@index');

Route::get('test', 'API\TestController@test');
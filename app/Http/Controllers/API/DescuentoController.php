<?php

namespace App\Http\Controllers\API;

use App\Cliente;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DescuentoController extends Controller
{
    public function requestDescuento(Request $request)
    {
        $tableName = 'D050'
            . DB::select('select CONNECTION_ID() as conn')[0]->conn
            . 00
            . rand(10000000, 99999999);
        Schema::table($tableName, function (Blueprint $table) {
            $table->double('linha', 10, 0)->nullable()->default(null);
            $table->double('cliente', 6, 0)->nullable()->default(null);
            $table->char('clinome', 40)->nullable()->default(null);
            $table->double('clisaldo', 13, 2)->nullable()->default(null);
            $table->double('clilimite', 13, 2)->nullable()->default(null);
            $table->double('clivendep', 13, 2)->nullable()->default(null);
            $table->double('clivence', 13, 2)->nullable()->default(null);
            $table->double('totalvenda', 13, 2)->nullable()->default(null);
            $table->double('totallucro', 13, 2)->nullable()->default(null);
            $table->double('totalmarge', 13, 2)->nullable()->default(null);
            $table->double('totalquant', 13, 2)->nullable()->default(null);
            $table->double('produto', 6, 0)->nullable()->default(null);
            $table->char('descricao', 40)->nullable()->default(null);
            $table->double('quantidade', 10, 0)->nullable()->default(null);
            $table->double('preco', 13, 2)->nullable()->default(null);
            $table->double('total', 13, 2)->nullable()->default(null);
            $table->double('minimo', 13, 2)->nullable()->default(null);
            $table->double('antpreco', 13, 2)->nullable()->default(null);
            $table->double('desconto', 13, 2)->nullable()->default(null);
            $table->double('percdesc', 13, 2)->nullable()->default(null);
            $table->char('nivel', 1)->nullable()->default(null);
            $table->double('custo', 19, 2)->nullable()->default(null);
            $table->double('lucro', 19, 2)->nullable()->default(null);
            $table->double('margem', 19, 2)->nullable()->default(null);
            $table->char('status', 10)->nullable()->default(null);
            $table->char('subitem', 1)->nullable()->default(null);
            $table->char('obs', 30)->nullable()->default(null);
            $table->char('altera', 1)->nullable()->default(null);
            $table->char('pede', 1)->nullable()->default(null);
            $table->double('nota', 7, 0)->nullable()->default(null);
            $table->double('operacao', 2, 0)->nullable()->default(null);
            $table->char('gerentcons', 10)->nullable()->default(null);
            $table->double('iva', 13, 2)->nullable()->default(null);
            $table->double('totaliva', 13, 2)->nullable()->default(null);
            $table->char('taxado', 1)->nullable()->default(null);
            $table->char('clienivel', 2)->nullable()->default(null);
            $table->double('preco_c', 13, 2)->nullable()->default(null);
            $table->char('ip', 50)->nullable()->default(null);
            $table->bigInteger('sr_recno', true)->unique();
            $table->char('sr_deleted', 1);
        });

        $cliente = Cliente::where('numero', '=', $request->idCliente)->first();

        DB::table($tableName)->insert([
            'linha' => 1,
            'cliente' => $request->idCliente,
            'cliente' => $cliente->nome,
        ]);
    }
}

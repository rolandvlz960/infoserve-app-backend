<?php

namespace App\Http\Controllers\API;

use App\Cliente;
use App\Producto;
use App\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DescuentoController extends Controller
{
    public function open(Request $request)
    {
        $producto = Producto::where('produto', '=', $request->idProducto)->first();
        $gerente = Usuario::where('numero', '=', $request->idGerente)->first();

        if ($gerente->AUTABXCOST !== 'S' && ($gerente->AUTABXCOST === 'C' && ($request->precoSolicitado < $producto->CUSTOCIF))) {
            return [
                'error' => 401
            ];
        }

        if ($gerente->PROIVAABCU === 'N' && ($request->precoSolicitado < ($producto->TAXADO_A/1.1))) {
            return [
                'error' => 401
            ];
        }

        $pid = DB::select('select CONNECTION_ID() as conn')[0]->conn;
        $tableName = 'd050'
            . $pid
            . '00'
            . rand(10000000, 99999999);
        Schema::create($tableName, function (Blueprint $table) {
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
            $table->char('sr_deleted', 1)->nullable()->default(null);
        });

        $cliente = Cliente::where('cliente', '=', $request->idCliente)->first();

        $valorProd = $request->precoSolicitado * $request->quantidadeProducto;
        $custoProd = $producto->CUSTOCIF * $request->quantidadeProducto;
        DB::table($tableName)->insert([
            'linha' => 1,
            'cliente' => $request->idCliente,
            'clinome' => $cliente->NOME,
            'totalvenda' => $request->precoSolicitado * $request->quantidadeProducto,
            'totallucro' =>
                $valorProd - $custoProd,
            'totalmarge' =>
                ($valorProd - $custoProd) / $custoProd * 100,
            'totalquant' => $request->quantidadeProducto,
            'produto' => $producto->DIGITO,
            'descricao' => substr($producto->DESCRICAO, 0, 40),
            'quantidade' => $request->quantidadeProducto,
            'preco' => $request->precoSolicitado,
            'total' => $request->precoSolicitado * $request->quantidadeProducto,
            'minimo' => $producto->PRECO_C,
            'antpreco' => $request->precoSolicitado,
            'nivel' => $cliente->NIVELPRECO,
            'custo' => $producto->CUSTOCIF,
            'lucro' =>
                $valorProd - $custoProd,
            'margem' =>
                $valorProd - $custoProd,
            'status' => '*auto*',
            'altera' => 'S',
            'pede' => 0,
            'taxado' => $cliente->CLI_PESSOA === 2 ? 'T' : 'N',
            'clienivel' => $cliente->NIVELPRECO,
            'preco_c' => $producto->PRECO_C,
        ]);

        DB::table('fil580')->insert([
            'numero' => $request->idVendedor,
            'usuario' => $request->idCliente,
            'pid' => $pid,
            'hora' => DB::raw('time_format(NOW(), "%H:%i:%s")'),
            'ngerente' => $request->idGerente,
            'nomegerent' => $gerente->NOME,
            'status' => 1,
            'arquivo' => $tableName,
            'cliente' => 0,
            'saldo' => 0,
            'limite' => 0,
            'vencido' => 0,
            'vendaspend' => 0,
            'condpag' => 'V',
            'totvenda' => 0,
            'totcusto' => 0,
            'totlucro' => 0,
            'totperc' => 0,
            'tipo' => 1,
            'descnota' => 0,
            'operacao' => 1,
            'desconto' => '',
            'nomecli' => '',
            'nivelpreco' => '',
            'gerentcons' => '',
            'vendedor' => $request->idVendedor,
            'sr_deleted' => '',
        ]);

        return [
            'table' => $tableName
        ];
    }

    public function checkDescuento(Request $request)
    {
        $arquivo = DB::table($request->tableName)->first();
        $autorizacion = DB::table('fil580')
            ->where('arquivo','=', $request->tableName)
            ->first();
        $status = $autorizacion->STATUS;
        $preco = $arquivo->preco;

        if ($status == 2) {
            $this->dropDescuento($request);
        }

        return [
            'status' => $status,
            'preco' => $preco
        ];
    }

    public function dropDescuento(Request $request)
    {
        DB::table('fil580')
            ->where('arquivo','=', $request->tableName)
            ->update([
                'sr_deleted' => 'T'
            ]);
        Schema::dropIfExists($request->tableName);
        return [
            'status' => 'ok'
        ];
    }
}

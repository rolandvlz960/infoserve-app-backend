<?php

namespace App\Jobs;

use App\Moneda;
use App\Producto;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Posprint\Connectors\Network;
use Posprint\Printers\Bematech;

class PrintNota implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $printerIp;

    private $printerPort;

    private $nota;

    private $fecha;

    private $receivedItems;

    private $cliente;

    private $usuario;

    private $deposito;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        $printerIp,
        $printerPort,
        $nota,
        $fecha,
        $receivedItems,
        $cliente,
        $usuario,
        $deposito
    )
    {
        $this->printerIp = $printerIp;
        $this->printerPort = $printerPort;
        $this->nota = $nota;
        $this->fecha = $fecha;
        $this->receivedItems = $receivedItems;
        $this->cliente = $cliente;
        $this->usuario = $usuario;
        $this->deposito = $deposito;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $connector = new Network($this->printerIp, $this->printerPort);
            $printer = new Bematech($connector);
            $total = 0;
            $items = [];
            $cant = 0;
            $mensagens = DB::table('FIL050')->select('MENSAGEM_1', 'MENSAGEM_2', 'NTRESTABLET')->first();
            if ($mensagens->NTRESTABLET == 'S') {
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("Numero: " . $this->nota . "\n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->close();
            } else {
                $sigla = Moneda::first()->SIGLA;
                $datetime = Carbon::createFromFormat("Y-m-d H:i", $this->fecha);
                $mensagens = DB::table('FIL050')->select('MENSAGEM_1', 'MENSAGEM_2')->first();
                foreach ($this->receivedItems as $item) {
                    $res = Producto::select('digito', 'descricao')->where('produto', $item['producto'])->first();
                    $items[] = [
                        $res->digito . "    " . $res->descricao,
                        $item['cantidad'] . ' x ' . $sigla . " " . number_format($item['precio'], 2) . "    " . $sigla . ' ' . number_format($item['precio'] * $item['cantidad'], 2)
                    ];
                    $cant += $item['cantidad'];
                    $total = $total + ($item['precio'] * $item['cantidad']);
                }
                $printer->text("------------------------------------------\n");
                $printer->text("VENTAS\n");
                $printer->text("PEDIDO DE VENTAS\n");
                $printer->text("Fecha de emision: " . $datetime->format('d/m/Y H:i') . "\n");
                $printer->text("Cliente: " . $this->cliente . "\n");
                $printer->text("Usuario: " . $this->usuario->numero . "-" . $this->usuario->nome . "\n");
                $printer->text("Numero: " . $this->nota . "\n");
                $printer->text("Deposito: " . $this->deposito . "\n");
                $printer->text("==========================================\n");
                $printer->text("Codigo    Descrip.\n");
                $printer->text("Cant    Precio    Total\n");
                foreach ($items as $item) {
                    $printer->text($item[0] . "\n");
                    $printer->text($item[1] . "\n");
                }
                $printer->text("------------------------------------------\n");
                $printer->text("Total: " . $sigla . " " . number_format($total, 2) . "    Items: " . $cant . "\n");
                $printer->text("------------------------------------------\n");
                $printer->text("Total: " . $sigla . " " . number_format($total, 2) . "\n");
                $printer->text("Desc: 0\n");
                $printer->text("Total: " . $sigla . " " . number_format($total, 2) . "\n");
                $printer->text("==========================================\n");
                $printer->text($mensagens->MENSAGEM_1 . "\n");
                $printer->text($mensagens->MENSAGEM_2 . "\n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $printer->text("                       \n");
                $this->cut($printer);
                $printer->send();
                $printer->close();
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function cut(Bematech $printer)
    {
        $printer->lineFeed(2);
        $printer->cut();
    }
}

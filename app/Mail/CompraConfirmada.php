<?php

namespace App\Mail;

use App\Models\VentaEntrada;
use App\Models\Entrada;
use App\Models\Evento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompraConfirmada extends Mailable
{
    use Queueable, SerializesModels;

    public $ventaEntrada;
    public $entrada;
    public $evento;
    public $user;
    public $factura;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\VentaEntrada  $ventaEntrada
     * @return void
     */
    public function __construct(VentaEntrada $ventaEntrada)
    {
        $this->ventaEntrada = $ventaEntrada;
        $this->entrada = $ventaEntrada->entrada;
        $this->evento = $ventaEntrada->entrada->evento;
        $this->user = $ventaEntrada->participante->user;
        $this->factura = null;
        
        // Intentar obtener la factura si existe
        $factura = \App\Models\Factura::where('idEntrada', $ventaEntrada->idEntrada)
            ->where('idParticipante', $ventaEntrada->idParticipante)
            ->first();
            
        if ($factura) {
            $this->factura = $factura;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Confirmación de compra: ' . $this->evento->nombreEvento)
                    ->view('emails.compra-confirmada');
    }
} 
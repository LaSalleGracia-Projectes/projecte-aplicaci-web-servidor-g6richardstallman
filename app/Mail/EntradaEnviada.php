<?php

namespace App\Mail;

use App\Models\VentaEntrada;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EntradaEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $ventaEntrada;
    public $pdfContent;
    public $nombreArchivo;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\VentaEntrada  $ventaEntrada
     * @param  string  $pdfContent
     * @param  string  $nombreArchivo
     * @return void
     */
    public function __construct(VentaEntrada $ventaEntrada, $pdfContent, $nombreArchivo)
    {
        $this->ventaEntrada = $ventaEntrada;
        $this->pdfContent = $pdfContent;
        $this->nombreArchivo = $nombreArchivo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tu entrada para ' . $this->ventaEntrada->entrada->evento->nombreEvento)
                    ->view('emails.entrada-enviada')
                    ->attachData($this->pdfContent, $this->nombreArchivo, [
                        'mime' => 'application/pdf',
                    ]);
    }
} 
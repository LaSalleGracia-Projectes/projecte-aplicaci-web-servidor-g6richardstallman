<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\VentaEntrada;
use Illuminate\Http\Request;
use PDF;

class PdfController extends Controller
{
    public function generarFacturaPdf($idFactura)
    {
        $factura = Factura::with(['participante.user', 'entrada.evento'])->findOrFail($idFactura);
        
        $pdf = PDF::loadView('pdfs.factura', ['factura' => $factura]);
        
        return $pdf->download('factura-' . $factura->numero_factura . '.pdf');
    }
    
    public function generarEntradaPdf($idEntrada)
    {
        $venta = VentaEntrada::with(['entrada.evento', 'entrada.tipoEntrada', 'participante.user'])
            ->where('idEntrada', $idEntrada)
            ->firstOrFail();
            
        $pdf = PDF::loadView('pdfs.entrada', ['venta' => $venta]);
        
        return $pdf->download('entrada-' . $venta->entrada->codigo . '.pdf');
    }
}
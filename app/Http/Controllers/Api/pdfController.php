<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Models\VentaEntrada;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    public function generarFacturaPdf($idFactura)
    {
        try {
            // Obtener el usuario autenticado
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Debe iniciar sesión para descargar la factura',
                    'status' => 'error'
                ], 401);
            }

            // Buscar la factura con sus relaciones
            $factura = Factura::with(['participante.user', 'entrada.evento'])
                ->findOrFail($idFactura);

            // Verificar que la factura pertenece al usuario autenticado
            if ($factura->participante->idUser !== $user->idUser) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'No tiene permiso para descargar esta factura',
                    'status' => 'error'
                ], 403);
            }

            // Generar el PDF
            $pdf = PDF::loadView('pdfs.factura', ['factura' => $factura]);

            // Configurar el nombre del archivo
            $nombreArchivo = 'factura-' . $factura->numero_factura . '.pdf';

            // Devolver el PDF para descarga
            return $pdf->download($nombreArchivo);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Factura no encontrada: ' . $e->getMessage());
            return response()->json([
                'error' => 'Factura no encontrada',
                'message' => 'No se encontró la factura solicitada',
                'status' => 'error'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de factura: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al generar PDF',
                'message' => 'Ocurrió un error al generar el PDF de la factura',
                'status' => 'error'
            ], 500);
        }
    }
    
    public function generarEntradaPdf($idEntrada)
    {
        try {
            // Obtener el usuario autenticado
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Debe iniciar sesión para descargar la entrada',
                    'status' => 'error'
                ], 401);
            }

            // Buscar la venta con sus relaciones
            $venta = VentaEntrada::with(['entrada.evento', 'entrada.tipoEntrada', 'participante.user'])
                ->where('idEntrada', $idEntrada)
                ->firstOrFail();

            // Verificar que la entrada pertenece al usuario autenticado
            if ($venta->idParticipante !== $user->participante->idParticipante) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'No tiene permiso para descargar esta entrada',
                    'status' => 'error'
                ], 403);
            }

            // Generar el PDF
            $pdf = PDF::loadView('pdfs.entrada', ['venta' => $venta]);

            // Configurar el nombre del archivo
            $nombreArchivo = 'entrada-' . $venta->entrada->codigo . '.pdf';

            // Devolver el PDF para descarga
            return $pdf->download($nombreArchivo);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Entrada no encontrada: ' . $e->getMessage());
            return response()->json([
                'error' => 'Entrada no encontrada',
                'message' => 'No se encontró la entrada solicitada',
                'status' => 'error'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al generar PDF de entrada: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al generar PDF',
                'message' => 'Ocurrió un error al generar el PDF de la entrada',
                'status' => 'error'
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Participante;
use App\Models\Evento;
use App\Models\TipoEntrada;
use App\Models\VentaEntrada;
use App\Models\Entrada;
use App\Models\User;
use App\Models\Factura;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VentaEntradaController extends Controller
{
    /**
     * Realizar la compra de entradas para un evento
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comprar(Request $request)
    {
        // Iniciar log
        Log::info('Iniciando proceso de compra', ['request' => $request->except('token')]);

        try {
            // Modificar la validación para que datos_facturacion sea opcional
            $validated = $request->validate([
                'idEvento' => 'required|exists:evento,idEvento',
                'entradas' => 'required|array|min:1',
                'entradas.*.idTipoEntrada' => 'required|exists:tipo_entrada,idTipoEntrada',
                'entradas.*.cantidad' => 'required|integer|min:1',
                'entradas.*.precio' => 'required|numeric|min:0',
                'emitir_factura' => 'sometimes|boolean',
                'metodo_pago' => 'sometimes|string'
            ]);

            // Obtener el usuario autenticado
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Debe iniciar sesión para realizar una compra',
                    'status' => 'error'
                ], 401);
            }

            // Verificar si el usuario es un participante
            if ($user->role !== 'participante') {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'Solo los participantes pueden comprar entradas',
                    'status' => 'error'
                ], 403);
            }

            // Obtener el participante y sus datos
            $participante = Participante::where('idUser', $user->idUser)
                ->with('user') // Cargar los datos del usuario
                ->first();
            
            if (!$participante) {
                return response()->json([
                    'error' => 'Perfil incompleto',
                    'message' => 'No se encontró el perfil de participante asociado a su cuenta',
                    'status' => 'error'
                ], 404);
            }
            
            // Usar los datos del usuario automáticamente
            $nombreComprador = $user->nombre . ' ' . $user->apellido1;
            $emailComprador = $user->email;

            // Comprobar que el evento existe y no ha pasado
            $evento = Evento::findOrFail($validated['idEvento']);
            $fechaEvento = Carbon::parse($evento->fechaEvento . ' ' . $evento->hora);
            
            if ($fechaEvento->isPast()) {
                return response()->json([
                    'error' => 'Evento no disponible',
                    'message' => 'No se pueden comprar entradas para un evento que ya ha pasado',
                    'status' => 'error'
                ], 400);
            }

            // Iniciar transacción DB
            DB::beginTransaction();
            
            try {
                // Calcular el total y verificar disponibilidad de entradas
                $total = 0;
                $entradasCompradas = [];
                
                foreach ($validated['entradas'] as $entradaData) {
                    // Obtener el tipo de entrada
                    $tipoEntrada = TipoEntrada::findOrFail($entradaData['idTipoEntrada']);
                    
                    // Verificar que pertenece al evento solicitado
                    if ($tipoEntrada->idEvento != $validated['idEvento']) {
                        throw new \Exception('El tipo de entrada no pertenece al evento seleccionado');
                    }
                    
                    // Verificar disponibilidad
                    if (!$tipoEntrada->es_ilimitado && 
                        ($tipoEntrada->entradas_vendidas + $entradaData['cantidad'] > $tipoEntrada->cantidad_disponible)) {
                        throw new \Exception("No hay suficientes entradas disponibles de tipo '{$tipoEntrada->nombre}'");
                    }
                    
                    // Verificar que el precio coincide (seguridad)
                    if (abs($tipoEntrada->precio - $entradaData['precio']) > 0.01) {
                        throw new \Exception('El precio de la entrada ha cambiado. Por favor, actualice la página e intente de nuevo.');
                    }
                    
                    // Actualizar el contador de entradas vendidas
                    $tipoEntrada->entradas_vendidas += $entradaData['cantidad'];
                    $tipoEntrada->save();
                    
                    // Calcular subtotal de este tipo de entrada
                    $subtotal = $tipoEntrada->precio * $entradaData['cantidad'];
                    $total += $subtotal;
                    
                    // Crear las entradas individuales
                    for ($i = 0; $i < $entradaData['cantidad']; $i++) {
                        $entrada = new Entrada();
                        $entrada->fecha_venta = Carbon::now();
                        $entrada->nombre_persona = $nombreComprador;
                        $entrada->idEvento = $validated['idEvento'];
                        $entrada->save();
                        
                        // Guardar relación entre entrada y participante
                        $ventaEntrada = new VentaEntrada();
                        $ventaEntrada->idEntrada = $entrada->idEntrada;
                        $ventaEntrada->idParticipante = $participante->idParticipante;
                        $ventaEntrada->fecha_compra = Carbon::now();
                        $ventaEntrada->estado_pago = 'Pendiente';
                        
                        // Asignar precio y calcular automáticamente impuestos
                        $ventaEntrada->setPrecioAndCalculateValues($tipoEntrada->precio);
                        $ventaEntrada->save();
                        
                        $entradasCompradas[] = [
                            'idEntrada' => $entrada->idEntrada,
                            'idVentaEntrada' => $ventaEntrada->idVentaEntrada,
                            'tipo' => $tipoEntrada->nombre,
                            'precio' => $tipoEntrada->precio,
                            'impuestos' => $ventaEntrada->impuestos
                        ];
                    }
                }
                
                // Si se solicita factura, usar datos del participante
                $facturaId = null;
                if (isset($validated['emitir_factura']) && $validated['emitir_factura']) {
                    // Crear o buscar registro de pago
                    $pago = Pago::firstOrCreate(
                        ['email' => $emailComprador],
                        [
                            'nombre' => $nombreComprador,
                            'contacto' => $nombreComprador,
                            'telefono' => $participante->telefono ?? '',
                            'email' => $emailComprador
                        ]
                    );
                    
                    // Calcular valores para la factura basados en el total de la compra
                    $subtotalFactura = round($total / (1 + VentaEntrada::IVA), 2);
                    $impuestosFactura = round($total - $subtotalFactura, 2);
                    
                    // Crear factura con datos del participante
                    $factura = new Factura();
                    $factura->numero_factura = Factura::generarNumeroFactura();
                    $factura->fecha_emision = now()->format('Y-m-d');
                    $factura->fecha_vencimiento = now()->addDays(30)->format('Y-m-d'); // 30 días para pagar
                    $factura->subtotal = $subtotalFactura;
                    $factura->impostos = $impuestosFactura;
                    $factura->descuento = 0; // Sin descuento
                    $factura->montoTotal = $total;
                    $factura->estado = 'emitida';
                    
                    // Usar datos del usuario/participante para la facturación
                    $factura->nombre_fiscal = $nombreComprador;
                    $factura->nif = $participante->dni ?? 'No especificado';
                    $factura->direccion_fiscal = $participante->direccion ?? 'No especificada';
                    $factura->metodo_pago = $validated['metodo_pago'] ?? 'tarjeta';
                    $factura->notas = "Factura por compra de entradas para el evento: {$evento->nombreEvento}";
                    $factura->idParticipante = $participante->idParticipante;
                    $factura->idEntrada = count($entradasCompradas) > 0 ? $entradasCompradas[0]['idEntrada'] : null;
                    $factura->idPago = $pago->idPago;
                    $factura->save();
                    
                    $facturaId = $factura->idFactura;
                }
                
                // Simular procesamiento de pago
                $pagoExitoso = $this->procesarPago($total, ['nombre' => $nombreComprador, 'email' => $emailComprador]);
                
                if (!$pagoExitoso) {
                    throw new \Exception('Error al procesar el pago. Por favor, intente con otro método de pago.');
                }
                
                // Actualizar estado de las ventas a 'completado'
                foreach ($entradasCompradas as $entrada) {
                    $ventaEntrada = VentaEntrada::find($entrada['idVentaEntrada']);
                    $ventaEntrada->estado_pago = 'Pagado';
                    $ventaEntrada->save();
                }
                
                // Confirmar transacción
                DB::commit();
                
                // Preparar respuesta
                $respuesta = [
                    'message' => 'Compra realizada con éxito',
                    'total' => $total,
                    'entradas' => $entradasCompradas,
                    'evento' => [
                        'id' => $evento->idEvento,
                        'nombre' => $evento->nombreEvento,
                        'fecha' => $evento->fechaEvento,
                        'hora' => $evento->hora
                    ],
                    'comprador' => [
                        'id' => $participante->idParticipante,
                        'nombre' => $nombreComprador,
                        'email' => $emailComprador
                    ],
                    'status' => 'success'
                ];
                
                // Si se emitió factura, incluir detalles simplificados
                if ($facturaId) {
                    $factura = Factura::findOrFail($facturaId);
                    $respuesta['factura'] = [
                        'id' => $factura->idFactura,
                        'numero' => $factura->numero_factura,
                        'fecha_emision' => $factura->fecha_emision,
                        'subtotal' => $factura->subtotal,
                        'impuestos' => $factura->impostos,
                        'total' => $factura->montoTotal,
                        'estado' => $factura->estado,
                        'datos_fiscales' => [
                            'nombre' => $factura->nombre_fiscal,
                            'nif' => $factura->nif,
                            'direccion' => $factura->direccion_fiscal
                        ],
                        'metodo_pago' => $factura->metodo_pago
                    ];
                }
                
                return response()->json($respuesta, 201);
                
            } catch (\Exception $e) {
                // Revertir transacción en caso de error
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en compra: ' . json_encode($e->errors()));
            return response()->json([
                'error' => 'Error de validación',
                'messages' => $e->errors(),
                'status' => 'error'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en proceso de compra: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al procesar la compra',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
    
    /**
     * Simula el procesamiento de un pago
     * En una implementación real, esto conectaría con una pasarela de pago
     *
     * @param float $total
     * @param array $datosComprador
     * @return bool
     */
    private function procesarPago($total, $datosComprador)
    {
        // En un entorno de producción, aquí iría la lógica para conectar con la pasarela de pago
        // Para este ejemplo, simplemente simulamos un pago exitoso
        
        Log::info('Procesando pago simulado', [
            'total' => $total,
            'comprador' => $datosComprador['nombre'] ?? 'Usuario'
        ]);
        
        // Simulamos un 95% de éxito en los pagos
        return (mt_rand(1, 100) <= 95);
    }
    
    /**
     * Listar compras del usuario autenticado
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listarCompras(Request $request)
    {
        try {
            // Obtener el participante asociado al usuario
            $user = $request->user();
            $participante = Participante::where('idUser', $user->idUser)->first();
            
            if (!$participante) {
                return response()->json([
                    'error' => 'Perfil no encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }
            
            // Obtener todas las compras del participante
            $compras = VentaEntrada::where('idParticipante', $participante->idParticipante)
                ->with(['entrada', 'entrada.evento'])
                ->orderBy('fecha_compra', 'desc')
                ->get();
            
            // Agrupar compras por evento para mejor presentación
            $comprasAgrupadas = [];
            
            foreach ($compras as $compra) {
                $idEvento = $compra->entrada->idEvento;
                
                if (!isset($comprasAgrupadas[$idEvento])) {
                    $evento = $compra->entrada->evento;
                    
                    $comprasAgrupadas[$idEvento] = [
                        'evento' => [
                            'id' => $evento->idEvento,
                            'nombre' => $evento->nombreEvento,
                            'fecha' => $evento->fechaEvento,
                            'hora' => $evento->hora,
                            'imagen' => $evento->imagen
                        ],
                        'entradas' => [],
                        'total' => 0,
                        'fecha_compra' => $compra->fecha_compra
                    ];
                }
                
                $comprasAgrupadas[$idEvento]['entradas'][] = [
                    'id' => $compra->idEntrada,
                    'precio' => $compra->precio,
                    'estado' => $compra->estado_pago,
                    'nombre_persona' => $compra->entrada->nombre_persona
                ];
                
                $comprasAgrupadas[$idEvento]['total'] += $compra->precio;
            }
            
            return response()->json([
                'message' => 'Compras obtenidas con éxito',
                'compras' => array_values($comprasAgrupadas),
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al listar compras: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener las compras',
                'message' => 'No se pudieron recuperar sus compras',
                'status' => 'error'
            ], 500);
        }
    }
} 
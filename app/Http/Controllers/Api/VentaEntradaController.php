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
use App\Mail\CompraConfirmada;
use Illuminate\Support\Facades\Mail;

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
                        $entrada->idTipoEntrada = $entradaData['idTipoEntrada']; 
                        $entrada->estado = 'disponible'; 
                        $entrada->precio = $tipoEntrada->precio;
                        
                        // Generar un código único
                        $codigo = 'ENT-' . $validated['idEvento'] . '-' . $entradaData['idTipoEntrada'] . '-' . uniqid(microtime(), true);
                        
                        // Verificar si existe algún código igual en la base de datos
                        while (Entrada::where('codigo', $codigo)->exists()) {
                            $codigo = 'ENT-' . $validated['idEvento'] . '-' . $entradaData['idTipoEntrada'] . '-' . uniqid(microtime(), true);
                        }
                        
                        $entrada->codigo = $codigo;
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
                
                // Crear o buscar registro de pago (esto debe ir FUERA del bloque if)
                $pago = Pago::firstOrCreate(
                    ['email' => $emailComprador],
                    [
                        'nombre' => $nombreComprador,
                        'contacto' => $nombreComprador,
                        'telefono' => $participante->telefono ?? '',
                        'email' => $emailComprador
                    ]
                );
                
                // Luego el bloque condicional para la factura
                $facturaId = null;
                if (isset($validated['emitir_factura']) && $validated['emitir_factura']) {
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
                foreach ($entradasCompradas as $entradaData) {
                    $ventaEntrada = VentaEntrada::find($entradaData['idVentaEntrada']);
                    $ventaEntrada->estado_pago = 'Pagado';
                    $ventaEntrada->idPago = $pago->idPago;
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
                
                // Enviar correo de confirmación para cada entrada comprada
                foreach ($entradasCompradas as $entrada) {
                    $ventaEntrada = VentaEntrada::with(['entrada.evento', 'participante.user'])
                        ->find($entrada['idVentaEntrada']);
                    
                    if ($ventaEntrada) {
                        try {
                            Mail::to($emailComprador)->send(new CompraConfirmada($ventaEntrada));
                        } catch (\Exception $e) {
                            // Loguear el error pero continuar con el proceso
                            Log::error('Error al enviar correo de confirmación: ' . $e->getMessage());
                        }
                    }
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
        
        // Simulamos un pago exitoso (100% de éxito para pruebas)
        return true;
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
                $fechaCompra = $compra->fecha_compra;
                
                // Formatear la fecha solo si es un objeto Carbon, de lo contrario usar como está
                if ($fechaCompra instanceof \Carbon\Carbon) {
                    $fechaCompraStr = $fechaCompra->format('Y-m-d H:i:s');
                } else {
                    $fechaCompraStr = $fechaCompra;
                }
                
                // Usar una clave compuesta de idEvento + fecha_compra para agrupar por evento y fecha
                $claveAgrupacion = $idEvento . '_' . $fechaCompraStr;
                
                if (!isset($comprasAgrupadas[$claveAgrupacion])) {
                    $evento = $compra->entrada->evento;
                    
                    $comprasAgrupadas[$claveAgrupacion] = [
                        'evento' => [
                            'id' => $evento->idEvento,
                            'nombre' => $evento->nombreEvento,
                            'fecha' => $evento->fechaEvento,
                            'hora' => $evento->hora,
                            'imagen' => $evento->imagen
                        ],
                        'entradas' => [],
                        'total' => 0,
                        'fecha_compra' => $fechaCompraStr,
                        'id_compra' => $compra->idVentaEntrada
                    ];
                }
                
                $comprasAgrupadas[$claveAgrupacion]['entradas'][] = [
                    'id' => $compra->idEntrada,
                    'precio' => $compra->precio,
                    'estado' => $compra->estado_pago,
                    'nombre_persona' => $compra->entrada->nombre_persona,
                    'tipo_entrada' => 'General' // Por defecto, ya que no hay relación con tipo_entrada
                ];
                
                $comprasAgrupadas[$claveAgrupacion]['total'] += $compra->precio;
            }
            
            // Convertir a array indexado numéricamente
            $resultado = array_values($comprasAgrupadas);
            
            // Ordenar por fecha de compra (descendente)
            usort($resultado, function($a, $b) {
                return strtotime($b['fecha_compra']) - strtotime($a['fecha_compra']);
            });
            
            return response()->json([
                'message' => 'Compras obtenidas con éxito',
                'compras' => $resultado,
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al listar compras: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al obtener las compras',
                'message' => 'No se pudieron recuperar sus compras: ' . $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Ver detalle completo de una compra específica
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detalleCompra($id, Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Debe iniciar sesión para ver los detalles de la compra',
                    'status' => 'error'
                ], 401);
            }
            
            // Obtener el participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            if (!$participante) {
                return response()->json([
                    'error' => 'Perfil no encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }
            
            // Buscar la venta por ID
            $ventaEntrada = VentaEntrada::where('idVentaEntrada', $id)
                ->with(['entrada.evento', 'pago'])
                ->first();
                
            if (!$ventaEntrada) {
                return response()->json([
                    'error' => 'Compra no encontrada',
                    'message' => 'No se encontró la compra solicitada',
                    'status' => 'error'
                ], 404);
            }
            
            // Verificar que la compra pertenece al usuario autenticado
            if ($ventaEntrada->idParticipante != $participante->idParticipante) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'No tiene permiso para ver los detalles de esta compra',
                    'status' => 'error'
                ], 403);
            }
            
            // Buscar la factura asociada, si existe
            $factura = Factura::where('idEntrada', $ventaEntrada->idEntrada)
                ->where('idParticipante', $participante->idParticipante)
                ->first();
            
            // Preparar los datos del evento
            $evento = $ventaEntrada->entrada->evento;
            $eventoData = [
                'id' => $evento->idEvento,
                'nombre' => $evento->nombreEvento,
                'descripcion' => $evento->descripcionEvento,
                'fecha' => $evento->fechaEvento,
                'hora' => $evento->hora,
                'direccion' => $evento->direccion,
                'imagen' => $evento->imagen,
                'organizador' => [
                    'id' => $evento->idOrganizador,
                    'nombre' => $evento->organizador->user->nombre ?? 'No disponible'
                ]
            ];
            
            // Preparar los datos de la entrada
            $entradaData = [
                'id' => $ventaEntrada->idEntrada,
                'nombre_persona' => $ventaEntrada->entrada->nombre_persona,
                'precio' => $ventaEntrada->precio,
                'impuestos' => $ventaEntrada->impuestos,
                'total' => $ventaEntrada->precio + $ventaEntrada->impuestos,
                'estado_pago' => $ventaEntrada->estado_pago,
                'fecha_compra' => $ventaEntrada->fecha_compra instanceof \Carbon\Carbon 
                    ? $ventaEntrada->fecha_compra->format('Y-m-d H:i:s') 
                    : $ventaEntrada->fecha_compra
            ];
            
            // Preparar respuesta
            $respuesta = [
                'id_compra' => $ventaEntrada->idVentaEntrada,
                'estado' => $ventaEntrada->estado_pago,
                'fecha_compra' => $ventaEntrada->fecha_compra instanceof \Carbon\Carbon 
                    ? $ventaEntrada->fecha_compra->format('Y-m-d H:i:s') 
                    : $ventaEntrada->fecha_compra,
                'entrada' => $entradaData,
                'evento' => $eventoData,
                'comprador' => [
                    'id' => $participante->idParticipante,
                    'nombre' => $user->nombre . ' ' . $user->apellido1,
                    'email' => $user->email,
                    'telefono' => $participante->telefono ?? 'No disponible'
                ],
                'pago' => [
                    'metodo' => $ventaEntrada->pago->metodo_pago ?? 'No especificado',
                    'fecha' => $ventaEntrada->fecha_compra instanceof \Carbon\Carbon 
                        ? $ventaEntrada->fecha_compra->format('Y-m-d H:i:s') 
                        : $ventaEntrada->fecha_compra,
                    'estado' => $ventaEntrada->estado_pago
                ]
            ];
            
            // Agregar información de factura si existe
            if ($factura) {
                $respuesta['factura'] = [
                    'id' => $factura->idFactura,
                    'numero' => $factura->numero_factura,
                    'fecha_emision' => $factura->fecha_emision instanceof \Carbon\Carbon 
                        ? $factura->fecha_emision->format('Y-m-d') 
                        : $factura->fecha_emision,
                    'fecha_vencimiento' => $factura->fecha_vencimiento instanceof \Carbon\Carbon 
                        ? $factura->fecha_vencimiento->format('Y-m-d') 
                        : $factura->fecha_vencimiento,
                    'subtotal' => $factura->subtotal,
                    'impuestos' => $factura->impostos,
                    'descuento' => $factura->descuento,
                    'total' => $factura->montoTotal,
                    'estado' => $factura->estado,
                    'datos_fiscales' => [
                        'nombre' => $factura->nombre_fiscal,
                        'nif' => $factura->nif,
                        'direccion' => $factura->direccion_fiscal
                    ],
                    'metodo_pago' => $factura->metodo_pago,
                    'notas' => $factura->notas
                ];
            }
            
            return response()->json([
                'message' => 'Detalle de compra obtenido con éxito',
                'compra' => $respuesta,
                'status' => 'success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener detalle de compra: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al obtener detalle de compra',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Generar factura para una compra específica
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generarFactura($id, Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'error' => 'No autorizado',
                    'message' => 'Debe iniciar sesión para generar una factura',
                    'status' => 'error'
                ], 401);
            }
            
            // Obtener el participante
            $participante = Participante::where('idUser', $user->idUser)->first();
            if (!$participante) {
                return response()->json([
                    'error' => 'Perfil no encontrado',
                    'message' => 'No se encontró el perfil de participante',
                    'status' => 'error'
                ], 404);
            }
            
            // Buscar la venta por ID
            $ventaEntrada = VentaEntrada::where('idVentaEntrada', $id)
                ->with(['entrada.evento', 'pago'])
                ->first();
                
            if (!$ventaEntrada) {
                return response()->json([
                    'error' => 'Compra no encontrada',
                    'message' => 'No se encontró la compra solicitada',
                    'status' => 'error'
                ], 404);
            }
            
            // Verificar que la compra pertenece al usuario autenticado
            if ($ventaEntrada->idParticipante != $participante->idParticipante) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'message' => 'No tiene permiso para generar la factura de esta compra',
                    'status' => 'error'
                ], 403);
            }
            
            // Verificar si ya existe una factura para esta compra
            $facturaExistente = Factura::where('idEntrada', $ventaEntrada->idEntrada)
                ->where('idParticipante', $participante->idParticipante)
                ->first();
            
            if ($facturaExistente) {
                // Si ya existe una factura, devolverla
                $facturaData = [
                    'id' => $facturaExistente->idFactura,
                    'numero' => $facturaExistente->numero_factura,
                    'fecha_emision' => $facturaExistente->fecha_emision instanceof \Carbon\Carbon 
                        ? $facturaExistente->fecha_emision->format('Y-m-d') 
                        : $facturaExistente->fecha_emision,
                    'fecha_vencimiento' => $facturaExistente->fecha_vencimiento instanceof \Carbon\Carbon 
                        ? $facturaExistente->fecha_vencimiento->format('Y-m-d') 
                        : $facturaExistente->fecha_vencimiento,
                    'subtotal' => $facturaExistente->subtotal,
                    'impuestos' => $facturaExistente->impostos,
                    'descuento' => $facturaExistente->descuento,
                    'total' => $facturaExistente->montoTotal,
                    'estado' => $facturaExistente->estado,
                    'datos_fiscales' => [
                        'nombre' => $facturaExistente->nombre_fiscal,
                        'nif' => $facturaExistente->nif,
                        'direccion' => $facturaExistente->direccion_fiscal
                    ],
                    'metodo_pago' => $facturaExistente->metodo_pago,
                    'notas' => $facturaExistente->notas
                ];
                
                return response()->json([
                    'message' => 'Factura ya existente',
                    'factura' => $facturaData,
                    'status' => 'success'
                ]);
            }
            
            // Crear o buscar registro de pago
            $pago = Pago::firstOrCreate(
                ['email' => $user->email],
                [
                    'nombre' => $user->nombre . ' ' . $user->apellido1,
                    'contacto' => $user->nombre . ' ' . $user->apellido1,
                    'telefono' => $participante->telefono ?? '',
                    'email' => $user->email
                ]
            );
            
            // Calcular valores para la factura
            $precio = $ventaEntrada->precio;
            $impuestos = $ventaEntrada->impuestos;
            $subtotal = round($precio - $impuestos, 2);
            
            // Crear factura
            $factura = new Factura();
            $factura->numero_factura = Factura::generarNumeroFactura();
            $factura->fecha_emision = now()->format('Y-m-d');
            $factura->fecha_vencimiento = now()->addDays(30)->format('Y-m-d');
            $factura->subtotal = $subtotal;
            $factura->impostos = $impuestos;
            $factura->descuento = 0; // Sin descuento
            $factura->montoTotal = $precio;
            $factura->estado = 'emitida';
            
            // Usar datos del usuario/participante para la facturación
            $factura->nombre_fiscal = $user->nombre . ' ' . $user->apellido1;
            $factura->nif = $participante->dni ?? 'No especificado';
            $factura->direccion_fiscal = $participante->direccion ?? 'No especificada';
            $factura->metodo_pago = $ventaEntrada->pago->metodo_pago ?? 'tarjeta';
            $factura->notas = "Factura por compra de entrada para el evento: {$ventaEntrada->entrada->evento->nombreEvento}";
            $factura->idParticipante = $participante->idParticipante;
            $factura->idEntrada = $ventaEntrada->idEntrada;
            $factura->idPago = $pago->idPago;
            $factura->save();
            
            // Preparar respuesta con datos de la factura
            $facturaData = [
                'id' => $factura->idFactura,
                'numero' => $factura->numero_factura,
                'fecha_emision' => $factura->fecha_emision,
                'fecha_vencimiento' => $factura->fecha_vencimiento,
                'subtotal' => $factura->subtotal,
                'impuestos' => $factura->impostos,
                'descuento' => $factura->descuento,
                'total' => $factura->montoTotal,
                'estado' => $factura->estado,
                'datos_fiscales' => [
                    'nombre' => $factura->nombre_fiscal,
                    'nif' => $factura->nif,
                    'direccion' => $factura->direccion_fiscal
                ],
                'metodo_pago' => $factura->metodo_pago,
                'notas' => $factura->notas
            ];
            
            return response()->json([
                'message' => 'Factura generada con éxito',
                'factura' => $facturaData,
                'status' => 'success'
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error al generar factura: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al generar factura',
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }
} 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'factura';
    protected $primaryKey = 'idFactura';
    public $timestamps = true;

    protected $fillable = [
        'numero_factura',
        'fecha_emision',
        'fecha_vencimiento',
        'montoTotal',
        'descuento',
        'impostos',
        'subtotal',
        'estado',
        'nombre_fiscal',
        'nif',
        'direccion_fiscal',
        'metodo_pago',
        'notas',
        'idParticipante',
        'idEntrada',
        'idPago'
    ];

    /**
     * Genera un número de factura único en formato YYYY-XXXXX
     * donde YYYY es el año actual y XXXXX es un número secuencial
     *
     * @return string
     */
    public static function generarNumeroFactura(): string
{
    $anio = Carbon::now()->format('Y');
    $numeroGenerado = null;
    
    // Usar una transacción para evitar condiciones de carrera
    DB::beginTransaction();
    
    try {
        // Bloquear la tabla para evitar lecturas sucias
        $ultimaFactura = DB::table('factura')
            ->where('numero_factura', 'like', "$anio-%")
            ->orderBy('numero_factura', 'desc')
            ->lockForUpdate()
            ->first();
        
        if ($ultimaFactura) {
            // Extraer el número de la última factura y sumar 1
            $partes = explode('-', $ultimaFactura->numero_factura);
            
            // Verificar si es puramente numérico
            if (is_numeric($partes[1])) {
                $ultimoNumero = (int) $partes[1];
                $nuevoNumero = $ultimoNumero + 1;
            } else {
                // Si contiene caracteres no numéricos, empezar desde 1
                $nuevoNumero = 1;
            }
        } else {
            $nuevoNumero = 1;
        }
        
        // Formatear el nuevo número de factura (puramente numérico)
        $numeroGenerado = $anio . '-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
        
        // Comprobar si ya existe una factura con ese número
        $facturaExistente = DB::table('factura')
            ->where('numero_factura', $numeroGenerado)
            ->exists();
        
        // Si existe, añadir un identificador aleatorio
        if ($facturaExistente) {
            $randomId = substr(md5(uniqid(mt_rand(), true)), 0, 4);
            $numeroGenerado = $anio . '-' . str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT) . '-' . $randomId;
        }
        
        DB::commit();
        return $numeroGenerado; // Retornar aquí si todo salió bien
    } catch (\Exception $e) {
        DB::rollBack();
        // Generar un número de factura único con timestamp para evitar errores
        $timestamp = Carbon::now()->format('YmdHis');
        $random = substr(md5(uniqid()), 0, 5);
        return $anio . '-' . $timestamp . '-' . $random;
    }
}

    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class, 'idParticipante');
    }

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class, 'idEntrada');
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'idPago');
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
}
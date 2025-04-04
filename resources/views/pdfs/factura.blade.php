<!-- resources/views/pdfs/factura.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $factura->numero_factura }}</title>
    <style>
        /* Estilos CSS para la factura */
        body { font-family: 'Helvetica', sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .total { margin-top: 30px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURA</h1>
        <h2>#{{ $factura->numero_factura }}</h2>
    </div>
    
    <div class="company-info">
        <p><strong>Fecha emisión:</strong> {{ $factura->fecha_emision }}</p>
        <p><strong>Cliente:</strong> {{ $factura->nombre_fiscal }}</p>
        <p><strong>NIF/CIF:</strong> {{ $factura->nif }}</p>
        <p><strong>Dirección:</strong> {{ $factura->direccion_fiscal }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Entrada para "{{ $factura->entrada->evento->nombreEvento }}"</td>
                <td>1</td>
                <td>{{ number_format($factura->subtotal, 2) }}€</td>
                <td>{{ number_format($factura->subtotal, 2) }}€</td>
            </tr>
        </tbody>
    </table>
    
    <div class="total">
        <p><strong>Subtotal:</strong> {{ number_format($factura->subtotal, 2) }}€</p>
        <p><strong>IVA (21%):</strong> {{ number_format($factura->impostos, 2) }}€</p>
        <p><strong>TOTAL:</strong> {{ number_format($factura->montoTotal, 2) }}€</p>
    </div>
</body>
</html>
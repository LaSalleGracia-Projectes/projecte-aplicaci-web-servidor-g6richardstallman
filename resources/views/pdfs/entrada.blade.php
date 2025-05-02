<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Entrada #{{ $venta->entrada->codigo }}</title>
    <style>
        body { 
            font-family: 'Helvetica', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            color: #666;
            margin: 5px 0 0;
            font-size: 18px;
        }
        .event-info { 
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .event-info p {
            margin: 5px 0;
        }
        .ticket-details {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .ticket-details p {
            margin: 5px 0;
        }
        .codigo-entrada {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border: 2px dashed #652c2d;
            border-radius: 5px;
        }
        .codigo-entrada h3 {
            color: #652c2d;
            margin: 0 0 10px 0;
        }
        .codigo-entrada p {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }
        .qr-code {
            margin: 20px auto;
            width: 200px;
            height: 200px;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .label {
            font-weight: bold;
            color: #333;
        }
        .warning {
            color: #a53435;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            background-color: #fff3f3;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ENTRADA</h1>
        <h2>#{{ $venta->entrada->codigo }}</h2>
    </div>
    
    <div class="event-info">
        <h3>{{ $venta->entrada->evento->nombreEvento }}</h3>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($venta->entrada->evento->fechaEvento)->format('d/m/Y') }}</p>
        <p><strong>Hora:</strong> {{ $venta->entrada->evento->hora }}</p>
        <p><strong>Lugar:</strong> {{ $venta->entrada->evento->direccion }}</p>
    </div>
    
    <div class="ticket-details">
        <h3>Detalles de la Entrada</h3>
        <p><strong>Tipo de Entrada:</strong> {{ $venta->entrada->tipoEntrada->nombre }}</p>
        <p><strong>Nombre del Asistente:</strong> {{ $venta->entrada->nombre_persona }}</p>
        <p><strong>Fecha de Compra:</strong> {{ \Carbon\Carbon::parse($venta->fecha_compra)->format('d/m/Y H:i') }}</p>
        <p><strong>Precio:</strong> {{ number_format($venta->precio, 2) }}€</p>
    </div>

    <div class="codigo-entrada">
        <h3>Código de Entrada</h3>
        <div class="qr-code">
            {!! QrCode::size(200)->generate($venta->entrada->codigo) !!}
        </div>
        <p>{{ $venta->entrada->codigo }}</p>
    </div>

    <div class="warning">
        <p>IMPORTANTE: Esta entrada es personal e intransferible. Debe presentarse junto con un documento de identidad válido.</p>
    </div>

    <div class="footer">
        <p>Esta entrada ha sido generada automáticamente por el sistema de gestión de eventos.</p>
        <p>Para cualquier consulta, por favor contacte con el servicio de atención al cliente.</p>
    </div>
</body>
</html> 
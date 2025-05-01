<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tu entrada para {{ $ventaEntrada->entrada->evento->nombreEvento }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #252525;
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
        }
        .header {
            background-color: #652c2d;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .content {
            padding: 20px;
            background-color: white;
        }
        .ticket-info {
            background-color: #dbd9d6;
            border: 1px solid #dbd9d6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .event-title {
            color: #652c2d;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .event-details p {
            margin: 5px 0;
        }
        .ticket-details {
            border-bottom: 1px solid #dbd9d6;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .cta-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #a53435;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .reminder {
            margin-top: 20px;
            font-style: italic;
            color: #252525;
        }
        .label {
            font-weight: bold;
            color: #652c2d;
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
        <h1>¡Tu entrada está lista!</h1>
    </div>
    
    <div class="content">
        <p>Hola {{ $ventaEntrada->entrada->nombre_persona }},</p>
        
        <p>Tu entrada para el evento ha sido generada correctamente. Encontrarás el archivo PDF adjunto a este correo.</p>
        
        <div class="event-title">{{ $ventaEntrada->entrada->evento->nombreEvento }}</div>
        
        <div class="event-details">
            <p><span class="label">Fecha:</span> {{ \Carbon\Carbon::parse($ventaEntrada->entrada->evento->fechaEvento)->format('d/m/Y') }}</p>
            <p><span class="label">Hora:</span> {{ $ventaEntrada->entrada->evento->hora }}</p>
            <p><span class="label">Lugar:</span> {{ $ventaEntrada->entrada->evento->direccion }}</p>
        </div>
        
        <div class="ticket-info">
            <h3>Información de la Entrada</h3>
            
            <div class="ticket-details">
                <p><span class="label">Código de Entrada:</span> #{{ $ventaEntrada->entrada->codigo }}</p>
                <p><span class="label">Tipo de Entrada:</span> {{ $ventaEntrada->entrada->tipoEntrada->nombre }}</p>
                <p><span class="label">Fecha de Compra:</span> {{ \Carbon\Carbon::parse($ventaEntrada->fecha_compra)->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        
        <div class="warning">
            <p>IMPORTANTE: Esta entrada es personal e intransferible. Debe presentarse junto con un documento de identidad válido.</p>
        </div>
        
        <p class="reminder">Recuerda llevar tu entrada (digital o impresa) y un documento de identidad al evento.</p>
        
        <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>
        
        <p>¡Esperamos verte en el evento!</p>
    </div>
</body>
</html> 
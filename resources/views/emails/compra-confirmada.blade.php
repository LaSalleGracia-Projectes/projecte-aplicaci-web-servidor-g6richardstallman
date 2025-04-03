<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmación de Compra</title>
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
        .price-details {
            border-top: 1px solid #dbd9d6;
            padding-top: 15px;
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
        .estado-pagado {
            color: green;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Compra Confirmada!</h1>
    </div>
    
    <div class="content">
        <p>Hola Yago,</p>
        
        <p>Gracias por tu compra. A continuación encontrarás los detalles de tu entrada para el evento:</p>
        
        <div class="event-title">Festival de Música Electrónica</div>
        
        <div class="event-details">
            <p><span class="label">Fecha:</span> 15/08/2025</p>
            <p><span class="label">Hora:</span> 22:00:00</p>
            <p><span class="label">Lugar:</span></p>
        </div>
        
        <div class="ticket-info">
            <h3>Información de la Entrada</h3>
            
            <div class="ticket-details">
                <p><span class="label">Código de Entrada:</span> #49</p>
                <p><span class="label">Nombre:</span> Yago Alonso</p>
                <p><span class="label">Fecha de Compra:</span> 03/04/2025 11:21</p>
            </div>
            
            <div class="price-details">
                <p><span class="label">Precio:</span> 45.00 €</p>
                <p><span class="label">IVA (21%):</span> 9.45 €</p>
                <p><span class="label">Total:</span> 54.45 €</p>
                <p><span class="label">Estado:</span> <span class="estado-pagado">Pagado</span></p>
            </div>
        </div>
        
        <p class="reminder">Recuerda llevar tu entrada (digital o impresa) y un documento de identidad al evento.</p>
        
        <a href="#" class="cta-button">Ver mis Compras</a>
    </div>
</body>
</html>
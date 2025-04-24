<!DOCTYPE html>
<html>

<head>
    <title>Bienvenido a Eventflix</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 480px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 32px 28px 24px 28px;
        }

        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #e53c3d;
            letter-spacing: 2px;
            margin-bottom: 18px;
        }

        .divider {
            border: none;
            border-top: 1px solid #ececec;
            margin: 18px 0 24px 0;
        }

        .welcome {
            font-size: 22px;
            color: #222;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .message {
            font-size: 16px;
            color: #444;
            text-align: center;
            margin-bottom: 18px;
        }

        .info {
            background: #f8f8f8;
            border-radius: 6px;
            padding: 16px;
            font-size: 15px;
            color: #555;
            margin-bottom: 18px;
            text-align: center;
        }

        .footer {
            font-size: 12px;
            color: #888;
            text-align: center;
            margin-top: 30px;
        }

        .footer a {
            color: #e53c3d;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="text-align:center; margin-bottom:18px;">
            <img src="{{ asset('logo.jpg') }}" alt="Eventflix Logo" style="height:70px; border-radius:8px; box-shadow:0 2px 8px rgba(229,60,61,0.12);">
        </div>
        <hr class="divider">
        <div class="welcome" style="color:#e53c3d;">¡Bienvenido a Eventflix!</div>
        <div class="message" style="font-size:17px; color:#333; margin-bottom:22px;">
            Hola <span style="font-weight:600;">{{ $user->nombre }}</span>,<br><br>
            ¡Tu cuenta ha sido creada con éxito!<br>
            Ya eres parte de la comunidad que vive los mejores <span style="color:#e53c3d; font-weight:600;">eventos presenciales</span>.<br><br>
            <span style="color:#e53c3d; font-weight:600;">Crea, gestiona y compra entradas</span> para eventos de <span style="font-weight:600;">música</span>, <span style="font-weight:600;">gastronomía</span>, <span style="font-weight:600;">ocio</span> y mucho más. ¡Descubre experiencias únicas y comparte momentos inolvidables!
        </div>
        <div class="info" style="background:linear-gradient(90deg,#ffe5e5 0%,#fff7e5 100%); color:#e53c3d; font-size:16px; font-weight:500;">
            🎟️ Explora eventos, reserva tus entradas y disfruta de los mejores planes.<br>
            🎤 ¿Tienes un evento? ¡Publícalo y llega a más personas con Eventflix!
        </div>
        <div class="footer">
            Este correo fue enviado a {{ $user->email }}<br>
            &copy; {{ date('Y') }} Eventflix. Todos los derechos reservados.<br>
            <span style="font-size:10px; color:#bbb;">Si no has creado una cuenta en Eventflix, puedes ignorar este mensaje.</span>
        </div>
    </div>
</body>

</html>
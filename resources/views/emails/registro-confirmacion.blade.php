<!DOCTYPE html>
<html>
<head>
    <title>¡Bienvenido a Evenflix!</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #E53935;
        }
        .button {
            display: inline-block;
            background-color: #E53935;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #777777;
            text-align: center;
        }
        .emoji {
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">EVENFLIX</div>
    </div>

    <p><span class="emoji">👋</span> Hola {{ $user->nombre }},</p>
    
    <p>¡Gracias por registrarte en Evenflix! <span class="emoji">🎉</span> Estamos encantados de tenerte con nosotros. Ahora puedes acceder a los mejores eventos en streaming y disfrutar de una experiencia única.</p>
    
    <p>Para completar tu registro y comenzar a explorar todo lo que Evenflix tiene para ofrecer, haz clic en el siguiente botón:</p>
    
    <div style="text-align: center;">
        <a href="{{ url('/') }}" class="button"><span class="emoji">🔹</span> Confirmar mi cuenta</a>
    </div>
    
    <p>Si no has creado una cuenta en Evenflix, ignora este mensaje.</p>
    
    <p><span class="emoji">📩</span> Si tienes alguna pregunta, nuestro equipo de soporte está aquí para ayudarte: <a href="mailto:soporte@evenflix.com">soporte@evenflix.com</a></p>
    
    <p>¡Nos vemos en Evenflix!</p>
    
    <p>— El equipo de Evenflix</p>
    
    <div class="footer">
        <p>Este correo fue enviado a {{ $user->email }}</p>
        <p>&copy; {{ date('Y') }} Evenflix. Todos los derechos reservados.</p>
    </div>
</body>
</html> 
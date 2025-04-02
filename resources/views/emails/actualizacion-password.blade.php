<!DOCTYPE html>
<html>
<head>
    <title>Tu contraseña ha sido actualizada - Eventflix</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #252525;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #e53c3d, #a53435);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(229, 60, 61, 0.3);
        }
        .logo {
            font-size: 36px;
            font-weight: bold;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            letter-spacing: 3px;
        }
        .welcome-text {
            font-size: 26px;
            color: #652c2d;
            text-align: center;
            margin: 30px 0;
            font-weight: bold;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
        }
        .button {
            display: inline-block;
            background: linear-gradient(to right, #e53c3d, #a53435);
            color: #ffffff !important;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-weight: bold;
            margin: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(229, 60, 61, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid transparent;
        }
        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(229, 60, 61, 0.4);
            border: 2px solid #ffffff;
        }
        .content {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin: 20px 0;
        }
        .feature-box {
            background-color: #fff8f8;
            border-left: 5px solid #e53c3d;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 12px 12px 0;
            box-shadow: 0 2px 10px rgba(229, 60, 61, 0.1);
        }
        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: #652c2d;
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #f8e5e5;
        }
        .emoji {
            font-size: 24px;
            margin: 0 5px;
            vertical-align: middle;
        }
        .social-links {
            margin: 25px 0;
            text-align: center;
            padding: 15px;
            background: #fff8f8;
            border-radius: 12px;
        }
        .social-links a {
            color: #e53c3d;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .social-links a:hover {
            color: #a53435;
        }
        .password-box {
            background: linear-gradient(to right, #fff8f8, #fff);
            border: 2px solid #e53c3d;
            padding: 20px;
            margin: 25px 0;
            border-radius: 12px;
            text-align: center;
            font-family: monospace;
            font-size: 20px;
            letter-spacing: 2px;
            box-shadow: 0 4px 15px rgba(229, 60, 61, 0.1);
        }
        ul {
            list-style: none;
            padding-left: 0;
        }
        ul li {
            margin: 12px 0;
            padding-left: 30px;
            position: relative;
        }
        ul li .emoji {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">EVENTFLIX</div>
        </div>

        <div class="welcome-text">
            Tu contraseña ha sido actualizada <span class="emoji">🔐</span>
        </div>

        <div class="content">
            <p><span class="emoji">👋</span> ¡Hola {{ $user->nombre }}!</p>
            
            <div class="feature-box">
                <p>Hemos actualizado tu contraseña en Eventflix como solicitaste. A continuación encontrarás tu nueva contraseña:</p>
            </div>
            
            <div class="password-box">
                <strong>{{ $newPassword }}</strong>
            </div>
            
            <p>Por razones de seguridad, te recomendamos cambiar esta contraseña la próxima vez que inicies sesión.</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/') }}" class="button">
                    <span class="emoji">🔑</span> Iniciar sesión <span class="emoji">🔑</span>
                </a>
            </div>

            <div class="feature-box">
                <p><strong>Recuerda:</strong></p>
                <ul>
                    <li><span class="emoji">🔒</span> Mantén tu contraseña en un lugar seguro</li>
                    <li><span class="emoji">🔄</span> Cámbiala periódicamente</li>
                    <li><span class="emoji">❌</span> No la compartas con nadie</li>
                    <li><span class="emoji">⚠️</span> Si no solicitaste este cambio, contacta con nosotros inmediatamente</li>
                </ul>
            </div>
        </div>

        <div class="social-links">
            <p>Síguenos en nuestras redes sociales:</p>
            <a href="#">Instagram</a> |
            <a href="#">Twitter</a> |
            <a href="#">Facebook</a>
        </div>

        <div class="footer">
            <p>Si necesitas ayuda, estamos aquí para ti: <a href="mailto:eventflix.app@gmail.com" style="color: #e53c3d;">eventflix.app@gmail.com</a></p>
            <p>Este correo fue enviado a {{ $user->email }}</p>
            <p>&copy; {{ date('Y') }} Eventflix. Todos los derechos reservados.</p>
            <p style="font-size: 11px; color: #777777;">Si no solicitaste este cambio de contraseña, por favor contacta con nosotros inmediatamente.</p>
        </div>
    </div>
</body>
</html> 
<!DOCTYPE html>
<html>
<head>
    <title>Tu contraseña ha sido actualizada - Evenflix</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #252525;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #dbd9d6;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: linear-gradient(135deg, #e53c3d, #a53435);
            border-radius: 8px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            letter-spacing: 2px;
        }
        .welcome-text {
            font-size: 24px;
            color: #652c2d;
            text-align: center;
            margin: 30px 0;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background: linear-gradient(to right, #e53c3d, #a53435);
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
            transition: transform 0.2s;
            box-shadow: 0 4px 6px rgba(229, 60, 61, 0.2);
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(229, 60, 61, 0.3);
        }
        .content {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .feature-box {
            background-color: #f8f8f8;
            border-left: 4px solid #e53c3d;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #652c2d;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dbd9d6;
        }
        .emoji {
            font-size: 24px;
            margin: 0 5px;
            vertical-align: middle;
        }
        .social-links {
            margin: 20px 0;
            text-align: center;
        }
        .social-links a {
            color: #e53c3d;
            margin: 0 10px;
            text-decoration: none;
        }
        .password-box {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
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
                    <li>🔒 Mantén tu contraseña en un lugar seguro</li>
                    <li>🔄 Cámbiala periódicamente</li>
                    <li>❌ No la compartas con nadie</li>
                    <li>⚠️ Si no solicitaste este cambio, contacta con nosotros inmediatamente</li>
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
            <p style="font-size: 10px; color: #777777;">Si no solicitaste este cambio de contraseña, por favor contacta con nosotros inmediatamente.</p>
        </div>
    </div>
</body>
</html> 
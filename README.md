# 🎫 Sistema de Gestión de Eventos - Backend API

## 📋 Descripción General
Este es el backend de una aplicación completa de gestión de eventos y venta de entradas, desarrollada con Laravel 10. Proporciona una API RESTful robusta y segura que permite la gestión integral de eventos, procesamiento de pagos, generación de documentación y gestión de usuarios.

### 🎯 Objetivo del Proyecto
Crear una plataforma completa que permita:
- A los organizadores gestionar sus eventos de manera eficiente
- A los participantes comprar entradas de forma segura
- Automatizar la generación y envío de documentación
- Proporcionar una experiencia de usuario fluida y segura

## 🚀 Características Detalladas

### 👥 Gestión de Usuarios
- Sistema de roles (Organizador, Participante, Administrador)
- Registro y autenticación seguros
- Perfiles personalizables
- Recuperación de contraseña
- Autenticación con Google (OAuth2)

### 📅 Gestión de Eventos
- CRUD completo de eventos
- Gestión de tipos de entradas
- Control de aforo
- Gestión de fechas y horarios
- Ubicación con integración de mapas
- Sistema de categorías y etiquetas
- Búsqueda y filtrado avanzado

### 🎟️ Sistema de Entradas
- Múltiples tipos de entradas por evento
- Control de stock en tiempo real
- Generación de códigos únicos
- Códigos QR para validación
- Sistema anti-reventa
- Cancelaciones y reembolsos

### 💰 Sistema de Pagos
- Integración con pasarelas de pago
- Gestión de transacciones
- Sistema de reembolsos
- Registro de histórico de pagos
- Facturación automática

### 📄 Generación de Documentos
- Entradas en PDF personalizadas
- Facturas según normativa
- Códigos QR únicos
- Envío automático por email
- Validación en tiempo real

### 📧 Sistema de Notificaciones
- Emails transaccionales
- Confirmaciones de compra
- Recordatorios de eventos
- Notificaciones de cambios
- Alertas de sistema

## 💻 Tecnologías y Herramientas

### 🛠️ Core
- PHP 8.1
- Laravel 10.x
- MySQL/MariaDB
- Redis (caché y colas)
- Nginx/Apache

### 📚 Principales Paquetes
```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "laravel/sanctum": "^3.2",
        "barryvdh/laravel-dompdf": "^2.0",
        "simplesoftwareio/simple-qrcode": "^4.2",
        "guzzlehttp/guzzle": "^7.5",
        "predis/predis": "^2.0"
    }
}
```

## 🏗️ Arquitectura del Sistema

### 📂 Estructura de Directorios
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   └── Auth/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
├── Repositories/
├── Mail/
├── Events/
├── Listeners/
└── Jobs/
```

### 🔄 Flujo de Datos
1. Request HTTP → Middleware
2. Middleware → Controller
3. Controller → Service
4. Service → Repository
5. Repository → Model
6. Response → Cliente

## 📦 Instalación y Configuración

### 📋 Requisitos Previos Detallados
- PHP >= 8.1
  - Extensiones: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- Composer 2.x
- MySQL/MariaDB >= 8.0
- Redis (opcional, para caché)
- Servidor web (Nginx/Apache)
- SSL para producción

### 🔧 Proceso de Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/tuorganizacion/eventos-backend.git
cd eventos-backend
```

2. Instalar dependencias:
```bash
composer install --optimize-autoloader --no-dev
```

3. Configuración del entorno:
```bash
cp .env.example .env
php artisan key:generate
php artisan storage:link
```

4. Configuración detallada del .env:
```env
# Configuración de la aplicación
APP_NAME="Sistema de Eventos"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventos_db
DB_USERNAME=usuario
DB_PASSWORD=contraseña

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Correo
MAIL_MAILER=smtp
MAIL_HOST=smtp.tuservidor.com
MAIL_PORT=587
MAIL_USERNAME=tu@email.com
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"

# JWT y Seguridad
JWT_SECRET=tu_jwt_secret
JWT_TTL=60
SANCTUM_STATEFUL_DOMAINS=tu-dominio.com
```

5. Preparar la base de datos:
```bash
php artisan migrate --seed --force
```

6. Optimizar:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📚 API Documentation

### 🔐 Autenticación
Todas las rutas (excepto login/registro) requieren token Bearer:
```http
Authorization: Bearer <tu_token>
```

### 📝 Ejemplos de Endpoints

#### Autenticación
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "usuario@email.com",
    "password": "contraseña"
}
```

#### Crear Evento
```http
POST /api/eventos
Content-Type: application/json
Authorization: Bearer <token>

{
    "nombre": "Mi Evento",
    "descripcion": "Descripción del evento",
    "fecha": "2024-12-31",
    "hora": "20:00",
    "ubicacion": "Dirección del evento",
    "aforo_maximo": 100
}
```

## 🔐 Seguridad

### 🛡️ Medidas Implementadas
- Autenticación JWT con Sanctum
- Protección CSRF
- Rate Limiting
- Validación de entrada
- Sanitización de datos
- Logs de seguridad
- Encriptación de datos sensibles

### ⚠️ Consideraciones de Seguridad
- Todas las contraseñas se hashean
- Datos sensibles encriptados
- Sesiones seguras
- Headers de seguridad configurados
- Protección contra inyección SQL
- Validación de archivos subidos

## 🧪 Testing

### 🔍 Tipos de Tests
```bash
# Ejecutar todos los tests
php artisan test

# Tests unitarios
php artisan test --testsuite=Unit

# Tests de integración
php artisan test --testsuite=Feature

# Tests específicos
php artisan test --filter=EventTest
```

## 👥 Equipo de Desarrollo

### 🧑‍💻 Desarrolladores Principales
- **Yago Alonso**
  - Rol: Lead Backend Developer
  - Responsabilidades: Arquitectura, API, Seguridad
  - GitHub: [YagoAlonso](https://github.com/YagoAlonso)

- **Arnau Gil**
  - Rol: Backend Developer
  - Responsabilidades: Testing, Integración, Base de datos
  - GitHub: [ArnauGil](https://github.com/ArnauGil)

- **Alex Vilanova**
  - Rol: Backend Developer
  - Responsabilidades: Documentación, Seguridad, API
  - GitHub: [AlexVilanova](https://github.com/AlexVilanova)

## 📈 Estado del Proyecto y Roadmap

### 🎯 Versión Actual
- Versión: 1.0.0
- Estado: En desarrollo activo
- Última actualización: Marzo 2024

### 🛣️ Próximas Características
- [ ] Integración con más proveedores de pago
- [ ] Sistema de eventos recurrentes
- [ ] API para aplicación móvil
- [ ] Panel de administración mejorado
- [ ] Sistema de análisis y estadísticas

## 📞 Soporte y Contacto

### 🆘 Soporte Técnico
- Email: soporte@tudominio.com
- Horario: Lunes a Viernes, 9:00 - 18:00 (CET)
- Issues: GitHub Issues

### 📱 Redes Sociales
- Twitter: [@EventosApp](https://twitter.com/EventosApp)
- LinkedIn: [EventosApp](https://linkedin.com/company/EventosApp)

## 📄 Licencia y Términos

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE.md](LICENSE.md) para más detalles.

---
Desarrollado con ❤️ por el equipo de EventosApp

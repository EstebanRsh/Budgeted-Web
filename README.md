# Presupuestador

Sistema web completo para la gestión de presupuestos, clientes y productos. Desarrollado en PHP nativo con arquitectura MVC, permite a las empresas crear, editar, duplicar y exportar presupuestos de forma profesional.

## Características principales

### Gestión de Presupuestos
- ✅ Crear presupuestos con múltiples ítems
- ✅ Editar y duplicar presupuestos existentes
- ✅ Exportación a PDF y Excel
- ✅ Vista de impresión optimizada
- ✅ Búsqueda y filtrado avanzado
- ✅ Numeración automática consecutiva
- ✅ Cálculo automático de totales
- ✅ Validez configurable por presupuesto

### Gestión de Clientes
- ✅ ABM completo de clientes
- ✅ Datos fiscales (CUIT/DNI, condición IVA)
- ✅ Información de contacto
- ✅ Validación de CUIT/DNI argentino
- ✅ Búsqueda en tiempo real

### Catálogo de Productos
- ✅ Gestión de productos y servicios
- ✅ Actualización rápida de precios
- ✅ Creación automática desde presupuestos
- ✅ Descripción y código de producto

### Panel de Administración
- ✅ Gestión de usuarios y empresas
- ✅ Aprobación de registros
- ✅ Activación/desactivación de cuentas
- ✅ Logs de correo electrónico
- ✅ Auditoría de acciones

### Características Técnicas
- ✅ Autenticación con protección CSRF
- ✅ Rate limiting en login
- ✅ Validaciones del lado del cliente y servidor
- ✅ Modo oscuro/claro
- ✅ Diseño responsive (Bootstrap 5)
- ✅ Búsqueda instantánea con HTMX
- ✅ Paginación eficiente
- ✅ Subida de logos de empresa

## Requisitos del sistema

- **PHP:** 8.0 o superior
- **MySQL:** 5.7 o superior / MariaDB 10.3+
- **Extensiones PHP requeridas:**
  - PDO MySQL
  - mbstring
  - GD (para manejo de imágenes)
  - fileinfo
- **Composer:** Para gestión de dependencias
- **Servidor web:** Apache 2.4+ (con mod_rewrite) o Nginx

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/presupuestador.git
cd presupuestador
```

### 2. Instalar dependencias

```bash
composer install
```

Esto instalará las siguientes librerías:
- **PHPMailer** - Envío de correos electrónicos
- **Dompdf** - Generación de PDFs
- **PhpSpreadsheet** - Exportación a Excel
- **ZipStream** - Creación de archivos ZIP

### 3. Configurar base de datos

Crear la base de datos MySQL:

```bash
mysql -u root -p
CREATE DATABASE presupuestos_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit
```

Importar el schema:

```bash
mysql -u root -p presupuestos_app < presupuestos_app.sql
```

### 4. Configurar variables de entorno

Copiar el archivo de ejemplo y configurar:

```bash
cp .env.example .env
```

Editar el archivo `.env` con tus credenciales:

```ini
# Configuración de base de datos
DB_HOST=localhost
DB_NAME=presupuestos_app
DB_USER=root
DB_PASS=tu_contraseña
DB_CHARSET=utf8mb4

# Configuración de correo (opcional)
MAIL_ENABLED=true
SMTP_HOST=smtp.tuservidor.com
SMTP_PORT=587
SMTP_USER=tu_email@dominio.com
SMTP_PASSWORD=tu_contraseña_smtp
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=noreply@tudominio.com
SMTP_FROM_NAME=Presupuestador
```

### 5. Configurar permisos

```bash
chmod 755 public/uploads
chmod 755 public/uploads/logos
chmod 755 logs
```

### 6. Configurar el servidor web

#### Apache (.htaccess ya incluido)

Asegurarse de que `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

El DocumentRoot debe apuntar a la carpeta `public/`:

```apache
<VirtualHost *:80>
    ServerName presupuestador.local
    DocumentRoot /ruta/al/proyecto/presupuestador/public

    <Directory /ruta/al/proyecto/presupuestador/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name presupuestador.local;
    root /ruta/al/proyecto/presupuestador/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 7. Acceder al sistema

Abrir el navegador en: `http://localhost/presupuestador` o tu dominio configurado.

## Credenciales de prueba

El archivo SQL incluye un usuario superadministrador para comenzar:

```
Email: admin@presupuestador.com
Contraseña: admin123
```

**⚠️ IMPORTANTE:** Cambiar esta contraseña inmediatamente en producción.

## Estructura del proyecto

```
presupuestador/
├── app/
│   ├── controllers/       # Controladores MVC
│   ├── models/           # Modelos de datos
│   ├── views/            # Vistas (HTML + PHP)
│   ├── helpers/          # Funciones auxiliares
│   ├── services/         # Servicios (Email, etc.)
│   └── scripts/          # Scripts de mantenimiento
├── config/               # Configuración de la app
├── logs/                 # Logs de la aplicación
├── public/               # DocumentRoot (acceso público)
│   ├── assets/          # CSS, JS, imágenes
│   └── uploads/         # Archivos subidos
├── vendor/              # Dependencias de Composer
├── .env                 # Configuración de entorno
├── .gitignore
├── composer.json
└── presupuestos_app.sql # Schema de base de datos
```

## Uso del sistema

### Flujo de trabajo típico

1. **Registrar empresa:** Los usuarios se registran y esperan aprobación del admin
2. **Configurar empresa:** Cargar logo, datos fiscales, información de contacto
3. **Cargar clientes:** Agregar clientes con sus datos fiscales
4. **Crear productos:** Definir catálogo de productos/servicios
5. **Generar presupuestos:** Crear presupuestos asociando cliente + productos
6. **Exportar:** Descargar en PDF o Excel, o imprimir directamente

### Roles de usuario

- **SuperAdmin:** Gestión completa del sistema, aprobación de usuarios
- **Usuario estándar:** Gestión de su empresa, clientes, productos y presupuestos

## Características de seguridad

- ✅ Protección CSRF en todos los formularios
- ✅ Prepared statements (PDO) contra SQL Injection
- ✅ Validación y sanitización de datos
- ✅ Hashing seguro de contraseñas (bcrypt)
- ✅ Rate limiting en login (5 intentos / 15 min)
- ✅ Validación de pertenencia empresa-recursos
- ✅ HTTP-only cookies de sesión
- ✅ Validaciones del lado cliente y servidor

## Exportación de datos

### PDF
Los presupuestos se exportan usando **Dompdf** con formato profesional incluyendo:
- Logo de la empresa
- Datos fiscales completos
- Detalle de ítems con cantidades y precios
- Total general
- Observaciones

### Excel
Exportación mediante **PhpSpreadsheet** con:
- Formato profesional con colores
- Cabeceras destacadas
- Fórmulas automáticas para totales
- Filtros y freeze de encabezados
- Formato de moneda para importes

## Desarrollo

### Scripts útiles

Resetear datos de demostración:
```bash
php app/scripts/reset_demo.php
```

Generar datos de prueba:
```bash
php app/scripts/seed_demo.php
```

### Convenciones de código

- **PSR-12** para estilo de código PHP
- **declare(strict_types=1)** en todos los archivos
- **PHPDoc** para documentación de funciones
- Nombres de variables en camelCase
- Nombres de clases en PascalCase

## Problemas comunes

### Error de permisos en uploads/logs
```bash
chmod -R 755 public/uploads logs
chown -R www-data:www-data public/uploads logs
```

### Error de conexión a base de datos
Verificar credenciales en `.env` y que el usuario tenga permisos:
```sql
GRANT ALL PRIVILEGES ON presupuestos_app.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Errores de Composer
```bash
composer clear-cache
composer install --no-cache
```

## Tecnologías utilizadas

- **Backend:** PHP 8.0+ (nativo, sin frameworks)
- **Base de datos:** MySQL 8.0 / MariaDB 10.5
- **Frontend:** Bootstrap 5.3, JavaScript vanilla
- **HTMX:** Para interacciones AJAX sin escribir JavaScript
- **PDO:** Para abstracción de base de datos
- **Composer:** Gestión de dependencias

## Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork del repositorio
2. Crear una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit de cambios (`git commit -am 'Agrega nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## Autor

Desarrollado por [Tu Nombre]

## Contacto

- Email: tu_email@ejemplo.com
- GitHub: [@tu-usuario](https://github.com/tu-usuario)

---

**⭐ Si este proyecto te resultó útil, considera darle una estrella en GitHub**

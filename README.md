<div align="center">

# 📊 Presupuestador Web

### Sistema profesional de gestión de presupuestos

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

[Demo](#-demo) • [Características](#-características) • [Instalación](#-instalación) • [Tecnologías](#-tecnologías)

</div>

---

## 📋 Descripción

**Presupuestador Web** es un sistema completo de gestión empresarial desarrollado en PHP nativo con arquitectura MVC. Permite a las empresas crear, administrar y exportar presupuestos de forma profesional, gestionar clientes, productos y generar documentos en PDF y Excel.

Ideal para pequeñas y medianas empresas que necesitan un sistema robusto, seguro y fácil de usar para la gestión de cotizaciones comerciales.

## ✨ Características

### 🧾 Gestión de Presupuestos
- Creación de presupuestos con múltiples ítems y productos
- Edición, duplicación y eliminación de presupuestos
- Exportación profesional a **PDF** y **Excel**
- Vista de impresión optimizada
- Búsqueda y filtrado avanzado en tiempo real
- Numeración automática consecutiva
- Cálculo automático de subtotales, IVA y totales
- Configuración de validez por presupuesto

### 👥 Gestión de Clientes
- ABM completo (Alta, Baja, Modificación)
- Datos fiscales argentinos (CUIT/DNI, Condición IVA)
- Información de contacto (teléfono, email, domicilio)
- Validación de formato CUIT/DNI
- Búsqueda instantánea con HTMX

### 📦 Catálogo de Productos
- Gestión completa de productos y servicios
- Actualización rápida de precios
- Creación automática desde presupuestos
- Código y descripción detallada

### 🛡️ Panel de Administración
- Gestión de usuarios y empresas (SuperAdmin)
- Aprobación de registros de nuevas empresas
- Activación/desactivación de cuentas
- Logs de correo electrónico
- Auditoría completa de acciones

### 🎨 Interfaz y UX
- **Modo oscuro/claro** con persistencia en localStorage
- Diseño **100% responsive** (Bootstrap 5)
- Búsqueda instantánea sin recargar página (HTMX)
- Notificaciones toast elegantes
- Paginación eficiente
- Subida de logos de empresa

### 🔒 Seguridad
- Protección **CSRF** en todos los formularios
- **Prepared Statements** (PDO) contra SQL Injection
- Validación y sanitización de entradas
- Hashing seguro con **bcrypt**
- **Rate limiting** en login (5 intentos / 15 min)
- Validación de pertenencia empresa-recursos
- Cookies de sesión HTTP-only
- Validaciones lado cliente y servidor

## 🎯 Primer uso

Después de instalar la base de datos, deberás crear tu primer usuario SuperAdmin manualmente:

```sql
INSERT INTO usuarios (empresa_id, nombre, email, password_hash, is_superadmin, estado, created_at, updated_at)
VALUES (NULL, 'Tu Nombre', 'tu@email.com', '$2y$10$...', 1, 'activo', NOW(), NOW());
```

Genera el hash de tu contraseña con:
```php
echo password_hash('tu_contraseña_segura', PASSWORD_BCRYPT);
```

## 🛠️ Tecnologías

<table>
  <tr>
    <td align="center"><strong>Backend</strong></td>
    <td align="center"><strong>Frontend</strong></td>
    <td align="center"><strong>Base de Datos</strong></td>
    <td align="center"><strong>Librerías</strong></td>
  </tr>
  <tr>
    <td>
      • PHP 8.0+<br>
      • Arquitectura MVC<br>
      • PDO (MySQL)<br>
      • Composer
    </td>
    <td>
      • Bootstrap 5.3<br>
      • JavaScript Vanilla<br>
      • HTMX<br>
      • CSS Custom Properties
    </td>
    <td>
      • MySQL 5.7+<br>
      • MariaDB 10.3+<br>
      • UTF8mb4
    </td>
    <td>
      • PHPMailer<br>
      • Dompdf<br>
      • PhpSpreadsheet<br>
      • ZipStream
    </td>
  </tr>
</table>

## ⚠️ IMPORTANTE - Seguridad en Producción

**Antes de subir a un hosting público, DEBES:**

1. ✅ **Cambiar TODAS las credenciales por defecto**
2. ✅ **Configurar `.env`** con credenciales reales (nunca subir `.env` a Git)
3. ✅ **Display errors desactivado** (ya configurado en `public/index.php`)
4. ✅ **Usar HTTPS obligatorio** - Protege credenciales en tránsito
5. ✅ **Permisos restrictivos** en `uploads/` y `logs/` (755 máximo)
6. ✅ **Eliminar scripts de prueba** del directorio `app/scripts/`
7. ⚠️ **Implementar `session_regenerate_id()`** después del login
8. ⚠️ **Cookies seguras**: `session.cookie_secure = 1` y `session.cookie_httponly = 1`

## 📥 Instalación

### Requisitos previos

- PHP 8.0 o superior
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Servidor web (Apache con mod_rewrite o Nginx)
- Extensiones PHP: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`

### Pasos de instalación

#### 1️⃣ Clonar el repositorio

```bash
git clone https://github.com/EstebanRsh/Budgeted-Web.git
cd Budgeted-Web
```

#### 2️⃣ Instalar dependencias

```bash
composer install
```

#### 3️⃣ Crear base de datos

```sql
CREATE DATABASE presupuestos_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importar el schema:

```bash
mysql -u root -p presupuestos_app < presupuestos_app.sql
```

#### 4️⃣ Configurar variables de entorno

```bash
cp .env.example .env
```

Editar `.env` con tus credenciales:

```ini
# Base de datos
DB_HOST=localhost
DB_NAME=presupuestos_app
DB_USER=root
DB_PASS=tu_contraseña

# Correo (opcional)
MAIL_ENABLED=true
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu_email@gmail.com
SMTP_PASSWORD=tu_contraseña_app
```

#### 5️⃣ Configurar permisos

```bash
chmod -R 755 public/uploads logs
chown -R www-data:www-data public/uploads logs
```

#### 6️⃣ Configurar servidor web

**Apache** (DocumentRoot → `public/`)

```apache
<VirtualHost *:80>
    ServerName presupuestador.local
    DocumentRoot /ruta/al/proyecto/public

    <Directory /ruta/al/proyecto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Habilitar mod_rewrite:

```bash
sudo a2enmod rewrite
sudo service apache2 restart
```

#### 7️⃣ Acceder al sistema

Abrir navegador en: `http://localhost/presupuestador`

## 📂 Estructura del proyecto

```
presupuestador/
├── app/
│   ├── controllers/        # Controladores MVC
│   ├── models/            # Modelos de datos
│   ├── views/             # Vistas (HTML + PHP)
│   ├── helpers/           # Funciones auxiliares
│   ├── services/          # Servicios (Email, etc.)
│   └── scripts/           # Scripts de mantenimiento
├── config/                # Configuración
├── logs/                  # Logs de aplicación
├── public/                # DocumentRoot
│   ├── assets/           # CSS, JS, imágenes
│   │   ├── css/
│   │   ├── js/
│   │   └── icons/
│   ├── uploads/          # Logos de empresas
│   └── index.php         # Front Controller
├── vendor/               # Dependencias Composer
├── .env.example
├── .gitignore
├── composer.json
├── LICENSE
└── presupuestos_app.sql  # Schema DB
```

## 🚀 Uso

### Flujo de trabajo

1. **Registro de empresa** → Nuevo usuario se registra y espera aprobación
2. **Configuración** → Cargar logo, datos fiscales, información de contacto
3. **Clientes** → Agregar clientes con sus datos fiscales completos
4. **Productos** → Definir catálogo de productos/servicios con precios
5. **Presupuestos** → Crear presupuestos combinando cliente + productos
6. **Exportar** → Generar PDF/Excel o imprimir directamente

### Roles de usuario

| Rol | Permisos |
|-----|----------|
| **SuperAdmin** | Gestión completa del sistema, aprobación de empresas, administración de usuarios |
| **Usuario** | Gestión de su empresa, clientes, productos y presupuestos |

## 📊 Exportación

### PDF (Dompdf)
- Logo de empresa
- Datos fiscales completos
- Detalle de ítems con cantidades y precios
- Subtotales, IVA y total general
- Observaciones y condiciones

### Excel (PhpSpreadsheet)
- Formato profesional con estilos
- Cabeceras destacadas con colores
- Fórmulas automáticas para cálculos
- Filtros y congelación de encabezados
- Formato de moneda argentino

## 🔧 Scripts útiles

```bash
# Resetear datos de demostración
php app/scripts/reset_demo.php

# Generar datos de prueba
php app/scripts/seed_demo.php

# Crear nuevos usuarios
php app/scripts/seed_users.php
```

## 🐛 Solución de problemas

### Error de permisos

```bash
chmod -R 755 public/uploads logs
chown -R www-data:www-data public/uploads logs
```

### Error de conexión DB

```sql
GRANT ALL PRIVILEGES ON presupuestos_app.* TO 'usuario'@'localhost';
FLUSH PRIVILEGES;
```

### Problemas con Composer

```bash
composer clear-cache
composer install --no-cache
```

## 📄 Licencia

Este proyecto está bajo la **Licencia MIT**. Ver [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Autor

**Esteban Rsh**

- GitHub: [@EstebanRsh](https://github.com/EstebanRsh)
- Email: ruschestebanalberto081201@gmail.com

# 🚀 Guía de Deployment - Hosting Compartido

Esta guía te ayudará a desplegar tu aplicación de forma **SEGURA** en un hosting compartido (Hostinger, cPanel, etc.).

## ⚠️ ANTES DE COMENZAR

### Checklist de Seguridad Pre-Deployment

- [ ] Tienes acceso a cPanel o panel de administración del hosting
- [ ] El hosting soporta PHP 8.0+ y MySQL 5.7+
- [ ] Tienes un dominio o subdominio configurado
- [ ] Tienes certificado SSL/HTTPS activo (Let's Encrypt gratis en la mayoría)

---

## 📋 Paso 1: Preparar archivos localmente

### 1.1 Configurar producción en archivos

**Archivo: `config/app.php`**
```php
// Descomentar esta línea cuando tengas HTTPS:
ini_set('session.cookie_secure', '1');
```

**Archivo: `public/.htaccess`**
```apache
# Descomentar estas líneas para forzar HTTPS:
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### 1.2 Verificar que display_errors esté OFF

**Archivo: `public/index.php`**
```php
// Debe estar así:
ini_set('display_errors', '0');
error_reporting(0);
```

### 1.3 Crear archivo .env para producción

**NO subir este archivo por FTP/Git**. Lo crearás directamente en el servidor.

---

## 📤 Paso 2: Subir archivos al hosting

### Opción A: FTP/SFTP (FileZilla, WinSCP)

1. Conectarte al servidor FTP de tu hosting
2. Subir **TODO** el proyecto a `/public_html/` o `/home/usuario/`
3. **IMPORTANTE**: El DocumentRoot debe apuntar a la carpeta `public/`

### Opción B: cPanel File Manager

1. Comprimir el proyecto localmente (sin `vendor/`, `.git/`, `.env`)
2. Subir el archivo ZIP
3. Extraer en el servidor

### Estructura en el servidor

```
/home/usuario/
├── presupuestador/          ← Proyecto completo aquí
│   ├── app/
│   ├── config/
│   ├── public/             ← Este debe ser el DocumentRoot
│   ├── vendor/
│   ├── .env                ← Crear manualmente (NO subir)
│   ├── .htaccess
│   └── composer.json
```

---

## 🔧 Paso 3: Configurar DocumentRoot

### En cPanel

1. Ir a **"Dominios"** o **"Addon Domains"**
2. Editar el dominio
3. Cambiar **Document Root** a: `/home/usuario/presupuestador/public`
4. Guardar cambios

### En Hostinger

1. Panel → **Hosting** → **Administrar**
2. **Avanzado** → **Configuración PHP**
3. Cambiar **Document Root** a `/public_html/presupuestador/public`

---

## 🗄️ Paso 4: Crear base de datos

### 4.1 Crear BD en cPanel

1. **MySQL Databases** → Crear nueva base de datos
2. Nombre: `usuario_presupuestos` (anota el nombre completo)
3. Crear usuario MySQL
4. Usuario: `usuario_app`
5. Contraseña: **Generar contraseña segura** (anotar)
6. **Agregar usuario a la base de datos** con **TODOS los privilegios**

### 4.2 Importar SQL

1. **phpMyAdmin**
2. Seleccionar la base de datos creada
3. **Importar** → Seleccionar `presupuestos_app.sql`
4. Ejecutar

### 4.3 Crear primer usuario SuperAdmin

Ejecutar en phpMyAdmin:

```sql
-- Primero genera el hash en tu terminal local:
-- php -r "echo password_hash('TuContraseñaSegura123', PASSWORD_BCRYPT);"

INSERT INTO usuarios (empresa_id, nombre, email, password_hash, is_superadmin, estado, created_at, updated_at)
VALUES (NULL, 'Tu Nombre', 'admin@tudominio.com', 'HASH_AQUI', 1, 'activo', NOW(), NOW());
```

---

## 🔐 Paso 5: Configurar .env en producción

### 5.1 Crear archivo .env

En **File Manager** o por **SSH**, crear el archivo `.env` en la raíz del proyecto:

```bash
cd /home/usuario/presupuestador
nano .env
```

### 5.2 Contenido del .env

```ini
# Base de datos
DB_HOST=localhost
DB_NAME=usuario_presupuestos
DB_USER=usuario_app
DB_PASS=tu_contraseña_mysql_generada
DB_CHARSET=utf8mb4

# Correo (opcional - configurar más tarde)
MAIL_ENABLED=false
SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASSWORD=
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=noreply@tudominio.com
SMTP_FROM_NAME=Presupuestador
```

### 5.3 Proteger .env

```bash
chmod 600 .env
```

---

## 📦 Paso 6: Instalar Composer dependencies

### Opción A: SSH (si tienes acceso)

```bash
cd /home/usuario/presupuestador
composer install --no-dev --optimize-autoloader
```

### Opción B: Sin SSH

1. Ejecutar localmente: `composer install --no-dev`
2. Subir la carpeta `vendor/` completa por FTP
3. **Nota**: Puede tardar por ser muchos archivos

---

## 🔒 Paso 7: Configurar permisos

```bash
# Permisos de carpetas
chmod 755 public/uploads
chmod 755 public/uploads/logos
chmod 755 logs

# Owner (si tienes SSH)
chown -R usuario:usuario /home/usuario/presupuestador

# Asegurar que uploads y logs sean escribibles
chmod -R 755 public/uploads
chmod -R 755 logs
```

---

## ✅ Paso 8: Verificación final

### 8.1 Checklist de verificación

- [ ] El sitio carga en `https://tudominio.com` (con HTTPS)
- [ ] Puedes hacer login con el usuario SuperAdmin creado
- [ ] Las rutas amigables funcionan (`/dashboard`, `/clientes`)
- [ ] No se muestran errores de PHP en pantalla
- [ ] Las sesiones persisten al navegar
- [ ] Puedes subir un logo de empresa (test de permisos)

### 8.2 Probar funcionalidades

1. Login/Logout
2. Crear cliente
3. Crear producto
4. Crear presupuesto
5. Exportar PDF
6. Exportar Excel

---

## 🐛 Troubleshooting

### Error: "500 Internal Server Error"

**Causa**: Error en .htaccess o permisos incorrectos

**Solución**:
```bash
# Verificar logs de Apache
tail -f /home/usuario/logs/error_log

# Revisar permisos
chmod 644 .htaccess
chmod 644 public/.htaccess
```

### Error: "Database connection failed"

**Causa**: Credenciales incorrectas en `.env`

**Solución**:
- Verificar nombre de base de datos en cPanel
- Verificar usuario y contraseña
- Usar `localhost` como DB_HOST

### Error: Página sin estilos (CSS no carga)

**Causa**: DocumentRoot mal configurado

**Solución**:
- Verificar que DocumentRoot apunte a `/public`
- Limpiar caché del navegador

### Error: "Class not found"

**Causa**: Composer no ejecutado o vendor/ no subido

**Solución**:
```bash
composer install --no-dev
# O subir vendor/ por FTP
```

---

## 🔐 Seguridad Post-Deployment

### Después del deployment

1. **Cambiar contraseña del SuperAdmin** desde la app
2. **Configurar email SMTP** si quieres notificaciones
3. **Hacer backup de la BD** semanalmente
4. **Eliminar scripts de prueba**:
   ```bash
   rm -rf app/scripts/seed_*.php
   rm -rf app/scripts/reset_*.php
   ```
5. **Monitorear logs**:
   ```bash
   tail -f logs/*.log
   ```

---

## 📞 Soporte de Hosting

### Hostinger
- Chat en vivo 24/7
- Documentación: https://support.hostinger.com

### cPanel (genérico)
- Contactar con tu proveedor de hosting
- Verificar PHP version: Panel → "Select PHP Version"

---

## ✅ Deployment completado

Tu aplicación ahora está:
- ✅ Corriendo en HTTPS
- ✅ Con errores ocultos
- ✅ Con sesiones seguras
- ✅ Con base de datos protegida
- ✅ Con headers de seguridad activos

**Próximos pasos:**
- Configurar backups automáticos en cPanel
- Configurar SMTP para envío de emails
- Personalizar logos y datos de empresa

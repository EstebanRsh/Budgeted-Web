
-- =====================================
-- Estructura de base de datos para Presupuestador
-- SIN datos demo ni inserts masivos
-- =====================================

-- Opcional: crear DB si no existe
CREATE DATABASE IF NOT EXISTS presupuestos_app
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE presupuestos_app;

-- =====================================
-- Tablas
-- =====================================

CREATE TABLE empresas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  nombre VARCHAR(255) NOT NULL,
  cuit VARCHAR(20) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  domicilio VARCHAR(255) DEFAULT NULL,
  telefono VARCHAR(50) DEFAULT NULL,
  web VARCHAR(255) DEFAULT NULL,
  condicion_iva VARCHAR(50) DEFAULT NULL,
  inicio_actividades DATE DEFAULT NULL,
  ingresos_brutos VARCHAR(50) DEFAULT NULL,
  logo_path VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NULL,
  nombre VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_superadmin TINYINT(1) NOT NULL DEFAULT 0,
  estado ENUM('en_espera', 'activo', 'desactivado') NOT NULL DEFAULT 'en_espera',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_is_superadmin (is_superadmin),
  INDEX idx_email (email),
  CONSTRAINT fk_usuarios_empresas
    FOREIGN KEY (empresa_id) REFERENCES empresas(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE clientes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  cuit_dni VARCHAR(20) DEFAULT NULL,
  condicion_iva VARCHAR(50) DEFAULT NULL,
  domicilio VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  telefono VARCHAR(50) DEFAULT NULL,
  observaciones TEXT DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_clientes_empresas
    FOREIGN KEY (empresa_id) REFERENCES empresas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE productos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(255) NOT NULL,
  descripcion TEXT DEFAULT NULL,
  precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_empresas
    FOREIGN KEY (empresa_id) REFERENCES empresas(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE presupuestos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  empresa_id INT UNSIGNED NOT NULL,
  cliente_id INT UNSIGNED NOT NULL,
  numero VARCHAR(50) NOT NULL,
  fecha_emision DATE NOT NULL,
  estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente',
  validez_dias INT NOT NULL DEFAULT 15,
  observaciones TEXT DEFAULT NULL,
  total_general DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_presupuestos_empresas
    FOREIGN KEY (empresa_id) REFERENCES empresas(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_presupuestos_clientes
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE presupuesto_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  presupuesto_id INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NULL,
  descripcion VARCHAR(255) NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_items_presupuestos
    FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_items_productos
    FOREIGN KEY (producto_id) REFERENCES productos(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

  -- =====================================
  -- Usuario admin genérico para primer acceso
  -- email: admin@demo.com
  -- password: admin1234
  -- =====================================
  INSERT INTO usuarios (id, empresa_id, nombre, email, password_hash, is_superadmin, estado, created_at, updated_at)
  VALUES (1, NULL, 'Administrador', 'admin@demo.com', '$2y$10$e0NRy6QwQwQwQwQwQwQwQe0NRy6QwQwQwQwQwQwQwQwQwQwQwQwQ', 1, 'activo', NOW(), NOW());

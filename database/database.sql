-- ============================================================
--  BASE DE DATOS: bienes_raices
--  Proyecto: Plataforma "Hogar Ideal Perú"
--  Importar en phpMyAdmin o ejecutar en MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS `bienes_raices`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `bienes_raices`;

-- ──────────────────────────────────────────
--  TABLA: usuarios (administradores del panel)
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre`     VARCHAR(100)  NOT NULL,
  `email`      VARCHAR(150)  NOT NULL UNIQUE,
  `password`   VARCHAR(255)  NOT NULL,
  `rol`        ENUM('admin', 'supervisor', 'vendedor', 'scrum_master', 'especialista_ti', 'seguridad') NOT NULL DEFAULT 'supervisor',
  `estado`     TINYINT(1) DEFAULT 1,
  `password_reset_required` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ──────────────────────────────────────────
--  TABLA: vendedores (personal de ventas)
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `vendedores` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED,
  `nombre`     VARCHAR(100) NOT NULL,
  `apellido`   VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL UNIQUE,
  `telefono`   VARCHAR(20),
  `dni`        VARCHAR(20),
  `foto`       VARCHAR(255) DEFAULT 'default.jpg',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ──────────────────────────────────────────
--  TABLA: propiedades
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `propiedades` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `titulo`           VARCHAR(200)  NOT NULL,
  `descripcion`      TEXT,
  `precio`           DECIMAL(12,2) NOT NULL,
  `tipo`             ENUM('casa','departamento','terreno','local') NOT NULL DEFAULT 'casa',
  `habitaciones`     TINYINT UNSIGNED DEFAULT 0,
  `banos`            TINYINT UNSIGNED DEFAULT 0,
  `estacionamientos` TINYINT UNSIGNED DEFAULT 0,
  `metros2`          DECIMAL(8,2),
  `direccion`        VARCHAR(255),
  `imagen`           VARCHAR(255) DEFAULT 'no-imagen.jpg',
  `vendedor_id`      INT UNSIGNED,
  `activo`           TINYINT(1) DEFAULT 1,
  `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vendedor_id`) REFERENCES `vendedores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ──────────────────────────────────────────
--  TABLA: mensajes (formulario de contacto)
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre`     VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `telefono`   VARCHAR(20),
  `asunto`     VARCHAR(200),
  `mensaje`    TEXT NOT NULL,
  `leido`      TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ──────────────────────────────────────────
--  INDICES DE OPTIMIZACION (consultas frecuentes)
-- ──────────────────────────────────────────
CREATE INDEX `idx_propiedades_vendedor`   ON `propiedades` (`vendedor_id`);
CREATE INDEX `idx_propiedades_tipo`       ON `propiedades` (`tipo`);
CREATE INDEX `idx_propiedades_activo`     ON `propiedades` (`activo`);
CREATE INDEX `idx_propiedades_created_at` ON `propiedades` (`created_at`);

CREATE INDEX `idx_mensajes_leido`         ON `mensajes` (`leido`);
CREATE INDEX `idx_mensajes_created_at`    ON `mensajes` (`created_at`);
CREATE INDEX `idx_mensajes_email`         ON `mensajes` (`email`);

-- ──────────────────────────────────────────
--  SEGURIDAD (opcional): usuario de aplicacion
--  Ejecutar con un usuario administrador de MySQL
-- ──────────────────────────────────────────
-- CREATE USER 'hogar_app'@'localhost' IDENTIFIED BY 'Cambia_Esta_Clave';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON `bienes_raices`.* TO 'hogar_app'@'localhost';
-- FLUSH PRIVILEGES;

-- ──────────────────────────────────────────
--  DATOS DE PRUEBA – Vendedores
-- ──────────────────────────────────────────
INSERT INTO `vendedores` (`nombre`, `apellido`, `email`, `telefono`) VALUES
('Gabriel',    'Gamero',  'gabriel@hogarideal.pe',    '+51 936 338 196'),
('Jean Pierre','Garcia',  'jeanpierre@hogarideal.pe', '+51 999 888 777'),
('Jorge',      'Campos',  'jorge@hogarideal.pe',      '+51 988 777 666'),
('Victor',     'Quispe',  'victor@hogarideal.pe',     '+51 977 666 555');

-- ──────────────────────────────────────────
--  DATOS DE PRUEBA – Propiedades de ejemplo
-- ──────────────────────────────────────────
INSERT INTO `propiedades` (`titulo`, `descripcion`, `precio`, `tipo`, `habitaciones`, `banos`, `estacionamientos`, `metros2`, `direccion`, `vendedor_id`) VALUES
('Casa Moderna en Surco',           'Hermosa casa de diseño contemporáneo con amplios espacios, cocina americana, jardín privado y piscina. Ideal para familias.',  450000, 'casa',         4, 3, 2, 280.00, 'Av. El Derby 278, Santiago de Surco, Lima',      1),
('Departamento en Miraflores',      'Moderno departamento frente al mar, con vista panorámica al Pacífico. Acabados de primera, gimnasio y áreas comunes.',          280000, 'departamento', 3, 2, 1, 120.00, 'Malecón de la Reserva 1035, Miraflores, Lima',   2),
('Casa en La Molina',               'Amplia casa en condominio cerrado con seguridad 24h. Sala doble altura, cochera para 3 autos y zona de parrilla.',              520000, 'casa',         5, 4, 3, 350.00, 'Calle Las Camelias 456, La Molina, Lima',         1),
('Terreno en Lurín',                'Terreno industrial en zona de expansión, con todos los servicios y acceso a la Panamericana Sur. Ideal para almacén o fábrica.', 90000, 'terreno',      0, 0, 0, 500.00, 'Km 38 Panamericana Sur, Lurín, Lima',             3),
('Local Comercial en San Isidro',   'Local en primer piso con vitrina al exterior, baños propios, depósito y estacionamiento incluido. Zona financiera.',            180000, 'local',        0, 1, 1,  85.00, 'Av. Javier Prado Oeste 1470, San Isidro, Lima',  4),
('Departamento en San Borja',       'Departamento seminuevo en edificio con ascensor, área de juegos para niños y vigilancia. Cerca al Jockey Plaza.',               195000, 'departamento', 3, 2, 1, 110.00, 'Av. San Luis 2345, San Borja, Lima',              2),
('Casa de Playa en Punta Hermosa',  'Casa de playa a 50 metros del mar. Terraza con vista al océano, piscina privada y parrilla. Perfecta para vacacionar.',         320000, 'casa',         4, 3, 2, 200.00, 'Calle Los Delfines 12, Punta Hermosa, Lima',      3),
('Terreno en Cieneguilla',          'Terreno en zona ecológica con vista al río Lurín. Apto para proyecto de bungalows o casa de campo. Acertado en documentos.',     45000, 'terreno',      0, 0, 0, 800.00, 'Sector El Sauce, Cieneguilla, Lima',              4);

-- ──────────────────────────────────────────
--  NOTA IMPORTANTE:
--  El usuario administrador se crea ejecutando:
--  http://localhost/APF1-INTEGRADOR/install
--
--  Credenciales por defecto:
--  Email:    admin@hogarideal.pe
--  Password: admin123
-- ──────────────────────────────────────────

-- ──────────────────────────────────────────
--  TABLA: tickets (Mesa de Ayuda)
-- ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tickets` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo`         VARCHAR(50) NOT NULL UNIQUE,
  `usuario_id`     INT UNSIGNED NOT NULL,
  `servicio_id`    VARCHAR(20) NOT NULL,
  `descripcion`    TEXT NOT NULL,
  `asignado_a`     INT UNSIGNED NULL,
  `prioridad`      ENUM('Baja', 'Media', 'Alta') NOT NULL DEFAULT 'Media',
  `estado`         ENUM('Abierto', 'En Progreso', 'Resuelto', 'Cerrado') NOT NULL DEFAULT 'Abierto',
  `accion_tecnica` TEXT NULL,
  `resolucion`     TEXT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre`   TIMESTAMP NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`asignado_a`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_tickets_codigo` ON `tickets` (`codigo`);
CREATE INDEX `idx_tickets_estado` ON `tickets` (`estado`);
CREATE INDEX `idx_tickets_usuario` ON `tickets` (`usuario_id`);


-- Migración para añadir campos de autenticación y perfil a los vendedores
ALTER TABLE `vendedores`
ADD COLUMN `dni` VARCHAR(20) DEFAULT NULL AFTER `apellido`,
ADD COLUMN `especialidad` VARCHAR(100) DEFAULT NULL AFTER `dni`,
ADD COLUMN `linkedin` VARCHAR(255) DEFAULT NULL AFTER `especialidad`,
ADD COLUMN `password` VARCHAR(255) DEFAULT NULL AFTER `linkedin`,
ADD COLUMN `requiere_cambio_pass` TINYINT(1) DEFAULT 0 AFTER `password`;

-- Asignar una contraseña por defecto a los vendedores existentes para evitar nulos problemáticos, aunque no la usarán a menos que el admin la resetee.
UPDATE `vendedores` SET `password` = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', `requiere_cambio_pass` = 1 WHERE `password` IS NULL;


CREATE TABLE IF NOT EXISTS `tickets` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `codigo`         VARCHAR(50) NOT NULL UNIQUE,
  `usuario_id`     INT UNSIGNED NOT NULL,
  `servicio_id`    VARCHAR(20) NOT NULL,
  `descripcion`    TEXT NOT NULL,
  `asignado_a`     INT UNSIGNED NULL,
  `prioridad`      ENUM('Baja', 'Media', 'Alta') NOT NULL DEFAULT 'Media',
  `estado`         ENUM('Abierto', 'En Progreso', 'Resuelto', 'Cerrado') NOT NULL DEFAULT 'Abierto',
  `accion_tecnica` TEXT NULL,
  `resolucion`     TEXT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre`   TIMESTAMP NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`asignado_a`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_tickets_codigo` ON `tickets` (`codigo`);
CREATE INDEX `idx_tickets_estado` ON `tickets` (`estado`);
CREATE INDEX `idx_tickets_usuario` ON `tickets` (`usuario_id`);


-- ============================================================
-- SPRINT 7: GESTIÓN DE PROSPECTOS Y FLUJO DE VENTAS
-- ============================================================

-- 1. Tabla de Prospectos
CREATE TABLE IF NOT EXISTS prospectos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Formato PROS-YYMMDD-XXX',
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    mensaje TEXT,
    propiedad_id INT UNSIGNED NULL COMMENT 'Puede ser NULL si es un contacto general',
    vendedor_id INT UNSIGNED NULL COMMENT 'ID del vendedor asignado',
    estado ENUM('Nuevo', 'Asignado', 'Contactado', 'Visita_Agendada', 'En_Negociacion', 'Cerrado_Ganado', 'Cerrado_Perdedor') DEFAULT 'Nuevo',
    asignado_por INT UNSIGNED NULL COMMENT 'ID del supervisor que asignó',
    fecha_asignacion DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE SET NULL,
    FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- 2. Historial de Actividades del Prospecto
CREATE TABLE IF NOT EXISTS actividades_prospecto (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prospecto_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Ej: Nuevo, Asignacion, Llamada, Email, Visita, Cierre',
    comentario TEXT,
    fecha_programada DATETIME NULL COMMENT 'Para visitas agendadas',
    nuevo_estado VARCHAR(50) NULL COMMENT 'Si la actividad cambió el estado',
    creado_por INT UNSIGNED NULL COMMENT 'ID del usuario/vendedor (NULL si es automático o por sistema)',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prospecto_id) REFERENCES prospectos(id) ON DELETE CASCADE,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- 3. Templates de mensajes por estado para el portal de seguimiento
CREATE TABLE IF NOT EXISTS mensajes_estado_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estado VARCHAR(50) NOT NULL UNIQUE,
    titulo_template VARCHAR(150) NOT NULL,
    cuerpo_template TEXT NOT NULL COMMENT 'Puede contener variables como {vendedor_nombre}',
    color_hex VARCHAR(10) DEFAULT '#000000',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insertar templates por defecto
INSERT IGNORE INTO mensajes_estado_templates (estado, titulo_template, cuerpo_template, color_hex) VALUES 
('Nuevo', 'Solicitud Recibida', 'Hemos recibido tu solicitud de información. Un supervisor revisará tu caso y pronto te asignará un agente inmobiliario.', '#3b82f6'),
('Asignado', 'Agente Asignado', 'Tu solicitud ha sido asignada al agente inmobiliario <strong>{vendedor_nombre}</strong>. Pronto se pondrá en contacto contigo.', '#8b5cf6'),
('Contactado', 'Primer Contacto', 'Tu agente <strong>{vendedor_nombre}</strong> ya ha iniciado el contacto contigo. Revisa tu correo o teléfono.', '#10b981'),
('Visita_Agendada', 'Visita Programada', 'Se ha agendado una visita a la propiedad. Por favor, asegúrate de estar disponible en la fecha acordada.', '#f59e0b'),
('En_Negociacion', 'En Negociación', 'Actualmente te encuentras en proceso de negociación con tu agente <strong>{vendedor_nombre}</strong>. ¡Esperamos que todo salga bien!', '#6366f1'),
('Cerrado_Ganado', 'Operación Exitosa', '¡Felicidades! La operación se ha cerrado con éxito. Gracias por confiar en Hogar Ideal Perú.', '#22c55e'),
('Cerrado_Perdedor', 'Operación Cancelada', 'Lamentablemente, la operación no pudo concretarse. Esperamos poder ayudarte en el futuro.', '#ef4444');


-- ============================================================
-- SPRINT 8: FLUJO DE CIERRES DE VENTA Y NOTIFICACIONES
-- ============================================================

-- 1. Alterar tabla propiedades
ALTER TABLE propiedades ADD COLUMN estado ENUM('Disponible', 'Vendida', 'Alquilada') DEFAULT 'Disponible';

-- 2. Alterar tabla prospectos para agregar nuevo estado
ALTER TABLE prospectos MODIFY COLUMN estado ENUM('Nuevo', 'Asignado', 'Contactado', 'Visita_Agendada', 'En_Negociacion', 'Pendiente_Cierre', 'Cerrado_Ganado', 'Cerrado_Perdedor') DEFAULT 'Nuevo';

-- 3. Tabla notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    enlace VARCHAR(255) NULL,
    leido TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- 4. Tabla solicitudes_cierre
CREATE TABLE IF NOT EXISTS solicitudes_cierre (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prospecto_id INT UNSIGNED NOT NULL,
    propiedad_id INT UNSIGNED NOT NULL,
    vendedor_id INT UNSIGNED NOT NULL,
    tipo_cierre ENUM('Venta', 'Alquiler') NOT NULL,
    monto_final DECIMAL(12,2) NOT NULL,
    comentarios_vendedor TEXT,
    estado ENUM('Pendiente', 'Aprobado', 'Rechazado') DEFAULT 'Pendiente',
    supervisor_id INT UNSIGNED NULL,
    comentarios_supervisor TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prospecto_id) REFERENCES prospectos(id) ON DELETE CASCADE,
    FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    FOREIGN KEY (vendedor_id) REFERENCES vendedores(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);



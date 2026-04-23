-- ============================================================
--  auto_setup.sql
--  Ejecutar este script en phpMyAdmin o desde la terminal
--  para crear la tabla de usuarios en la base de datos 'auto'
-- ============================================================

-- Seleccionar la base de datos
USE auto;

-- Crear tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255)  NOT NULL,   -- Hash bcrypt de: pepper + password + salt
    salt            VARCHAR(64)   NOT NULL,   -- Sal única por usuario (hex de 32 bytes)
    fecha_registro  DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Explicación del sistema de seguridad:
--
--  1. SAL (salt): string aleatorio único por usuario.
--               Se genera al registrar y se guarda en la BD.
--               Evita ataques de diccionario y rainbow tables.
--
--  2. PIMIENTA (pepper): string secreto fijo guardado en config.php.
--               NO se guarda en la BD. Aunque roben la base de datos,
--               no pueden hacer fuerza bruta sin conocer la pimienta.
--
--  3. HASH: password_hash( PEPPER + contraseña + SALT , BCRYPT )
--           bcrypt aplica su propio trabajo de coste computacional.
-- ============================================================
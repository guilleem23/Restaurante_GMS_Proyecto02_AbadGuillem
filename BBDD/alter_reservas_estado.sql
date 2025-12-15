-- ==========================================
--  ALTER TABLE: reservas
--  Añadir columna 'estado' para tracking de reservas
-- ==========================================

USE restaurante_db;

-- Añadir columna estado
ALTER TABLE reservas 
ADD COLUMN estado ENUM('activa', 'finalizada') DEFAULT 'activa' NOT NULL
AFTER created_at;

-- ==========================================
--  ESTRUCTURA FINAL:
--  id, id_usuario, id_mesa, nombre_cliente, telefono, 
--  fecha, hora_inicio, hora_fin, created_at, estado
-- ==========================================

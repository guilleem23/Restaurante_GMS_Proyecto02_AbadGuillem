-- ==========================================
--  ALTER TABLE: reservas
--  Modificar estructura para sistema de reservas por franja horaria
-- ==========================================

USE restaurante_db;

-- Paso 1: Eliminar la tabla reservas y recrearla con la nueva estructura
DROP TABLE IF EXISTS reservas;

-- Paso 2: Crear tabla reservas con la nueva estructura
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_mesa INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reservas_usuario FOREIGN KEY (id_usuario) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservas_mesa FOREIGN KEY (id_mesa) REFERENCES mesas(id) ON DELETE CASCADE,
    CONSTRAINT chk_time CHECK (hora_fin > hora_inicio)
);

-- ==========================================
--   ESTRUCTURA FINAL:
--   id, id_usuario, id_mesa, fecha, hora_inicio, hora_fin, created_at
-- ==========================================

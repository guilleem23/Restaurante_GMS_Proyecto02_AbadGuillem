<?php
/**
 * Funciones de validación sencillas para Gestión de Mesas
 * Uso: Incluir en archivos de procesamiento
 */

// Validar nombre de mesa
function validarNombre($nombre) {
    $nombre = trim($nombre);
    
    if (empty($nombre)) {
        return false;
    }
    
    if (strlen($nombre) < 2 || strlen($nombre) > 50) {
        return false;
    }
    
    if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $nombre)) {
        return false;
    }
    
    return true;
}

// Validar número de sillas
function validarSillas($sillas) {
    $sillas = intval($sillas);
    
    if ($sillas < 1 || $sillas > 50) {
        return false;
    }
    
    return true;
}

// Validar sala (verifica que exista en la base de datos)
function validarSala($id_sala, $conn) {
    if ($id_sala <= 0) {
        return false;
    }
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM salas WHERE id = :id");
        $stmt->execute([':id' => $id_sala]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}
?>

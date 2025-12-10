<?php
/**
 * Funciones de validación sencillas para Gestión de Salas
 * Uso: Incluir en archivos de procesamiento
 */

// Validar nombre de sala
function validarNombreSala($nombre) {
    $nombre = trim($nombre);
    
    if (empty($nombre)) {
        return false;
    }
    
    if (strlen($nombre) < 2 || strlen($nombre) > 100) {
        return false;
    }
    
    if (!preg_match('/^[a-zA-Z0-9\sáéíóúñÑ\-]+$/u', $nombre)) {
        return false;
    }
    
    return true;
}

// Validar formato de imagen
function validarFormatoImagen($file) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return true; // Opcional, no se valida si no hay archivo
    }
    
    $tipospermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    
    if (!in_array($file['type'], $tipos_permitidos)) {
        return false;
    }
    
    // Validar tamaño máximo 5MB
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return false;
    }
    
    return true;
}
?>

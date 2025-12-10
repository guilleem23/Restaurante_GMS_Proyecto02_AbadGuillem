<?php
/**
 * Funciones de validación sencillas para Gestión de Usuarios
 * Uso: Incluir en archivos de procesamiento
 */

// Validar username
function validarUsername($username) {
    $username = trim($username);
    
    if (empty($username)) {
        return false;
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        return false;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
        return false;
    }
    
    return true;
}

// Validar nombre
function validarNombre($nombre) {
    $nombre = trim($nombre);
    
    if (empty($nombre)) {
        return false;
    }
    
    if (strlen($nombre) < 2 || strlen($nombre) > 100) {
        return false;
    }
    
    if (!preg_match('/^[a-zA-ZáéíóúñÑ\s]+$/u', $nombre)) {
        return false;
    }
    
    return true;
}

// Validar email
function validarEmail($email) {
    $email = trim($email);
    
    if (empty($email)) {
        return false;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    return true;
}

// Validar contraseña
function validarPassword($password) {
    if (empty($password)) {
        return false;
    }
    
    if (strlen($password) < 5) {
        return false;
    }
    
    return true;
}

// Validar rol
function validarRol($rol) {
    $roles_validos = [1, 2, 3]; // 1=Camarero, 2=Admin, 3=Cliente
    
    return in_array((int)$rol, $roles_validos);
}
?>

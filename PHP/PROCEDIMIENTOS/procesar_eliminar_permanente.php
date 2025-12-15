<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN Y ROL ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: ../PUBLIC/index.php");
    exit();
}

// --- OBTENER Y VALIDAR ID DEL USUARIO ---
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=invalid_data");
    exit();
}

// --- PREVENIR QUE EL ADMIN SE ELIMINE A SÍ MISMO ---
if ($id == $_SESSION['id_usuario']) {
    header("Location: ../PUBLIC/gestion_usuarios.php?error=cannot_delete_self");
    exit();
}

try {
    // Iniciar transacción
    $conn->beginTransaction();
    
    // --- VERIFICAR QUE EL USUARIO EXISTE ---
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $conn->rollBack();
        header("Location: ../PUBLIC/gestion_usuarios.php?error=user_not_found");
        exit();
    }

    // --- ELIMINAR PERMANENTEMENTE EL USUARIO ---
    // IMPORTANTE: Esto eliminará el usuario y puede causar problemas con foreign keys
    // Si hay relaciones, deberán manejarse (CASCADE, SET NULL, etc.)
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // Confirmar transacción
    $conn->commit();
    header("Location: ../PUBLIC/gestion_usuarios.php?success=deleted_permanent");
    exit();

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al eliminar permanentemente usuario: " . $e->getMessage());
    
    // Si es error de foreign key, devolver mensaje específico
    if ($e->getCode() == 23000) {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=fk_constraint");
    } else {
        header("Location: ../PUBLIC/gestion_usuarios.php?error=db_error");
    }
    exit();
}

exit();
?>

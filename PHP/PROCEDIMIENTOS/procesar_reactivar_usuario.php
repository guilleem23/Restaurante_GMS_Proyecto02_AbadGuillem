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

try {
    // Iniciar transacción
    $conn->beginTransaction();
    
    // --- VERIFICAR QUE EL USUARIO EXISTE Y ESTÁ INACTIVO ---
    $stmt = $conn->prepare("SELECT id, fecha_baja FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $conn->rollBack();
        header("Location: ../PUBLIC/gestion_usuarios.php?error=user_not_found");
        exit();
    }

    // Verificar que está inactivo
    if ($usuario['fecha_baja'] === null) {
        $conn->rollBack();
        header("Location: ../PUBLIC/gestion_usuarios.php?error=user_already_active");
        exit();
    }

    // --- REACTIVAR USUARIO (Quitar fecha_baja) ---
    $stmt = $conn->prepare("
        UPDATE users 
        SET fecha_baja = NULL
        WHERE id = :id
    ");

    $stmt->execute(['id' => $id]);

    // Confirmar transacción
    $conn->commit();
    header("Location: ../PUBLIC/gestion_usuarios.php?success=reactivated");
    exit();

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al reactivar usuario: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_usuarios.php?error=db_error");
    exit();
}

exit();
?>

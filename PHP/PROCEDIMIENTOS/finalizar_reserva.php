<?php
session_start();
require_once '../CONEXION/conexion.php';

// Verificar sesión
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}

// Obtener ID de reserva
$id_reserva = intval($_GET['id'] ?? 0);

if ($id_reserva <= 0) {
    header("Location: ../PUBLIC/gestion_reservas.php?error=invalid_id");
    exit();
}

try {
    // Iniciar transacción
    $conn->beginTransaction();
    
    // Obtener información de la reserva para redirección
    $stmt = $conn->prepare("SELECT m.id_sala FROM reservas r INNER JOIN mesas m ON r.id_mesa = m.id WHERE r.id = :id");
    $stmt->execute(['id' => $id_reserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reserva) {
        $conn->rollBack();
        header("Location: ../PUBLIC/gestion_reservas.php?error=not_found");
        exit();
    }
    
    $id_sala = $reserva['id_sala'];
    
    // Actualizar estado de la reserva a 'finalizada'
    $stmt = $conn->prepare("UPDATE reservas SET estado = 'finalizada' WHERE id = :id");
    $stmt->execute(['id' => $id_reserva]);
    
    // Confirmar transacción
    $conn->commit();
    
    // Redirigir de vuelta a ver_reservas con mensaje de éxito
    header("Location: ../PUBLIC/ver_reservas.php?id=" . $id_sala . "&success=reserva_finalizada");
    exit();
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al finalizar reserva: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_reservas.php?error=db_error");
    exit();
}

exit();
?>

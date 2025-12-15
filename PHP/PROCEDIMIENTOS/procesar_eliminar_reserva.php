<?php
session_start();
require_once '../CONEXION/conexion.php';

if (!isset($_SESSION['loginok'])) { header("Location: ../PUBLIC/index.php"); exit(); }

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("DELETE FROM reservas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        
        // Confirmar transacción
        $conn->commit();
        header("Location: ../PUBLIC/gestion_reservas.php?success=eliminado");
        exit();
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header("Location: ../PUBLIC/gestion_reservas.php?error=db_error");
        exit();
    }
} else {
    header("Location: ../PUBLIC/gestion_reservas.php");
}
?>
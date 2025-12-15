<?php
session_start();
require_once '../CONEXION/conexion.php';

if (!isset($_SESSION['loginok'])) { header("Location: ../PUBLIC/index.php"); exit(); }

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $conn->prepare("DELETE FROM reservas WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: ../PUBLIC/gestion_reservas.php?success=eliminado");
    } catch (Exception $e) {
        header("Location: ../PUBLIC/gestion_reservas.php?error=db_error");
    }
} else {
    header("Location: ../PUBLIC/gestion_reservas.php");
}
?>
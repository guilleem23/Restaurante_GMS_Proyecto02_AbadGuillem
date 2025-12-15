<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN Y ROL ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: ../PUBLIC/index.php");
    exit();
}

// --- VALIDAR MÉTODO POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_method");
    exit();
}

// --- OBTENER Y VALIDAR DATOS DEL FORMULARIO ---
$nombre = trim($_POST['nombre'] ?? '');
$id_sala = intval($_POST['id_sala'] ?? 0);
$sillas = intval($_POST['sillas'] ?? 0);

// Validaciones básicas
if (empty($nombre) || $id_sala <= 0 || $sillas < 1 || $sillas > 50) {
    header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_data");
    exit();
}

try {
    // Iniciar transacción
    $conn->beginTransaction();
    
    // --- VERIFICAR QUE LA SALA EXISTE ---
    $stmt = $conn->prepare("SELECT id FROM salas WHERE id = :id");
    $stmt->execute(['id' => $id_sala]);
    if (!$stmt->fetch()) {
        $conn->rollBack();
        header("Location: ../PUBLIC/gestion_mesas.php?error=invalid_data");
        exit();
    }
    
    // --- INSERTAR NUEVA MESA ---
    $stmt = $conn->prepare("
        INSERT INTO mesas (nombre, id_sala, sillas, estado)
        VALUES (:nombre, :id_sala, :sillas, 1)
    ");
    
    $stmt->execute([
        'nombre' => $nombre,
        'id_sala' => $id_sala,
        'sillas' => $sillas
    ]);
    
    // Confirmar transacción
    $conn->commit();
    header("Location: ../PUBLIC/gestion_mesas.php?success=created");
    exit();
    
} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error al crear mesa: " . $e->getMessage());
    header("Location: ../PUBLIC/gestion_mesas.php?error=db_error");
    exit();
}

exit();
?>

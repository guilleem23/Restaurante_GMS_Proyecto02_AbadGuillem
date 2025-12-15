<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- VERIFICAR SESIÓN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: ../PUBLIC/login.php");
    exit();
}

// --- VALIDAR MÉTODO POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../PUBLIC/selector_salas.php");
    exit();
}

// --- OBTENER DATOS ---
$mesa_id = intval($_POST['mesa_id'] ?? 0);
$id_camarero = $_SESSION['id_usuario'] ?? 0;

if ($mesa_id <= 0 || $id_camarero <= 0) {
    header("Location: ../PUBLIC/selector_salas.php?error=invalid_data");
    exit();
}

try {
    // --- VERIFICAR QUE LA MESA EXISTE Y ESTÁ LIBRE ---
    $stmt = $conn->prepare("SELECT id, id_sala, estado FROM mesas WHERE id = :id");
    $stmt->execute(['id' => $mesa_id]);
    $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$mesa) {
        header("Location: ../PUBLIC/selector_salas.php?error=mesa_not_found");
        exit();
    }
    
    if ($mesa['estado'] != 1) {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=mesa_ocupada");
        exit();
    }
    
    // --- ASIGNAR MESA AL CAMARERO ---
    $stmt = $conn->prepare("
        UPDATE mesas 
        SET estado = 2, 
            asignado_por = :camarero
        WHERE id = :mesa_id
    ");
    
    $resultado = $stmt->execute([
        'camarero' => $id_camarero,
        'mesa_id' => $mesa_id
    ]);
    
    if ($resultado) {
        // --- BUSCAR RESERVA ACTIVA EN EL HORARIO ACTUAL ---
        $hora_actual = date('H:i:s');
        $fecha_actual = date('Y-m-d');
        
        $stmt_reserva = $conn->prepare("
            SELECT id, hora_inicio, hora_fin 
            FROM reservas 
            WHERE id_mesa = :mesa_id 
            AND fecha = :fecha
            AND estado = 'activa'
            AND hora_inicio <= :hora_actual
            AND hora_fin >= :hora_actual
        ");
        
        $stmt_reserva->execute([
            'mesa_id' => $mesa_id,
            'fecha' => $fecha_actual,
            'hora_actual' => $hora_actual
        ]);
        
        $reserva_activa = $stmt_reserva->fetch(PDO::FETCH_ASSOC);
        $id_reserva = null;
        
        // Si hay reserva activa en el horario, marcarla como finalizada
        if ($reserva_activa) {
            $stmt_finalizar = $conn->prepare("
                UPDATE reservas 
                SET estado = 'finalizada' 
                WHERE id = :id_reserva
            ");
            $stmt_finalizar->execute(['id_reserva' => $reserva_activa['id']]);
            $id_reserva = $reserva_activa['id'];
        }
        
        // --- CREAR REGISTRO DE OCUPACIÓN (con id_reserva si existe) ---
        $stmt = $conn->prepare("
            INSERT INTO ocupaciones (id_camarero, id_sala, id_mesa, inicio_ocupacion, num_comensales, id_reserva)
            VALUES (:camarero, :sala, :mesa, NOW(), 0, :id_reserva)
        ");
        
        $stmt->execute([
            'camarero' => $id_camarero,
            'sala' => $mesa['id_sala'],
            'mesa' => $mesa_id,
            'id_reserva' => $id_reserva
        ]);
        
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&success=mesa_asignada");
    } else {
        header("Location: ../PUBLIC/ver_sala.php?id=" . $mesa['id_sala'] . "&error=db_error");
    }
    
} catch (PDOException $e) {
    error_log("Error al asignar mesa: " . $e->getMessage());
    header("Location: ../PUBLIC/selector_salas.php?error=db_error");
}

exit();
?>

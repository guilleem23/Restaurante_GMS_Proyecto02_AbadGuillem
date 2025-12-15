<?php
session_start();
require_once '../CONEXION/conexion.php';
require_once 'validar_reserva.php';

if (!isset($_SESSION['loginok'])) { header("Location: ../PUBLIC/index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_cliente'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_mesa = intval($_POST['id_mesa'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora_inicio'] ?? '';
    
    
    $redirect_error = "../PUBLIC/crear_reserva.php?error=";

    // === VALIDACIÓN 1: Campos vacíos ===
    if (empty($nombre) || empty($telefono) || empty($fecha) || empty($hora) || $id_mesa <= 0) {
        header("Location: " . $redirect_error . "campos_vacios");
        exit();
    }

    // === VALIDACIÓN 2: Longitud del nombre ===
    if (strlen($nombre) < 3) {
        header("Location: " . $redirect_error . "nombre_corto");
        exit();
    }

    // === VALIDACIÓN 3: Teléfono (9 dígitos) ===
    if (!validarTelefono($telefono)) {
        header("Location: " . $redirect_error . "telefono_invalido");
        exit();
    }

    // === VALIDACIÓN 4: Fecha no puede ser pasada ===
    if (!validarFechaFutura($fecha)) {
        header("Location: " . $redirect_error . "fecha_pasada");
        exit();
    }

    // === VALIDACIÓN 5: Hora válida ===
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
        header("Location: " . $redirect_error . "hora_invalida");
        exit();
    }

    // === VALIDACIÓN 6: Mesa existe ===
    $stmt_mesa = $conn->prepare("SELECT id FROM mesas WHERE id = :id");
    $stmt_mesa->execute(['id' => $id_mesa]);
    if (!$stmt_mesa->fetch()) {
        header("Location: " . $redirect_error . "mesa_no_existe");
        exit();
    }

    // === VALIDACIÓN 7: Disponibilidad (conflictos con ocupaciones y otras reservas) ===
    if (!verificarDisponibilidadConOcupaciones($conn, $id_mesa, $fecha, $hora)) {
        header("Location: " . $redirect_error . "mesa_ocupada");
        exit();
    }

    // Insertar con transacción
    try {
        // Iniciar transacción
        $conn->beginTransaction();
        
        $dt = new DateTime("$fecha $hora");
        $dt->modify('+90 minutes');
        $hora_fin = $dt->format('H:i:s');

        $stmt = $conn->prepare("INSERT INTO reservas (id_usuario, id_mesa, nombre_cliente, telefono, fecha, hora_inicio, hora_fin) VALUES (:uid, :mid, :nom, :tel, :fec, :hini, :hfin)");
        $stmt->execute([
            'uid' => $_SESSION['id_usuario'],
            'mid' => $id_mesa,
            'nom' => $nombre,
            'tel' => $telefono,
            'fec' => $fecha,
            'hini' => $hora,
            'hfin' => $hora_fin
        ]);

        // Confirmar transacción
        $conn->commit();
        
        header("Location: ../PUBLIC/gestion_reservas.php?success=creado");
        exit();
    } catch (PDOException $e) {
        // Revertir cambios en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header("Location: " . $redirect_error . "db_error");
        exit();
    }
}
?>
<?php
session_start();
require_once '../CONEXION/conexion.php';
require_once 'validar_reserva.php';

if (!isset($_SESSION['loginok'])) { header("Location: ../PUBLIC/index.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre_cliente'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $id_mesa = intval($_POST['id_mesa'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora_inicio'] ?? '';


    $url_error = "../PUBLIC/editar_reserva.php?id=$id&error=";

    // === VALIDACIÓN 1: ID de reserva válido ===
    if ($id <= 0) { 
        header("Location: ../PUBLIC/gestion_reservas.php"); 
        exit(); 
    }

    // === VALIDACIÓN 2: Campos vacíos ===
    if (empty($nombre) || empty($telefono) || empty($fecha) || empty($hora) || $id_mesa <= 0) {
        header("Location: " . $url_error . "campos_vacios");
        exit();
    }

    // === VALIDACIÓN 3: Longitud del nombre ===
    if (strlen($nombre) < 3) {
        header("Location: " . $url_error . "nombre_corto");
        exit();
    }

    // === VALIDACIÓN 4: Teléfono (9 dígitos) ===
    if (!validarTelefono($telefono)) {
        header("Location: " . $url_error . "telefono_invalido");
        exit();
    }

    // === VALIDACIÓN 5: Fecha no puede ser pasada ===
    if (!validarFechaFutura($fecha)) {
        header("Location: " . $url_error . "fecha_pasada");
        exit();
    }

    // === VALIDACIÓN 6: Hora válida ===
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
        header("Location: " . $url_error . "hora_invalida");
        exit();
    }

    // === VALIDACIÓN 7: Mesa existe ===
    $stmt_mesa = $conn->prepare("SELECT id FROM mesas WHERE id = :id");
    $stmt_mesa->execute(['id' => $id_mesa]);
    if (!$stmt_mesa->fetch()) {
        header("Location: " . $url_error . "mesa_no_existe");
        exit();
    }

    // === VALIDACIÓN 8: Disponibilidad (excluyendo esta reserva) ===
    if (!verificarDisponibilidadConOcupaciones($conn, $id_mesa, $fecha, $hora, $id)) {
        header("Location: " . $url_error . "mesa_ocupada");
        exit();
    }

    try {
        $dt = new DateTime("$fecha $hora");
        $dt->modify('+90 minutes');
        $hora_fin = $dt->format('H:i:s');

        $stmt = $conn->prepare("UPDATE reservas SET id_mesa=:mid, nombre_cliente=:nom, telefono=:tel, fecha=:fec, hora_inicio=:hini, hora_fin=:hfin WHERE id=:id");
        $stmt->execute([
            'mid' => $id_mesa,
            'nom' => $nombre,
            'tel' => $telefono,
            'fec' => $fecha,
            'hini' => $hora,
            'hfin' => $hora_fin,
            'id' => $id
        ]);

        header("Location: ../PUBLIC/gestion_reservas.php?success=editado");
    } catch (PDOException $e) {
        header("Location: " . $url_error . "db_error");
    }
}
?>
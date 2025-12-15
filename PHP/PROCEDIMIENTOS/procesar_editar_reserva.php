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

    if ($id <= 0) { header("Location: ../PUBLIC/gestion_reservas.php"); exit(); }

    if (empty($nombre) || empty($telefono) || empty($fecha) || empty($hora) || $id_mesa <= 0) {
        header("Location: " . $url_error . "campos_vacios");
        exit();
    }

    if (!validarTelefono($telefono)) {
        header("Location: " . $url_error . "telefono_invalido");
        exit();
    }

    if (!validarFechaFutura($fecha)) {
        header("Location: " . $url_error . "fecha_pasada");
        exit();
    }

    if (!verificarDisponibilidad($conn, $id_mesa, $fecha, $hora, $id)) {
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
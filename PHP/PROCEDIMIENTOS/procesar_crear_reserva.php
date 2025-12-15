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

    // Validaciones
    if (empty($nombre) || empty($telefono) || empty($fecha) || empty($hora) || $id_mesa <= 0) {
        header("Location: " . $redirect_error . "campos_vacios");
        exit();
    }

    if (!validarTelefono($telefono)) {
        header("Location: " . $redirect_error . "telefono_invalido");
        exit();
    }

    if (!validarFechaFutura($fecha)) {
        header("Location: " . $redirect_error . "fecha_pasada");
        exit();
    }

    if (!verificarDisponibilidadConOcupaciones($conn, $id_mesa, $fecha, $hora)) {
        header("Location: " . $redirect_error . "mesa_ocupada");
        exit();
    }

    // Insertar
    try {
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

        header("Location: ../PUBLIC/gestion_reservas.php?success=creado");
    } catch (PDOException $e) {
        header("Location: " . $redirect_error . "db_error");
    }
}
?>
<?php
session_start();
require_once '../CONEXION/conexion.php';

// Variables necesarias para header.php
$username = $_SESSION['username'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 1;

if (!isset($_SESSION['loginok'])) { header("Location: index.php"); exit(); }

// Listar reservas
$sql = "SELECT r.*, m.nombre as nombre_mesa, s.nombre as nombre_sala 
        FROM reservas r 
        INNER JOIN mesas m ON r.id_mesa = m.id 
        INNER JOIN salas s ON m.id_sala = s.id 
        ORDER BY r.fecha DESC, r.hora_inicio ASC";
$stmt = $conn->query($sql);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservas</title>
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/reservas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; // Incluye el header ?>

    <div class="container-reservas">
        <div class="header-reservas">
            <h1>Listado de Reservas</h1>
            <a href="crear_reserva.php" class="btn-nueva">+ Nueva Reserva</a>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Operación realizada correctamente.</div>
        <?php endif; ?>

        <table class="tabla-reservas">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora (1h 30m)</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Sala / Mesa</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservas as $res): ?>
                <tr class="<?= $res['estado'] == 'finalizada' ? 'reserva-finalizada' : '' ?>">
                    <td><?php echo date('d/m/Y', strtotime($res['fecha'])); ?></td>
                    <td><?php echo substr($res['hora_inicio'],0,5) . ' - ' . substr($res['hora_fin'],0,5); ?></td>
                    <td><?php echo htmlspecialchars($res['nombre_cliente']); ?></td>
                    <td><?php echo htmlspecialchars($res['telefono']); ?></td>
                    <td><?php echo htmlspecialchars($res['nombre_sala'] . ' - ' . $res['nombre_mesa']); ?></td>
                    <td>
                        <?php if ($res['estado'] == 'finalizada'): ?>
                            <span class="badge-estado finalizada">Finalizada</span>
                        <?php else: ?>
                            <span class="badge-estado activa">Activa</span>
                        <?php endif; ?>
                    </td>
                    <td class="acciones">
                        <a href="editar_reserva.php?id=<?php echo $res['id']; ?>" class="btn-editar">Editar</a>
                        <a href="../PROCEDIMIENTOS/procesar_eliminar_reserva.php?id=<?php echo $res['id']; ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que quieres eliminar esta reserva?');">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
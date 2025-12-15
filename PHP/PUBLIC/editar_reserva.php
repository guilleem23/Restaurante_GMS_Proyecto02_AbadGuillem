<?php
session_start();
require_once '../CONEXION/conexion.php';

// Variables necesarias para header.php
$username = $_SESSION['username'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 1;

if (!isset($_SESSION['loginok'])) { header("Location: index.php"); exit(); }

$id_reserva = intval($_GET['id'] ?? 0);
if ($id_reserva <= 0) { header("Location: gestion_reservas.php"); exit(); }

// 1. Obtener datos de la reserva
$stmtRes = $conn->prepare("SELECT * FROM reservas WHERE id = :id");
$stmtRes->execute(['id' => $id_reserva]);
$reserva = $stmtRes->fetch(PDO::FETCH_ASSOC);

if (!$reserva) { header("Location: gestion_reservas.php"); exit(); }

// 2. Obtener la mesa actual para saber cual es su sala original
$stmtMesaActual = $conn->prepare("SELECT id_sala FROM mesas WHERE id = :id");
$stmtMesaActual->execute(['id' => $reserva['id_mesa']]);
$mesaData = $stmtMesaActual->fetch(PDO::FETCH_ASSOC);
$id_sala_original = $mesaData['id_sala'];

// 3. Determinar qué sala mostrar en el select.
$id_sala_seleccionada = isset($_GET['id_sala_filtro']) ? intval($_GET['id_sala_filtro']) : $id_sala_original;

// 4. Cargar salas y mesas
$salas = $conn->query("SELECT * FROM salas")->fetchAll(PDO::FETCH_ASSOC);

$stmtMesas = $conn->prepare("SELECT id, nombre, sillas FROM mesas WHERE id_sala = :id AND estado = 1");
$stmtMesas->execute(['id' => $id_sala_seleccionada]);
$mesas = $stmtMesas->fetchAll(PDO::FETCH_ASSOC);

// 5. Priorizar valores GET sobre valores de BD (para preservar cambios al cambiar sala)
$nombre_cliente_value = isset($_GET['nombre_cliente']) ? htmlspecialchars($_GET['nombre_cliente']) : htmlspecialchars($reserva['nombre_cliente']);
$telefono_value = isset($_GET['telefono']) ? htmlspecialchars($_GET['telefono']) : htmlspecialchars($reserva['telefono']);
$fecha_value = isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : $reserva['fecha'];
$hora_inicio_value = isset($_GET['hora_inicio']) ? htmlspecialchars($_GET['hora_inicio']) : substr($reserva['hora_inicio'], 0, 5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reserva</title>
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/reservas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; // Incluye el header ?>

    <div class="container-reservas">
        <h2 style="text-align:center;">Editar Reserva (ID: <?php echo $id_reserva; ?>)</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                 <?php 
                    $err = $_GET['error'];
                    if($err == 'campos_vacios') echo "Faltan campos por rellenar.";
                    if($err == 'telefono_invalido') echo "El teléfono debe tener 9 dígitos.";
                    if($err == 'fecha_pasada') echo "La fecha no puede ser anterior a hoy.";
                    if($err == 'mesa_ocupada') echo "La mesa está ocupada en ese horario (rango 1h 30m).";
                    if($err == 'db_error') echo "Error en la base de datos al actualizar la reserva.";
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="../PROCEDIMIENTOS/procesar_editar_reserva.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $reserva['id']; ?>">

                <div class="form-group">
                    <label>Nombre Cliente:</label>
                    <input type="text" name="nombre_cliente" class="form-control" value="<?php echo $nombre_cliente_value; ?>">
                </div>

                <div class="form-group">
                    <label>Teléfono:</label>
                    <input type="text" name="telefono" class="form-control" value="<?php echo $telefono_value; ?>">
                </div>

                <div class="form-group">
                    <label>Fecha:</label>
                    <input type="date" name="fecha" class="form-control" value="<?php echo $fecha_value; ?>">
                </div>

                <div class="form-group">
                    <label>Hora Inicio:</label>
                    <input type="time" name="hora_inicio" class="form-control" value="<?php echo $hora_inicio_value; ?>">
                </div>

                <div class="form-group">
                    <label>Sala:</label>
                    <select id="sala_select" class="form-control">
                        <?php foreach($salas as $sala): ?>
                            <option value="<?php echo $sala['id']; ?>" <?php if($sala['id'] == $id_sala_seleccionada) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($sala['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Cambiar sala recarga la página para ver las mesas disponibles.</small>
                </div>

                <div class="form-group">
                    <label>Mesa:</label>
                    <select name="id_mesa" class="form-control">
                        <?php if(empty($mesas)): ?>
                             <option value="">No hay mesas activas en esta sala</option>
                        <?php else: ?>
                            <?php foreach($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>" <?php if($mesa['id'] == $reserva['id_mesa']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($mesa['nombre']) . " (" . $mesa['sillas'] . " sillas)"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit" style="background-color: #007bff;">Actualizar Reserva</button>
            </form>
            <br>
            <a href="gestion_reservas.php" style="display:block; text-align:center;">Cancelar</a>
        </div>
    </div>

    <script src="../../JS/reservas.js"></script>
</body>
</html>
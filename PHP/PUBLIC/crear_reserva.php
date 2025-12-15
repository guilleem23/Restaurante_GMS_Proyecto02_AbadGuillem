<?php
session_start();
require_once '../CONEXION/conexion.php';

// Variables necesarias para header.php
$username = $_SESSION['username'] ?? 'Invitado';
$rol = $_SESSION['rol'] ?? 1;

if (!isset($_SESSION['loginok'])) { header("Location: index.php"); exit(); }

// 1. Obtener todas las salas
$stmtSalas = $conn->query("SELECT * FROM salas");
$salas = $stmtSalas->fetchAll(PDO::FETCH_ASSOC);

// 2. Determinar sala seleccionada (por URL o por defecto la primera)
$id_sala_seleccionada = isset($_GET['id_sala_filtro']) ? intval($_GET['id_sala_filtro']) : 0;
$mesas = [];

// 3. Si hay sala seleccionada, cargar TODAS las mesas (no solo libres)
// La validación de disponibilidad se hará en procesar_crear_reserva.php
if ($id_sala_seleccionada > 0) {
    $stmtMesas = $conn->prepare("SELECT id, nombre, sillas FROM mesas WHERE id_sala = :id ORDER BY nombre");
    $stmtMesas->execute(['id' => $id_sala_seleccionada]);
    $mesas = $stmtMesas->fetchAll(PDO::FETCH_ASSOC);
}

// 4. Capturar valores del formulario de los parámetros GET (para preservar al cambiar sala)
$nombre_cliente_value = isset($_GET['nombre_cliente']) ? htmlspecialchars($_GET['nombre_cliente']) : '';
$telefono_value = isset($_GET['telefono']) ? htmlspecialchars($_GET['telefono']) : '';
$fecha_value = isset($_GET['fecha']) ? htmlspecialchars($_GET['fecha']) : '';
$hora_inicio_value = isset($_GET['hora_inicio']) ? htmlspecialchars($_GET['hora_inicio']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Reserva</title>
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/reservas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; // Incluye el header ?>
    
    <div class="container-reservas">
        <h2 style="text-align:center;">Crear Nueva Reserva</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    $err = $_GET['error'];
                    if($err == 'campos_vacios') echo "Faltan campos por rellenar.";
                    if($err == 'nombre_corto') echo "El nombre debe tener al menos 3 caracteres.";
                    if($err == 'telefono_invalido') echo "El teléfono debe tener exactamente 9 dígitos.";
                    if($err == 'fecha_pasada') echo "La fecha no puede ser anterior a hoy.";
                    if($err == 'fecha_hora_pasada') echo "La fecha y hora de la reserva no pueden ser anteriores a la fecha y hora actual.";
                    if($err == 'hora_invalida') echo "La hora no es válida.";
                    if($err == 'mesa_no_existe') echo "La mesa seleccionada no existe.";
                    if($err == 'mesa_ocupada') echo "La mesa está ocupada en ese horario (rango 1h 30m).";
                    if($err == 'db_error') echo "Error en la base de datos al guardar la reserva.";
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form action="../PROCEDIMIENTOS/procesar_crear_reserva.php" method="POST">
                
                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre_cliente" id="nombre_cliente" class="form-control" value="<?php echo $nombre_cliente_value; ?>">
                </div>

                <div class="form-group">
                    <label>Teléfono (9 dígitos):</label>
                    <input type="text" name="telefono" id="telefono" class="form-control" placeholder="600123456" value="<?php echo $telefono_value; ?>">
                </div>

                <div class="form-group">
                    <label>Fecha:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo $fecha_value; ?>">
                </div>

                <div class="form-group">
                    <label>Hora Inicio (Duración fija 1h 30m):</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" value="<?php echo $hora_inicio_value; ?>">
                </div>

                <div class="form-group">
                    <label>Selecciona Sala:</label>
                    <select id="sala_select" class="form-control">
                        <option value="">-- Seleccionar Sala --</option>
                        <?php foreach($salas as $sala): ?>
                            <option value="<?php echo $sala['id']; ?>" <?php if($sala['id'] == $id_sala_seleccionada) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($sala['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Selecciona una sala para ver sus mesas.</small>
                </div>

                <div class="form-group">
                    <label>Selecciona Mesa:</label>
                    <select name="id_mesa" id="id_mesa" class="form-control">
                        <?php if(empty($mesas)): ?>
                            <option value="">
                                <?php echo ($id_sala_seleccionada > 0) ? 'No hay mesas activas en esta sala' : 'Primero selecciona una sala...'; ?>
                            </option>
                        <?php else: ?>
                            <option value="">-- Seleccionar Mesa --</option>
                            <?php foreach($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>">
                                    <?php echo htmlspecialchars($mesa['nombre']) . " (" . $mesa['sillas'] . " sillas)"; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Guardar Reserva</button>
            </form>
            <br>
            <a href="gestion_reservas.php" style="display:block; text-align:center;">Cancelar</a>
        </div>
    </div>
    
    <script src="../../JS/reservas.js"></script>
    <script src="../../JS/validar_reservas.js"></script>
</body>
</html>
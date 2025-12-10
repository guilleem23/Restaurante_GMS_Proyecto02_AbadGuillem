<?php
session_start();
require_once '../CONEXION/conexion.php';

// --- SEGURIDAD: SOLO ADMIN ---
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true || ($_SESSION['rol'] ?? 1) != 2) {
    header("Location: index.php");
    exit();
}

// Variables header
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);
$username = htmlspecialchars($_SESSION['username']);
$rol = $_SESSION['rol'];
$saludo = "Buenos días";

// --- OBTENER MESAS DE LA BASE DE DATOS ---
$mesas = [];
$mensaje = '';
$tipo_mensaje = '';

// Procesar mensajes de operaciones previas
if (isset($_GET['success'])) {
    $tipo_mensaje = 'success';
    switch ($_GET['success']) {
        case 'created':
            $mensaje = 'Mesa creada exitosamente.';
            break;
        case 'updated':
            $mensaje = 'Mesa actualizada exitosamente.';
            break;
        case 'deleted':
            $mensaje = 'Mesa eliminada exitosamente.';
            break;
    }
}

if (isset($_GET['error'])) {
    $tipo_mensaje = 'error';
    switch ($_GET['error']) {
        case 'invalid_data':
            $mensaje = 'Datos inválidos. Por favor verifica la información.';
            break;
        case 'invalid_chairs':
            $mensaje = 'Número de sillas inválido (debe ser entre 1 y 50).';
            break;
        case 'table_occupied':
            $mensaje = 'No se puede eliminar la mesa porque está ocupada.';
            break;
        case 'db_error':
            $mensaje = 'Error de base de datos. Intenta nuevamente.';
            break;
        case 'mesa_not_found':
            $mensaje = 'Mesa no encontrada.';
            break;
        default:
            $mensaje = 'Ocurrió un error. Intenta nuevamente.';
    }
}

try {
    // Obtener todas las salas para el select y agrupación
    $stmt_salas = $conn->query("SELECT id, nombre FROM salas ORDER BY nombre ASC");
    $salas = $stmt_salas->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener todas las mesas con información de la sala
    $stmt = $conn->query("
        SELECT 
            m.id,
            m.nombre,
            m.sillas,
            m.estado,
            m.id_sala,
            s.nombre as sala_nombre,
            u.username as camarero
        FROM mesas m
        INNER JOIN salas s ON m.id_sala = s.id
        LEFT JOIN users u ON m.asignado_por = u.id
        ORDER BY s.nombre ASC, m.nombre ASC
    ");
    $todas_mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar mesas por sala
    $mesas_por_sala = [];
    foreach ($todas_mesas as $mesa) {
        $id_sala = $mesa['id_sala'];
        if (!isset($mesas_por_sala[$id_sala])) {
            $mesas_por_sala[$id_sala] = [
                'nombre_sala' => $mesa['sala_nombre'],
                'mesas' => []
            ];
        }
        $mesas_por_sala[$id_sala]['mesas'][] = $mesa;
    }
    
} catch (PDOException $e) {
    $mensaje = "Error al cargar mesas: " . $e->getMessage();
    $tipo_mensaje = 'error';
    $mesas_por_sala = [];
    $salas = [];
}

// Función para obtener clase de estado
function getEstadoClase($estado) {
    switch ($estado) {
        case 1: return 'libre';
        case 2: return 'ocupada';
        case 3: return 'reservada';
        default: return 'libre';
    }
}

// Función para obtener texto de estado
function getEstadoTexto($estado) {
    switch ($estado) {
        case 1: return 'Libre';
        case 2: return 'Ocupada';
        case 3: return 'Reservada';
        default: return 'Libre';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Mesas - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/gestion_mesas.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
</head>
<body>

    <?php require_once 'header.php'; ?>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="dashboard-title" style="margin-bottom: 0;">Gestión de Mesas</h1>
            <a href="panel_administrador.php" class="logout-btn" style="text-decoration: none; background-color: #eee; color: #333;">
                <i class="fa-solid fa-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <i class="fa-solid fa-<?= $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div class="stat-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: #333; margin: 0;">Listado de Mesas</h2>
                <button class="btn-crear" onclick="abrirModalCrear()">
                    <i class="fa-solid fa-plus"></i> Nueva Mesa
                </button>
            </div>

            <?php if (empty($mesas_por_sala)): ?>
                <div style="text-align: center; padding: 40px; color: #999; background: #f8f9fa; border-radius: 8px;">
                    <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <p>No hay mesas registradas</p>
                </div>
            <?php else: ?>
                <?php foreach ($mesas_por_sala as $id_sala => $datos_sala): ?>
                    <div class="sala-section" style="margin-bottom: 30px;">
                        <!-- Encabezado de Sala -->
                        <div style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0; font-size: 1.2rem;">
                                <i class="fa-solid fa-door-open"></i> <?= htmlspecialchars($datos_sala['nombre_sala']) ?>
                            </h3>
                            <span style="background: rgba(255, 255, 255, 0.2); padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                                <?= count($datos_sala['mesas']) ?> mesa<?= count($datos_sala['mesas']) != 1 ? 's' : '' ?>
                            </span>
                        </div>

                        <!-- Tabla de Mesas de esta Sala -->
                        <div class="table-container" style="border-radius: 0 0 8px 8px; border-top: none;">
                            <table class="mesas-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Sillas</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos_sala['mesas'] as $mesa): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($mesa['id']) ?></td>
                                            <td><strong><?= htmlspecialchars($mesa['nombre']) ?></strong></td>
                                            <td>
                                                <i class="fa-solid fa-chair"></i> <?= htmlspecialchars($mesa['sillas']) ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= getEstadoClase($mesa['estado']) ?>">
                                                    <?= getEstadoTexto($mesa['estado']) ?>
                                                </span>
                                            </td>
                                            <td class="acciones">
                                                <button class="btn-accion btn-editar" onclick='editarMesa(<?= json_encode($mesa) ?>)' title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button class="btn-accion btn-eliminar" 
                                                        onclick="eliminarMesa(<?= $mesa['id'] ?>, '<?= htmlspecialchars($mesa['nombre']) ?>', <?= $mesa['estado'] ?>)" 
                                                        title="Eliminar">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- MODAL PARA CREAR MESA -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nueva Mesa</h2>
                <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrear" action="../PROCEDIMIENTOS/procesar_crear_mesa.php" method="POST" novalidate>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre de la Mesa *</label>
                            <input type="text" name="nombre" id="nuevo_nombre" placeholder="Ej: T1-5, C2-3">
                        </div>

                        <div class="form-group">
                            <label for="nueva_sala">Sala *</label>
                            <select name="id_sala" id="nueva_sala">
                                <option value="">Selecciona una sala</option>
                                <?php foreach ($salas as $sala): ?>
                                    <option value="<?= $sala['id'] ?>"><?= htmlspecialchars($sala['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nuevas_sillas">Número de Sillas *</label>
                        <input type="number" name="sillas" id="nuevas_sillas" min="1" max="50" placeholder="Ej: 4">
                        <small>Cantidad entre 1 y 50</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrear()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Crear Mesa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR MESA -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Mesa</h2>
                <button class="modal-close" onclick="cerrarModalEditar()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formEditar" action="../PROCEDIMIENTOS/procesar_editar_mesa.php" method="POST" novalidate>
                    <input type="hidden" name="id" id="mesa_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="mesa_nombre">Nombre de la Mesa *</label>
                            <input type="text" name="nombre" id="mesa_nombre">
                        </div>

                        <div class="form-group">
                            <label for="mesa_sala">Sala *</label>
                            <select name="id_sala" id="mesa_sala">
                                <?php foreach ($salas as $sala): ?>
                                    <option value="<?= $sala['id'] ?>"><?= htmlspecialchars($sala['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mesa_sillas">Número de Sillas *</label>
                        <input type="number" name="sillas" id="mesa_sillas" min="1" max="50">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalEditar()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Externo -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../JS/validar_mesa.js"></script>
    <script src="../../JS/gestion_mesas.js"></script>

</body>
</html>
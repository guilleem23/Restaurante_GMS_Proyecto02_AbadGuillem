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
$saludo = "Buenos días"; // El header.php lo recalcula

// --- OBTENER USUARIOS DE LA BASE DE DATOS ---
$usuarios = [];
$mensaje = '';
$tipo_mensaje = '';

// Procesar mensajes de operaciones previas
if (isset($_GET['success'])) {
    $tipo_mensaje = 'success';
    switch ($_GET['success']) {
        case 'created':
            $mensaje = 'Usuario creado exitosamente.';
            break;
        case 'updated':
            $mensaje = 'Usuario actualizado exitosamente.';
            break;
        case 'deleted':
            $mensaje = 'Usuario desactivado exitosamente.';
            break;
        case 'reactivated':
            $mensaje = 'Usuario reactivado exitosamente.';
            break;
        case 'deleted_permanent':
            $mensaje = 'Usuario eliminado permanentemente.';
            break;
    }
}

if (isset($_GET['error'])) {
    $tipo_mensaje = 'error';
    switch ($_GET['error']) {
        case 'duplicate_username':
            $mensaje = 'El nombre de usuario ya existe.';
            break;
        case 'duplicate_email':
            $mensaje = 'El email ya está registrado.';
            break;
        case 'invalid_data':
            $mensaje = 'Datos inválidos. Por favor verifica la información.';
            break;
        case 'db_error':
            $mensaje = 'Error de base de datos. Intenta nuevamente.';
            break;
        case 'cannot_delete_self':
            $mensaje = 'No puedes eliminarte a ti mismo.';
            break;
        case 'user_not_found':
            $mensaje = 'Usuario no encontrado.';
            break;
        case 'user_already_active':
            $mensaje = 'El usuario ya está activo.';
            break;
        case 'fk_constraint':
            $mensaje = 'No se puede eliminar el usuario porque tiene registros asociados. Desactívalo en su lugar.';
            break;
        default:
            $mensaje = 'Ocurrió un error. Intenta nuevamente.';
    }
}

// --- PARÁMETROS DE FILTRADO Y PAGINACIÓN ---
$filter_username = isset($_GET['username']) ? trim($_GET['username']) : '';
$filter_rol = isset($_GET['rol']) ? trim($_GET['rol']) : '';
$filter_estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 25;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Validar entries_per_page
if (!in_array($entries_per_page, [10, 25, 50, 100])) {
    $entries_per_page = 25;
}

try {
    // Construir consulta con filtros
    $where_conditions = [];
    $params = [];
    
    // Filtro por username
    if (!empty($filter_username)) {
        $where_conditions[] = "username LIKE :username";
        $params[':username'] = '%' . $filter_username . '%';
    }
    
    // Filtro por rol
    if ($filter_rol !== '') {
        $where_conditions[] = "rol = :rol";
        $params[':rol'] = $filter_rol;
    }
    
    // Filtro por estado
    if ($filter_estado === 'activo') {
        $where_conditions[] = "fecha_baja IS NULL";
    } elseif ($filter_estado === 'inactivo') {
        $where_conditions[] = "fecha_baja IS NOT NULL";
    }
    
    // Construir WHERE clause
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Contar total de registros filtrados
    $count_query = "SELECT COUNT(*) as total FROM users $where_clause";
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calcular paginación
    $total_pages = ceil($total_records / $entries_per_page);
    if ($current_page > $total_pages && $total_pages > 0) {
        $current_page = $total_pages;
    }
    $offset = ($current_page - 1) * $entries_per_page;
    
    // Obtener usuarios con filtros y paginación
    $query = "
        SELECT 
            id, 
            username, 
            nombre, 
            apellido, 
            email, 
            rol,
            fecha_alta,
            fecha_baja,
            CASE 
                WHEN fecha_baja IS NULL THEN 'Activo'
                WHEN fecha_baja IS NOT NULL THEN 'Inactivo'
            END as estado
        FROM users
        $where_clause
        ORDER BY fecha_baja ASC, id DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    
    // Bind de parámetros de filtros
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind de parámetros de paginación
    $stmt->bindValue(':limit', $entries_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular rangos de visualización
    $showing_start = $total_records > 0 ? $offset + 1 : 0;
    $showing_end = min($offset + $entries_per_page, $total_records);
    
} catch (PDOException $e) {
    $mensaje = "Error al cargar usuarios: " . $e->getMessage();
    $tipo_mensaje = 'error';
    $total_records = 0;
    $total_pages = 0;
    $showing_start = 0;
    $showing_end = 0;
}

// Función auxiliar para obtener el nombre del rol
function getNombreRol($rol_id) {
    switch ($rol_id) {
        case 1: return 'Camarero';
        case 2: return 'Administrador';
        case 3: return 'Cliente';
        default: return 'Desconocido';
    }
}

// Función auxiliar para obtener la clase del badge del rol
function getRolBadgeClass($rol_id) {
    switch ($rol_id) {
        case 1: return 'badge-camarero';
        case 2: return 'badge-admin';
        case 3: return 'badge-cliente';
        default: return 'badge-default';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/gestion_usuarios.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
</head>
<body>

    <?php require_once 'header.php'; ?>

    <div class="container">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1 class="dashboard-title" style="margin-bottom: 0;">Gestión de Usuarios</h1>
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
                <h2 style="color: #333; margin: 0;">Listado de Usuarios</h2>
                <button class="btn-crear" onclick="abrirModalCrear()">
                    <i class="fa-solid fa-user-plus"></i> Añadir Nuevo Usuario
                </button>
            </div>

            <!-- Controles de Filtro y Paginación -->
            <form method="GET" action="" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px; align-items: end;">
                    
                    <!-- Búsqueda por Username -->
                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 0.9rem;">
                            <i class="fa-solid fa-search"></i> Buscar por Username
                        </label>
                        <input type="text" name="username" value="<?= htmlspecialchars($filter_username) ?>" placeholder="Escribe para buscar..." 
                               style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                    </div>

                    <!-- Filtro por Rol -->
                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 0.9rem;">
                            <i class="fa-solid fa-user-tag"></i> Rol
                        </label>
                        <select name="rol" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                            <option value="">Todos</option>
                            <option value="1" <?= $filter_rol === '1' ? 'selected' : '' ?>>Camarero</option>
                            <option value="2" <?= $filter_rol === '2' ? 'selected' : '' ?>>Administrador</option>
                            <option value="3" <?= $filter_rol === '3' ? 'selected' : '' ?>>Cliente</option>
                        </select>
                    </div>

                    <!-- Filtro por Estado -->
                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-check"></i> Estado
                        </label>
                        <select name="estado" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                            <option value="">Todos</option>
                            <option value="activo" <?= $filter_estado === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= $filter_estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>

                    <!-- Selector de Entradas por Página -->
                    <div>
                        <label style="display: block; margin-bottom: 5px; color: #555; font-weight: 500; font-size: 0.9rem;">
                            <i class="fa-solid fa-list"></i> Mostrar
                        </label>
                        <select name="entries" onchange="this.form.submit()" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem;">
                            <option value="10" <?= $entries_per_page === 10 ? 'selected' : '' ?>>10 entradas</option>
                            <option value="25" <?= $entries_per_page === 25 ? 'selected' : '' ?>>25 entradas</option>
                            <option value="50" <?= $entries_per_page === 50 ? 'selected' : '' ?>>50 entradas</option>
                            <option value="100" <?= $entries_per_page === 100 ? 'selected' : '' ?>>100 entradas</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button type="submit" style="padding: 8px 20px; background: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                        <i class="fa-solid fa-filter"></i> Aplicar Filtros
                    </button>
                    <a href="?" style="padding: 8px 20px; background: #95a5a6; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-block;">
                        <i class="fa-solid fa-times"></i> Limpiar Filtros
                    </a>
                </div>

                <!-- Información de resultados -->
                <div style="margin-top: 10px; color: #666; font-size: 0.9rem;">
                    <i class="fa-solid fa-info-circle"></i> Mostrando <?= $showing_start ?>-<?= $showing_end ?> de <?= $total_records ?> usuarios
                </div>
            </form>

            <div class="table-container">
                <table class="usuarios-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                    No hay usuarios registrados
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr class="<?= $usuario['fecha_baja'] ? 'usuario-inactivo' : '' ?>">
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($usuario['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                                    <td>
                                        <span class="badge <?= getRolBadgeClass($usuario['rol']) ?>">
                                            <?= getNombreRol($usuario['rol']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $usuario['fecha_baja'] ? 'badge-inactivo' : 'badge-activo' ?>">
                                            <?= $usuario['estado'] ?>
                                        </span>
                                    </td>
                                    <td class="acciones">
                                        <?php if (!$usuario['fecha_baja']): ?>
                                            <!-- Usuario Activo: Editar y Desactivar -->
                                            <button class="btn-accion btn-editar" onclick='editarUsuario(<?= json_encode($usuario) ?>)' title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <?php if ($usuario['id'] != $_SESSION['id_usuario']): ?>
                                                <button class="btn-accion btn-eliminar" onclick="eliminarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Desactivar">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- Usuario Inactivo: Reactivar y Eliminar Permanentemente -->
                                            <button class="btn-accion btn-reactivar" onclick="reactivarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Reactivar">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button class="btn-accion btn-eliminar-permanente" onclick="eliminarPermanente(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['username']) ?>')" title="Eliminar Permanentemente">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Navegación de Paginación -->
            <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 20px; padding: 15px;">
                <?php
                // Construir query string para mantener filtros
                $query_params = [
                    'username' => $filter_username,
                    'rol' => $filter_rol,
                    'estado' => $filter_estado,
                    'entries' => $entries_per_page
                ];
                $query_string = http_build_query(array_filter($query_params));
                ?>
                
                <!-- Botón Anterior -->
                <?php if ($current_page > 1): ?>
                    <a href="?<?= $query_string ?>&page=<?= $current_page - 1 ?>" 
                       style="padding: 8px 16px; background: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; text-decoration: none;">
                        <i class="fa-solid fa-chevron-left"></i> Anterior
                    </a>
                <?php else: ?>
                    <span style="padding: 8px 16px; background: #95a5a6; color: white; border: none; border-radius: 6px; opacity: 0.5;">
                        <i class="fa-solid fa-chevron-left"></i> Anterior
                    </span>
                <?php endif; ?>
                
                <!-- Números de Página -->
                <div style="display: flex; gap: 5px;">
                    <?php
                    // Lógica para mostrar números de página con elipsis
                    $show_pages = 7; // Máximo de páginas a mostrar
                    
                    if ($total_pages <= $show_pages) {
                        // Mostrar todas las páginas
                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i == $current_page) {
                                echo '<span style="padding: 8px 12px; background: #2c3e50; color: white; border-radius: 4px; font-weight: 600;">' . $i . '</span>';
                            } else {
                                echo '<a href="?' . $query_string . '&page=' . $i . '" style="padding: 8px 12px; border: 1px solid #ddd; background: white; color: #333; border-radius: 4px; text-decoration: none; transition: all 0.2s;">' . $i . '</a>';
                            }
                        }
                    } else {
                        // Mostrar con elipsis
                        // Siempre mostrar primera página
                        if ($current_page == 1) {
                            echo '<span style="padding: 8px 12px; background: #2c3e50; color: white; border-radius: 4px; font-weight: 600;">1</span>';
                        } else {
                            echo '<a href="?' . $query_string . '&page=1" style="padding: 8px 12px; border: 1px solid #ddd; background: white; color: #333; border-radius: 4px; text-decoration: none;">1</a>';
                        }
                        
                        if ($current_page > 3) {
                            echo '<span style="padding: 8px 4px; color: #999;">...</span>';
                        }
                        
                        $start_page = max(2, $current_page - 1);
                        $end_page = min($total_pages - 1, $current_page + 1);
                        
                        for ($i = $start_page; $i <= $end_page; $i++) {
                            if ($i == $current_page) {
                                echo '<span style="padding: 8px 12px; background: #2c3e50; color: white; border-radius: 4px; font-weight: 600;">' . $i . '</span>';
                            } else {
                                echo '<a href="?' . $query_string . '&page=' . $i . '" style="padding: 8px 12px; border: 1px solid #ddd; background: white; color: #333; border-radius: 4px; text-decoration: none;">' . $i . '</a>';
                            }
                        }
                        
                        if ($current_page < $total_pages - 2) {
                            echo '<span style="padding: 8px 4px; color: #999;">...</span>';
                        }
                        
                        // Siempre mostrar última página
                        if ($current_page == $total_pages) {
                            echo '<span style="padding: 8px 12px; background: #2c3e50; color: white; border-radius: 4px; font-weight: 600;">' . $total_pages . '</span>';
                        } else {
                            echo '<a href="?' . $query_string . '&page=' . $total_pages . '" style="padding: 8px 12px; border: 1px solid #ddd; background: white; color: #333; border-radius: 4px; text-decoration: none;">' . $total_pages . '</a>';
                        }
                    }
                    ?>
                </div>
                
                <!-- Botón Siguiente -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?<?= $query_string ?>&page=<?= $current_page + 1 ?>" 
                       style="padding: 8px 16px; background: #2c3e50; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; text-decoration: none;">
                        Siguiente <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span style="padding: 8px 16px; background: #95a5a6; color: white; border: none; border-radius: 6px; opacity: 0.5;">
                        Siguiente <i class="fa-solid fa-chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- MODAL PARA EDITAR USUARIO -->
    <div id="modalUsuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Editar Usuario</h2>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formUsuario" action="../PROCEDIMIENTOS/procesar_editar_usuario.php" method="POST" novalidate>
                    <input type="hidden" name="id" id="usuario_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_username">Username *</label>
                            <input type="text" name="username" id="usuario_username">
                        </div>
                        <div class="form-group">
                            <label for="usuario_rol">Rol *</label>
                            <select name="rol" id="usuario_rol">
                                <option value="1">Camarero</option>
                                <option value="2">Administrador</option>
                                <option value="3">Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="usuario_nombre">
                        </div>
                        <div class="form-group">
                            <label for="usuario_apellido">Apellido</label>
                            <input type="text" name="apellido" id="usuario_apellido">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="usuario_email">Email *</label>
                        <input type="email" name="email" id="usuario_email">
                    </div>

                    <div class="form-group">
                        <label for="usuario_password">Nueva Contraseña (dejar vacío para no cambiar)</label>
                        <input type="password" name="password" id="usuario_password" minlength="5">
                        <small>Mínimo 5 caracteres. Dejar vacío si no desea cambiarla.</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA CREAR USUARIO -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Usuario</h2>
                <button class="modal-close" onclick="cerrarModalCrear()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCrear" action="../PROCEDIMIENTOS/procesar_crear_usuario.php" method="POST" novalidate>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_username">Username *</label>
                            <input type="text" name="username" id="nuevo_username" minlength="3">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_rol">Rol *</label>
                            <select name="rol" id="nuevo_rol">
                                <option value="1">Camarero</option>
                                <option value="2">Administrador</option>
                                <option value="3">Cliente</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nuevo_nombre">Nombre *</label>
                            <input type="text" name="nombre" id="nuevo_nombre">
                        </div>
                        <div class="form-group">
                            <label for="nuevo_apellido">Apellido</label>
                            <input type="text" name="apellido" id="nuevo_apellido">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_email">Email *</label>
                        <input type="email" name="email" id="nuevo_email">
                    </div>

                    <div class="form-group">
                        <label for="nuevo_password">Contraseña *</label>
                        <input type="password" name="password" id="nuevo_password" minlength="5">
                        <small>Mínimo 5 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_password_confirm">Confirmar Contraseña *</label>
                        <input type="password" name="password_confirm" id="nuevo_password_confirm" minlength="5">
                        <small>Debe coincidir con la contraseña</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancelar" onclick="cerrarModalCrear()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Externo -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../JS/validar_usuario.js"></script>
    <script src="../../JS/gestion_usuarios.js"></script>

</body>
</html>
<?php
session_start();
require_once '../CONEXION/conexion.php';

// Verificación de sesión
if (!isset($_SESSION['loginok']) || $_SESSION['loginok'] !== true) {
    header("Location: login.php");
    exit();
}

// Obtener ID de sala
$id_sala = intval($_GET['id'] ?? 0);

if ($id_sala <= 0) {
    header("Location: selector_salas.php");
    exit();
}

// Obtener fecha del filtro (hoy por defecto)
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');

// Variables para header
$nombre = htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['username']);
$username = htmlspecialchars($_SESSION['username']);
$rol = $_SESSION['rol'] ?? 1;
$saludo = "Buenos días";

// Obtener información de la sala
try {
    $stmt = $conn->prepare("SELECT id, nombre, imagen_fondo, imagen_mesa FROM salas WHERE id = :id");
    $stmt->execute(['id' => $id_sala]);
    $sala = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sala) {
        header("Location: selector_salas.php?error=sala_not_found");
        exit();
    }
} catch (PDOException $e) {
    die("Error al cargar sala: " . $e->getMessage());
}

// Obtener reservas de la fecha seleccionada para esta sala
try {
    $stmt = $conn->prepare("
        SELECT 
            r.id,
            r.nombre_cliente,
            r.telefono,
            r.hora_inicio,
            r.hora_fin,
            r.estado,
            m.id as mesa_id,
            m.nombre as mesa_nombre,
            m.sillas,
            u.nombre as nombre_camarero
        FROM reservas r
        INNER JOIN mesas m ON r.id_mesa = m.id
        LEFT JOIN users u ON r.id_usuario = u.id
        WHERE m.id_sala = :sala
        AND r.fecha = :fecha
        ORDER BY r.hora_inicio ASC
    ");
    $stmt->execute(['sala' => $id_sala, 'fecha' => $fecha_filtro]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar reservas: " . $e->getMessage());
}

// Obtener todas las salas para el selector
try {
    $stmt_salas = $conn->query("SELECT id, nombre FROM salas ORDER BY nombre ASC");
    $todas_salas = $stmt_salas->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $todas_salas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - <?= htmlspecialchars($sala['nombre']) ?> - Casa GMS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="../../css/panel_principal.css">
    <link rel="stylesheet" href="../../css/ver_sala.css">
    <link rel="icon" type="image/png" href="../../img/icono.png">
    
    <style>
        /* Estilos dinámicos inyectados desde PHP con variables de la sala */
        .sala-layout {
            background-image: url('<?= $sala['imagen_fondo'] ? '../../img/salas/fondos/' . htmlspecialchars($sala['imagen_fondo']) : '../../img/fondo_panel_principal.png' ?>');
        }
        
        .mesa-img {
            content: url('<?= $sala['imagen_mesa'] ? '../../img/salas/mesas/' . htmlspecialchars($sala['imagen_mesa']) : '../../img/mesa2.png' ?>');
        }
        
        /* Estilos para reservas */
        .mesa-card.activa {
            border-color: #28a745;
            background: rgba(200, 250, 205, 0.95);
        }
        
        .mesa-card.activa:hover {
            background: rgba(200, 250, 205, 1);
            border-color: #218838;
        }
        
        .mesa-card.finalizada {
            border-color: #6c757d;
            background: rgba(220, 220, 220, 0.95);
            opacity: 0.7;
        }
        
        .mesa-card.finalizada:hover {
            background: rgba(200, 200, 200, 1);
            opacity: 0.85;
        }
        
        .mesa-estado-badge.activa {
            background-color: #28a745;
        }
        
        .mesa-estado-badge.finalizada {
            background-color: #6c757d;
        }
        
        /* Animación de entrada */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Mejora de cards de reservas */
        .mesa-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .mesa-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25) !important;
        }
        
        .mesa-card.activa {
            border: 3px solid #28a745;
            background: linear-gradient(145deg, rgba(200, 250, 205, 0.95), rgba(180, 240, 185, 0.95));
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.2);
        }
        
        .mesa-card.finalizada {
            border: 3px solid #6c757d;
            background: linear-gradient(145deg, rgba(220, 220, 220, 0.95), rgba(200, 200, 200, 0.95));
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.15);
        }
        
        .mesa-estado-badge {
            font-size: 0.75rem;
            padding: 5px 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .mesa-label {
            font-size: 1.1rem;
            color: #1a1a1a;
            text-shadow: 0 1px 2px rgba(255,255,255,0.8);
        }
        
        .mesa-camarero {
            background: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            color: #ce4535;
        }
    </style>

</head>
<body>
    <?php include 'header.php'; ?>

    <div class="sala-container">
        <main class="sala-layout">
            <div class="sala-header">
                <div class="sala-title-section">
                    <a href="ver_sala.php?id=<?= $id_sala ?>" class="btn-back">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <h1 class="sala-title">Reservas - <?= htmlspecialchars($sala['nombre']) ?></h1>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="ver_sala.php?id=<?= $id_sala ?>" class="btn-salas">
                        <i class="fa-solid fa-chair"></i> Ver Mesas
                    </a>
                    <a href="gestion_reservas.php" class="btn-salas">
                        <i class="fa-solid fa-list"></i> Gestión Reservas
                    </a>
                </div>
            </div>
            
            <!-- Dropdown selector de salas -->
            <div class="dropdown">
                <button class="btn btn-salas dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-door-open"></i> <?= htmlspecialchars($sala['nombre']) ?>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($todas_salas as $s): ?>
                        <li>
                            <a class="dropdown-item <?= $s['id'] == $id_sala ? 'active' : '' ?>" 
                               href="ver_reservas.php?id=<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['nombre']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Filtro de fecha -->
            <div style="margin: 20px 0;">
                <form method="GET" id="filtroFecha" style="
                    display: flex; 
                    align-items: center; 
                    gap: 15px; 
                    background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(248,249,250,0.95)); 
                    padding: 20px 25px; 
                    border-radius: 15px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    border: 1px solid rgba(206, 69, 53, 0.2);
                ">
                    <input type="hidden" name="id" value="<?= $id_sala ?>">
                    <label style="
                        font-weight: 700; 
                        color: #2c3e50;
                        font-size: 1.1rem;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    ">
                        <i class="fa-solid fa-calendar" style="color: #ce4535;"></i> Fecha:
                    </label>
                    <input type="date" 
                           name="fecha" 
                           value="<?= htmlspecialchars($fecha_filtro) ?>" 
                           onchange="this.form.submit()"
                           style="
                               padding: 10px 15px; 
                               border: 2px solid #ce4535; 
                               border-radius: 10px; 
                               font-size: 1rem; 
                               cursor: pointer;
                               font-weight: 600;
                               color: #2c3e50;
                               background: white;
                               transition: all 0.3s ease;
                           "
                           onmouseover="this.style.boxShadow='0 0 10px rgba(206,69,53,0.3)'"
                           onmouseout="this.style.boxShadow='none'">
                    <div style="
                        background: linear-gradient(135deg, #ce4535, #b73728);
                        color: white;
                        padding: 8px 20px;
                        border-radius: 20px;
                        font-weight: 600;
                        font-size: 0.95rem;
                        box-shadow: 0 2px 8px rgba(206,69,53,0.3);
                    ">
                        <i class="fa-solid fa-list-check"></i> <?= count($reservas) ?> reserva<?= count($reservas) != 1 ? 's' : '' ?>
                    </div>
                </form>
            </div>

            <?php if (isset($_GET['success']) && $_GET['success'] === 'reserva_finalizada'): ?>
                <div style="
                    background: linear-gradient(135deg, #d4edda, #c3e6cb); 
                    color: #155724; 
                    padding: 18px 25px; 
                    border-radius: 12px; 
                    margin: 20px 0; 
                    border: 2px solid #28a745;
                    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    animation: slideIn 0.5s ease-out;
                ">
                    <i class="fa-solid fa-check-circle" style="font-size: 1.5rem; color: #28a745;"></i> 
                    <strong>Reserva finalizada correctamente</strong>
                </div>
            <?php endif; ?>


            <?php if (!empty($reservas)): ?>
                <div class="mesas-grid">
                    <?php foreach ($reservas as $reserva): ?>
                        <?php 
                            $clase_estado = $reserva['estado'] === 'finalizada' ? 'finalizada' : 'activa';
                        ?>
                        <div class="mesa-card <?= $clase_estado ?>" 
                             data-reserva-id="<?= $reserva['id'] ?>"
                             data-reserva-cliente="<?= htmlspecialchars($reserva['nombre_cliente']) ?>"
                             data-reserva-telefono="<?= htmlspecialchars($reserva['telefono']) ?>"
                             data-reserva-hora-inicio="<?= $reserva['hora_inicio'] ?>"
                             data-reserva-hora-fin="<?= $reserva['hora_fin'] ?>"
                             data-reserva-estado="<?= $reserva['estado'] ?>"
                             data-mesa-nombre="<?= htmlspecialchars($reserva['mesa_nombre']) ?>"
                             data-mesa-sillas="<?= $reserva['sillas'] ?>"
                             data-nombre-camarero="<?= htmlspecialchars($reserva['nombre_camarero'] ?? 'N/A') ?>"
                             onclick="mostrarInfoReserva(this)">
                            
                            <img src="<?= $sala['imagen_mesa'] ? '../../img/salas/mesas/' . htmlspecialchars($sala['imagen_mesa']) : '../../img/mesa2.png' ?>" 
                                 alt="Mesa" 
                                 class="mesa-img">
                            
                            <span class="mesa-label"><?= htmlspecialchars($reserva['mesa_nombre']) ?></span>
                            
                            <div class="mesa-sillas">
                                <i class="fa-solid fa-user"></i> <?= htmlspecialchars($reserva['nombre_cliente']) ?>
                            </div>
                            
                            <div class="mesa-camarero">
                                <i class="fa-solid fa-clock"></i> <?= substr($reserva['hora_inicio'],0,5) ?> - <?= substr($reserva['hora_fin'],0,5) ?>
                            </div>
                            
                            <div class="mesa-estado-badge <?= $clase_estado ?>">
                                <?= $clase_estado === 'finalizada' ? '<i class="fa-solid fa-check-circle"></i> Finalizada' : '<i class="fa-solid fa-calendar-check"></i> Activa' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="../../JS/ver_reservas.js"></script>

</body>
</html>

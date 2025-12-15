<?php
// /PROCEDIMIENTOS/validar_reserva.php

function validarTelefono($telefono) {
    // 9 dígitos exactos y solo números
    return preg_match('/^[0-9]{9}$/', trim($telefono));
}

function validarFechaFutura($fecha) {
    $fecha_actual = date('Y-m-d');
    return $fecha >= $fecha_actual;
}


function verificarDisponibilidad($conn, $id_mesa, $fecha, $hora_inicio, $id_reserva_ignorar = null) {
    // Calcular hora fin propuesta (+1 hora 30 min)
    $inicio = new DateTime("$fecha $hora_inicio");
    $fin = clone $inicio;
    $fin->modify('+90 minutes');
    $hora_fin_propuesta = $fin->format('H:i:s');
    
    // QUERY: Buscar si existe alguna reserva que se solape
    // Condición de solape: (InicioA < FinB) AND (FinA > InicioB)
    $sql = "SELECT COUNT(*) FROM reservas 
            WHERE id_mesa = :id_mesa 
            AND fecha = :fecha
            AND hora_inicio < :hora_fin_propuesta 
            AND hora_fin > :hora_inicio_propuesta";
            
    $params = [
        'id_mesa' => $id_mesa,
        'fecha' => $fecha,
        'hora_fin_propuesta' => $hora_fin_propuesta,
        'hora_inicio_propuesta' => $hora_inicio
    ];

    if ($id_reserva_ignorar) {
        $sql .= " AND id != :id_ignorar";
        $params['id_ignorar'] = $id_reserva_ignorar;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchColumn() == 0;
}

/**
 * Verifica disponibilidad considerando tanto reservas como ocupaciones activas
 * Permite reservar mesas ocupadas si no hay conflicto de horario (fuera de 1h 30min de ocupación)
 */
function verificarDisponibilidadConOcupaciones($conn, $id_mesa, $fecha, $hora_inicio, $id_reserva_ignorar = null) {
    // 1. Verificar conflictos con otras reservas
    if (!verificarDisponibilidad($conn, $id_mesa, $fecha, $hora_inicio, $id_reserva_ignorar)) {
        return false;
    }
    
    // 2. Verificar conflictos con ocupaciones activas (SOLO si la fecha es HOY)
    if ($fecha != date('Y-m-d')) {
        return true; // Ocupaciones solo afectan al día actual
    }
    
    // Buscar ocupaciones activas de la mesa HOY
    $sql = "SELECT inicio_ocupacion FROM ocupaciones 
            WHERE id_mesa = :id_mesa 
            AND DATE(inicio_ocupacion) = :fecha
            AND final_ocupacion IS NULL";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id_mesa' => $id_mesa, 'fecha' => $fecha]);
    $ocupacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ocupacion) {
        return true; // No hay ocupación activa
    }
    
    // Calcular rango de ocupación (inicio hasta inicio + 1h 30min)
    $inicio_ocup = new DateTime($ocupacion['inicio_ocupacion']);
    $fin_ocup = clone $inicio_ocup;
    $fin_ocup->modify('+90 minutes');
    
    // Calcular rango de reserva propuesta
    $inicio_reserva = new DateTime("$fecha $hora_inicio");
    $fin_reserva = clone $inicio_reserva;
    $fin_reserva->modify('+90 minutes');
    
    // Comprobar solapamiento: (InicioA < FinB) AND (FinA > InicioB)
    if ($inicio_reserva < $fin_ocup && $fin_reserva > $inicio_ocup) {
        return false; // Hay conflicto con ocupación activa
    }
    
    return true; // No hay conflicto
}
?>
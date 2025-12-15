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
?>
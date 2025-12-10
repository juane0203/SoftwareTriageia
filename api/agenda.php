<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'No autorizado']));
}

require_once 'config.php';
// Cargar conexión
if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

$db = Database::getInstance()->getConnection();

try {
    // Filtros
    $fecha = $_GET['fecha'] ?? date('Y-m-d'); // Por defecto hoy
    $profesional = $_GET['profesional_id'] ?? '';

    $sql = "SELECT 
                c.caso_id, c.fecha_atencion, c.hora_atencion, c.nivel_triage_final, c.nivel_triage_ia,
                p.nombre as p_nombre, p.apellido as p_apellido, p.direccion, p.telefono,
                u.nombre as prof_nombre, u.especialidad
            FROM CASO_TRIAGE c
            JOIN PACIENTE p ON c.paciente_id = p.paciente_id
            LEFT JOIN USUARIO u ON c.profesional_atencion_id = u.usuario_id
            WHERE c.estado = 'aprobado' 
            AND c.fecha_atencion IS NOT NULL";

    $params = [];

    // Si se selecciona una fecha específica
    if ($fecha) {
        $sql .= " AND c.fecha_atencion = ?";
        $params[] = $fecha;
    }

    // Si se filtra por profesional
    if ($profesional) {
        $sql .= " AND c.profesional_atencion_id = ?";
        $params[] = $profesional;
    }

    $sql .= " ORDER BY c.fecha_atencion ASC, c.hora_atencion ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'citas' => $citas]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'No autorizado']));
}

if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();
    
    switch ($action) {
        case 'estadisticas':
            echo json_encode(obtenerEstadisticas($db));
            break;
            
        case 'listar':
            echo json_encode(listarCasos($db));
            break;
            
        default:
            throw new Exception('Acciиоn no vивlida');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function obtenerEstadisticas($db) {
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN nivel_triage_ia IN (1,2) THEN 1 ELSE 0 END) as emergencias,
                SUM(CASE WHEN estado = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'VALIDADO' AND DATE(fecha_validacion) = CURDATE() THEN 1 ELSE 0 END) as validados_hoy
            FROM CASO_TRIAGE";
    
    $stmt = $db->query($sql);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'stats' => $stats
    ];
}

function listarCasos($db) {
    $estado = $_GET['estado'] ?? '';
    $nivel = $_GET['nivel'] ?? '';
    $fecha = $_GET['fecha'] ?? '';
    
    $sql = "SELECT 
                ct.id_caso,
                ct.fecha_creacion,
                ct.nivel_triage_ia,
                ct.sintomas,
                ct.estado,
                p.nombre,
                p.apellido,
                p.edad,
                p.sexo,
                p.telefono,
                p.embarazo
            FROM CASO_TRIAGE ct
            INNER JOIN PACIENTE p ON ct.id_paciente = p.id_paciente
            WHERE 1=1";
    
    $params = [];
    
    if ($estado) {
        $sql .= " AND ct.estado = ?";
        $params[] = $estado;
    }
    
    if ($nivel) {
        $sql .= " AND ct.nivel_triage_ia = ?";
        $params[] = $nivel;
    }
    
    if ($fecha) {
        $sql .= " AND DATE(ct.fecha_creacion) = ?";
        $params[] = $fecha;
    }
    
    $sql .= " ORDER BY 
                CASE ct.estado 
                    WHEN 'PENDIENTE' THEN 1 
                    WHEN 'VALIDADO' THEN 2 
                    ELSE 3 
                END,
                ct.nivel_triage_ia ASC,
                ct.fecha_creacion DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $casos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'success' => true,
        'casos' => $casos
    ];
}
?>
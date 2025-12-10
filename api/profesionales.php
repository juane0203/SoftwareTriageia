<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// Solo usuarios logueados pueden ver la lista
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'No autorizado']));
}

require_once 'config.php';
if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Buscar usuarios activos con rol 'profesional'
    $stmt = $db->prepare("SELECT usuario_id, nombre, especialidad FROM USUARIO WHERE rol = 'profesional' AND activo = 1 ORDER BY nombre ASC");
    $stmt->execute();
    $profesionales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'profesionales' => $profesionales]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
<?php
/**
 * Sistema de Auditoría
 * Registra todas las acciones en LOG_AUDITORIA
 */

require_once __DIR__ . '/../database/connection.php';

/**
 * Registra una acción en el log de auditoría
 * 
 * @param int|null $casoId ID del caso (opcional)
 * @param int|null $usuarioId ID del usuario (opcional)
 * @param string $accion Acción realizada
 * @param string|null $detalles Detalles en formato JSON (opcional)
 */
function logAudit($casoId, $usuarioId, $accion, $detalles = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Obtener IP y User Agent
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Si el user agent es muy largo, truncarlo
        if ($userAgent && strlen($userAgent) > 255) {
            $userAgent = substr($userAgent, 0, 255);
        }
        
        $stmt = $db->prepare("
            INSERT INTO LOG_AUDITORIA 
            (caso_id, usuario_id, accion, detalles, ip_address, user_agent, fecha_accion) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $casoId,
            $usuarioId,
            $accion,
            $detalles,
            $ipAddress,
            $userAgent
        ]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error en logAudit: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene el historial de auditoría de un caso
 */
function getAuditLog($casoId) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT 
                l.log_id,
                l.accion,
                l.detalles,
                l.fecha_accion,
                u.nombre as usuario_nombre,
                u.email as usuario_email
            FROM LOG_AUDITORIA l
            LEFT JOIN USUARIO u ON l.usuario_id = u.usuario_id
            WHERE l.caso_id = ?
            ORDER BY l.fecha_accion DESC
        ");
        
        $stmt->execute([$casoId]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error en getAuditLog: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene estadísticas de acciones por usuario
 */
function getUserStats($usuarioId, $fechaInicio = null, $fechaFin = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                accion,
                COUNT(*) as total
            FROM LOG_AUDITORIA
            WHERE usuario_id = ?
        ";
        
        $params = [$usuarioId];
        
        if ($fechaInicio) {
            $sql .= " AND fecha_accion >= ?";
            $params[] = $fechaInicio;
        }
        
        if ($fechaFin) {
            $sql .= " AND fecha_accion <= ?";
            $params[] = $fechaFin;
        }
        
        $sql .= " GROUP BY accion ORDER BY total DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Error en getUserStats: " . $e->getMessage());
        return [];
    }
}
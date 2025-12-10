<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'error' => 'No autorizado']));
}

// Incluir dependencias
if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

if (file_exists('mailer.php')) require_once 'mailer.php';
// IMPORTANTE: Incluir el sistema de auditoría
if (file_exists('audit.php')) require_once 'audit.php';

$action = $_GET['action'] ?? '';
$db = Database::getInstance()->getConnection();

try {
    switch ($action) {
        case 'estadisticas':
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'emergencia' OR nivel_triage_ia IN (1,2) THEN 1 ELSE 0 END) as emergencias,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'aprobado' AND DATE(fecha_validacion) = CURDATE() THEN 1 ELSE 0 END) as validados_hoy
                    FROM CASO_TRIAGE";
            $stmt = $db->query($sql);
            echo json_encode(['success' => true, 'stats' => $stmt->fetch(PDO::FETCH_ASSOC)]);
            break;

        case 'listar':
            $estado = $_GET['estado'] ?? '';
            $nivel = $_GET['nivel'] ?? '';
            $fecha = $_GET['fecha'] ?? '';
            
            $sql = "SELECT c.caso_id as id_caso, c.fecha_creacion, c.nivel_triage_ia, c.sintomas, c.estado,
                           p.nombre, p.apellido, p.edad, p.sexo, p.telefono,
                           (SELECT esta_embarazada FROM SIGNOS_VITALES sv WHERE sv.caso_id = c.caso_id LIMIT 1) as embarazo
                    FROM CASO_TRIAGE c
                    JOIN PACIENTE p ON c.paciente_id = p.paciente_id
                    WHERE 1=1";
            
            $params = [];
            if ($estado) { $sql .= " AND c.estado = ?"; $params[] = $estado; }
            if ($nivel) { $sql .= " AND c.nivel_triage_ia = ?"; $params[] = $nivel; }
            if ($fecha) { $sql .= " AND DATE(c.fecha_creacion) = ?"; $params[] = $fecha; }
            
            $sql .= " ORDER BY c.fecha_creacion DESC LIMIT 50";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success' => true, 'casos' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        case 'detalle':
            $id = $_GET['id'] ?? 0;
            $sql = "SELECT c.*, p.nombre, p.apellido, p.edad, p.sexo, p.telefono, p.email, p.direccion,
                           sv.tiene_fiebre, sv.temperatura, sv.tiene_dolor, sv.nivel_dolor, 
                           sv.tiene_dificultad_respiratoria, sv.tiene_vomito, sv.tiene_perdida_consciencia, sv.esta_embarazada,
                           u_val.nombre as validador_nombre,
                           u_prof.nombre as profesional_nombre,
                           u_prof.especialidad as profesional_especialidad
                    FROM CASO_TRIAGE c
                    JOIN PACIENTE p ON c.paciente_id = p.paciente_id
                    LEFT JOIN SIGNOS_VITALES sv ON c.caso_id = sv.caso_id
                    LEFT JOIN USUARIO u_val ON c.usuario_validador_id = u_val.usuario_id
                    LEFT JOIN USUARIO u_prof ON c.profesional_atencion_id = u_prof.usuario_id
                    WHERE c.caso_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            $caso = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$caso) throw new Exception("Caso no encontrado");

            // TRAER HISTORIAL DE AUDITORÍA
            $historial = [];
            if (function_exists('getAuditLog')) {
                $historial = getAuditLog($id);
            }

            echo json_encode(['success' => true, 'caso' => $caso, 'historial' => $historial]);
            break;
            
        case 'validar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception("Método inválido");
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['caso_id']) || empty($input['estado'])) throw new Exception("Datos incompletos");

            $esAprobado = ($input['estado'] === 'aprobado');
            
            $profesional_atencion = $esAprobado ? ($input['profesional_id'] ?? null) : null;
            $fecha_atencion = $esAprobado ? ($input['fecha_atencion'] ?? null) : null;
            $hora_atencion = $esAprobado ? ($input['hora_atencion'] ?? null) : null;

            $sql = "UPDATE CASO_TRIAGE SET 
                    estado = ?, 
                    usuario_validador_id = ?, 
                    fecha_validacion = NOW(),
                    justificacion_final = ?,
                    nivel_triage_final = ?,
                    motivo_rechazo = ?,
                    profesional_atencion_id = ?,
                    fecha_atencion = ?,
                    hora_atencion = ?
                    WHERE caso_id = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $input['estado'], 
                $_SESSION['usuario_id'], 
                $input['justificacion'] ?? null,
                $input['nivel_final'] ?? null,
                $input['motivo_rechazo'] ?? null,
                $profesional_atencion,
                $fecha_atencion,
                $hora_atencion,
                $input['caso_id']
            ]);

            // 1. REGISTRO DE AUDITORÍA (RF-17)
            if (function_exists('logAudit')) {
                $accion = $esAprobado ? 'APROBAR_CASO' : 'RECHAZAR_CASO';
                $detalles = json_encode([
                    'nivel_final' => $input['nivel_final'] ?? null,
                    'motivo' => $input['motivo_rechazo'] ?? null,
                    'medico_asignado' => $profesional_atencion
                ]);
                logAudit($input['caso_id'], $_SESSION['usuario_id'], $accion, $detalles);
            }

            // 2. ENVIAR NOTIFICACIÓN
            if (function_exists('enviarNotificacion')) {
                $stmtInfo = $db->prepare("
                    SELECT p.email, p.nombre, u.nombre as medico 
                    FROM CASO_TRIAGE c
                    JOIN PACIENTE p ON c.paciente_id = p.paciente_id
                    LEFT JOIN USUARIO u ON u.usuario_id = ?
                    WHERE c.caso_id = ?
                ");
                $stmtInfo->execute([$profesional_atencion, $input['caso_id']]);
                $info = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                if ($info && $info['email']) {
                    if ($esAprobado) {
                        $msg = generarPlantillaAprobacion($info['nombre'], $info['medico'] ?? 'Por asignar', $fecha_atencion, $hora_atencion);
                        $enviado = enviarNotificacion($info['email'], "Cita Confirmada - ADOM", $msg);
                    } else {
                        $msg = generarPlantillaRechazo($info['nombre'], $input['motivo_rechazo']);
                        $enviado = enviarNotificacion($info['email'], "Actualización de Solicitud - ADOM", $msg);
                    }
                    
                    // Auditar el envío de correo
                    if (function_exists('logAudit')) {
                        logAudit($input['caso_id'], null, 'ENVIO_EMAIL', "Destino: {$info['email']} - Resultado: " . ($enviado ? 'Exito' : 'Fallo'));
                    }
                }
            }
            
            echo json_encode(['success' => true]);
            break;

        // Reportes y Default igual que antes...
        case 'reportes':
            $sqlTotal = "SELECT COUNT(*) as total FROM CASO_TRIAGE WHERE estado IN ('aprobado', 'rechazado')";
            $stmt = $db->query($sqlTotal);
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            $sqlMatch = "SELECT COUNT(*) as coincidencias FROM CASO_TRIAGE 
                         WHERE estado = 'aprobado' AND nivel_triage_ia = nivel_triage_final";
            $stmt = $db->query($sqlMatch);
            $coincidencias = $stmt->fetch(PDO::FETCH_ASSOC)['coincidencias'];

            $sqlMismatch = "SELECT COUNT(*) as correcciones FROM CASO_TRIAGE 
                            WHERE estado = 'aprobado' AND nivel_triage_ia != nivel_triage_final";
            $stmt = $db->query($sqlMismatch);
            $correcciones = $stmt->fetch(PDO::FETCH_ASSOC)['correcciones'];

            $sqlRechazados = "SELECT COUNT(*) as rechazados FROM CASO_TRIAGE WHERE estado = 'rechazado'";
            $stmt = $db->query($sqlRechazados);
            $rechazados = $stmt->fetch(PDO::FETCH_ASSOC)['rechazados'];

            $sqlEmergencias = "SELECT COUNT(*) as emergencias FROM CASO_TRIAGE WHERE nivel_triage_ia IN (1,2)";
            $stmt = $db->query($sqlEmergencias);
            $emergencias = $stmt->fetch(PDO::FETCH_ASSOC)['emergencias'];

            $precision = ($total > 0) ? round(($coincidencias / $total) * 100, 1) : 0;

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_evaluados' => $total,
                    'coincidencias' => $coincidencias,
                    'correcciones' => $correcciones,
                    'rechazados' => $rechazados,
                    'emergencias_detectadas' => $emergencias,
                    'precision_ia' => $precision
                ]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Acción desconocida']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
<?php
// api/triage.php - VERSIÓN FINAL ESTABLE
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Limpieza de buffer de salida (Vital para evitar errores de sintaxis)
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Carga de configuracion
require_once 'config.php';
if (file_exists('../database/connection.php')) require_once '../database/connection.php';
else require_once 'db.php';

ob_clean(); // Borrar cualquier eco anterior

// Validar API Key
if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY)) {
    http_response_code(500);
    echo json_encode(['error' => 'Falta configurar GEMINI_API_KEY en config.php']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) throw new Exception('Datos JSON inválidos');

    // --- PASO 1: REGLAS DE ORO (HARD RULES) ---
    // Detectar emergencias vitales obvias SIN depender de la IA
    
    $info = $data['additionalInfo'] ?? [];
    $dolor = intval($info['painLevel'] ?? 0);
    $nivelForzado = 0;
    $justificacion = "";

    // A. Dificultad Respiratoria = Nivel 2 mínimo
    if (!empty($info['hasBreathingDifficulty'])) {
        $nivelForzado = 2;
        $justificacion = "ALERTA: Dificultad respiratoria activa. Requiere valoración prioritaria.";
    }
    // B. Pérdida de Consciencia = Nivel 1
    elseif (!empty($info['hasConsciousnessLoss'])) {
        $nivelForzado = 1;
        $justificacion = "EMERGENCIA: Paciente con pérdida de consciencia.";
    }
    // C. Dolor Severo (>8) = Nivel 2
    elseif (!empty($info['hasPain']) && $dolor >= 9) {
        $nivelForzado = 2;
        $justificacion = "ALERTA: Dolor severo ($dolor/10). Requiere manejo urgente.";
    }

    // --- PASO 2: CONSULTA A GEMINI (IA) ---
    
    $responseIA = null;

    // Preparamos el prompt incluso si hay reglas de oro, para tener el análisis completo si se requiere
    $sintomas = $data['symptoms'];
    $signosTexto = "";
    if(!empty($info['hasFever'])) $signosTexto .= "Fiebre ({$info['temperature']}°C). ";
    if(!empty($info['hasPain'])) $signosTexto .= "Dolor nivel $dolor/10. ";
    if(!empty($info['hasVomiting'])) $signosTexto .= "Vómito. ";
    
    $prompt = "Actúa como Médico de Triage.
    PACIENTE: {$data['age']} años, Sexo: {$data['sex']}.
    SINTOMAS: \"$sintomas\"
    SIGNOS VITALES: $signosTexto

    TAREA: Clasifica la urgencia (Nivel 1 a 5) según protocolo Manchester.
    
    EJEMPLOS:
    - Dolor leve, gripa, trámites -> Nivel 4 o 5.
    - Dolor moderado, fiebre alta -> Nivel 3.
    - Dolor torácico, asfixia, dolor severo -> Nivel 1 o 2.

    IMPORTANTE: Responde SOLAMENTE un objeto JSON válido sin markdown.
    FORMATO:
    {
        \"level\": (numero entero 1-5),
        \"justification\": \"(texto breve)\",
        \"waitTime\": \"(tiempo estimado)\",
        \"action\": \"(recomendacion)\",
        \"alarms\": [\"signo1\", \"signo2\"]
    }";

    // Si no hay regla de oro activada, preguntamos a la IA
    if ($nivelForzado === 0) {
        try {
            $aiRaw = callGemini($prompt);
            $responseIA = cleanAndParseJSON($aiRaw);
        } catch (Exception $e) {
            error_log("Error Gemini: " . $e->getMessage());
            // Solo si falla la IA y no hay reglas de oro, usamos fallback
        }
    }

    // --- PASO 3: CONSOLIDACIÓN DE RESULTADO ---
    
    $resultadoFinal = [
        'level' => 3, // Default moderado
        'description' => 'Evaluación manual requerida.',
        'waitTime' => 'Según disponibilidad',
        'recommendation' => 'Valoración médica',
        'canUseService' => true,
        'warningSigns' => []
    ];

    if ($nivelForzado > 0) {
        // Prioridad: Reglas de Oro
        $resultadoFinal['level'] = $nivelForzado;
        $resultadoFinal['description'] = $justificacion;
        $resultadoFinal['waitTime'] = 'INMEDIATA';
        $resultadoFinal['recommendation'] = 'Atención Urgente / 123';
        $resultadoFinal['canUseService'] = false;
    } elseif ($responseIA) {
        // Prioridad 2: Inteligencia Artificial
        $resultadoFinal = $responseIA;
    } else {
        // Fallback si todo falla (pero no asumimos emergencia si los signos son leves)
        // Si dolor es bajo y no hay signos graves, bajamos el nivel del fallback
        if ($dolor < 5 && empty($info['hasBreathingDifficulty'])) {
             $resultadoFinal['level'] = 4;
             $resultadoFinal['description'] = "Análisis automático no disponible. Por síntomas leves, se asigna prioridad baja.";
        }
    }

    // --- PASO 4: GUARDAR EN BD ---
    guardarEnBD($data, $resultadoFinal);

    echo json_encode($resultadoFinal);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// --- FUNCIONES ---

function callGemini($prompt) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . GEMINI_API_KEY;
    $body = ['contents' => [['parts' => [['text' => $prompt]]]]];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_TIMEOUT => 10
    ]);
    $res = curl_exec($ch);
    if(curl_errno($ch)) throw new Exception(curl_error($ch));
    curl_close($ch);
    return json_decode($res, true);
}

function cleanAndParseJSON($aiResponse) {
    // Extraer texto
    $raw = $aiResponse['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // 1. Buscar el primer '{' y el último '}'
    $start = strpos($raw, '{');
    $end = strrpos($raw, '}');
    
    if ($start !== false && $end !== false) {
        $jsonStr = substr($raw, $start, $end - $start + 1);
        // 2. Decodificar
        $json = json_decode($jsonStr, true);
        
        if ($json && isset($json['level'])) {
            return [
                'level' => (int)$json['level'],
                'description' => $json['justification'] ?? 'Análisis IA',
                'waitTime' => $json['waitTime'] ?? 'N/A',
                'recommendation' => $json['action'] ?? 'Consultar',
                'canUseService' => ((int)$json['level'] >= 3),
                'warningSigns' => $json['alarms'] ?? []
            ];
        }
    }
    return null; // Fallo en lectura
}

function guardarEnBD($data, $res) {
    try {
        $db = Database::getInstance()->getConnection();
        
        // Paciente
        $pid = null;
        if(!empty($data['email'])) {
            $s = $db->prepare("SELECT paciente_id FROM PACIENTE WHERE email=? LIMIT 1");
            $s->execute([$data['email']]);
            $r = $s->fetch(PDO::FETCH_ASSOC);
            if($r) $pid = $r['paciente_id'];
        }
        
        if(!$pid) {
            $s = $db->prepare("INSERT INTO PACIENTE (nombre, apellido, edad, sexo, direccion, telefono, email) VALUES (?,?,?,?,?,?,?)");
            $s->execute([$data['nombre'], $data['apellido'], $data['age'], $data['sex'], $data['direccion'], $data['telefono'], $data['email']]);
            $pid = $db->lastInsertId();
        }

        // Caso
        $st = ($res['level'] <= 2) ? 'emergencia' : 'pendiente';
        $s = $db->prepare("INSERT INTO CASO_TRIAGE (paciente_id, sintomas, nivel_triage_ia, justificacion_ia, estado) VALUES (?,?,?,?,?)");
        $s->execute([$pid, $data['symptoms'], $res['level'], $res['description'], $st]);
        
        // Signos (Opcional)
        // ... codigo de signos similar al anterior ...
        
    } catch (Exception $e) {
        error_log("BD Error: ".$e->getMessage());
    }
}
?>
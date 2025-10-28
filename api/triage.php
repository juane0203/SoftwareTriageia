<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido']));
}

$data = json_decode(file_get_contents('php://input'), true);

try {
    $prompt = prepareGeminiPrompt($data);
    $triageResult = callGeminiAI($prompt);
    $response = processTriageResponse($triageResult, $data);
    logTriage($data, $response);
    
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el procesamiento del triage',
        'details' => $e->getMessage()
    ]);
}

function callGeminiAI($prompt) {
    try {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.01,
                'topK' => 1,
                'topP' => 0.01
            ]
        ];

        $ch = curl_init($url . '?key=' . GEMINI_API_KEY);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Error en llamada a Gemini AI: ' . $error);
        }

        return json_decode($response, true);
    } catch (Exception $e) {
        error_log("Error en callGeminiAI: " . $e->getMessage());
        throw $e;
    }
}

function prepareGeminiPrompt($data) {
    $prompt = "Como médico experto, analiza este caso y determina su nivel de triage.

PACIENTE:
- Edad: {$data['age']}
- Sexo: {$data['sex']}
" . ($data['sex'] === 'F' ? "- Embarazo: " . ($data['isPregnant'] ? 'Sí' : 'No') . "\n" : "") . "
- Síntomas: {$data['symptoms']}

INFORMACIÓN ADICIONAL:
" . formatAdditionalInfo($data['additionalInfo'] ?? []) . "

Basado en la gravedad:
NIVEL 1: Emergencia vital - atención hospitalaria inmediata
NIVEL 2: Urgencia grave - atención hospitalaria
NIVEL 3: Urgencia moderada - atención domiciliaria posible
NIVEL 4-5: Condición leve - atención domiciliaria o telemedicina

RESPONDE EXACTAMENTE EN ESTE FORMATO:
NIVEL: [Número 1-5]
JUSTIFICACIÓN: [Tu análisis médico detallado]
TIEMPO: [Urgencia de atención]
ACCIÓN: [Instrucciones específicas]
ALARMA: [Lista cada signo de alarma en una nueva línea con guiones]";

    return $prompt;
}

function processTriageResponse($aiResponse, $originalData) {
    if (!isset($aiResponse['candidates'][0]['content']['parts'][0]['text'])) {
        return getDefaultResponse($originalData);
    }

    $content = $aiResponse['candidates'][0]['content']['parts'][0]['text'];
    error_log("Respuesta de Gemini: " . $content);

    // Extraer nivel
    preg_match('/NIVEL:\s*(\d)/i', $content, $levelMatch);
    $level = isset($levelMatch[1]) ? (int)$levelMatch[1] : determineDefaultLevel($originalData);

    // Extraer justificación
    preg_match('/JUSTIFICACIÓN:\s*(.*?)(?=TIEMPO:|$)/is', $content, $justMatch);
    $description = isset($justMatch[1]) ? trim($justMatch[1]) : "";

    // Extraer tiempo
    preg_match('/TIEMPO:\s*(.*?)(?=ACCIÓN:|$)/is', $content, $timeMatch);
    $customWaitTime = isset($timeMatch[1]) ? trim($timeMatch[1]) : "";

    // Mejorar extracción de signos de alarma
    preg_match('/ALARMA:\s*(.*?)(?=\n|$)/is', $content, $alarmaMatch);
    $warningSigns = [];
    if (isset($alarmaMatch[1])) {
        // Dividir por líneas y/o comas
        $signs = preg_split('/[\n,]+/', $alarmaMatch[1]);
        foreach ($signs as $sign) {
            $sign = trim($sign);
            if (strpos($sign, '-') === 0) {
                $sign = trim(substr($sign, 1));
            }
            if (!empty($sign)) {
                $warningSigns[] = $sign;
            }
        }
    }

    if (empty($warningSigns)) {
        $warningSigns = getDefaultWarningSigns($level);
    }

    return [
        'level' => $level,
        'description' => $description,
        'waitTime' => $customWaitTime ?: getWaitTime($level),
        'recommendation' => getRecommendation($level),
        'canUseService' => $level >= 3,
        'warningSigns' => $warningSigns
    ];
}

function getDefaultResponse($data) {
    $level = determineDefaultLevel($data);
    return [
        'level' => $level,
        'description' => 'Evaluación por defecto basada en síntomas presentados',
        'waitTime' => getWaitTime($level),
        'recommendation' => getRecommendation($level),
        'canUseService' => $level >= 3,
        'warningSigns' => getDefaultWarningSigns($level)
    ];
}

function getWaitTime($level) {
    $waitTimes = [
        1 => 'Inmediato',
        2 => 'Menos de 15 minutos',
        3 => '30-60 minutos',
        4 => '1-2 horas',
        5 => '2-4 horas'
    ];
    return $waitTimes[$level] ?? 'No determinado';
}

function getRecommendation($level) {
    $recommendations = [
        1 => 'LLAMAR 123 INMEDIATAMENTE',
        2 => 'Acudir a urgencias inmediatamente',
        3 => 'Consulta médica domiciliaria en las próximas horas',
        4 => 'Consulta médica domiciliaria o telemedicina',
        5 => 'Telemedicina o consulta programada'
    ];
    return $recommendations[$level] ?? 'Consultar con un profesional médico';
}

function getDefaultWarningSigns($level) {
    $defaultSigns = [
        1 => ['Buscar atención médica inmediata', 'No esperar, llamar 123'],
        2 => ['Acudir a urgencias si los síntomas empeoran', 'Monitorear signos vitales'],
        3 => ['Estar atento a cambios en los síntomas', 'Seguir recomendaciones médicas'],
        4 => ['Monitorear evolución de síntomas', 'Contactar si hay cambios significativos'],
        5 => ['Seguir indicaciones de cuidado en casa', 'Consultar si hay cambios']
    ];
    return $defaultSigns[$level] ?? ['Seguir indicaciones médicas generales'];
}

function formatAdditionalInfo($additionalInfo) {
    if (empty($additionalInfo)) {
        return "Sin signos adicionales reportados";
    }

    $info = [];
    
    if (!empty($additionalInfo['hasFever'])) {
        $temp = !empty($additionalInfo['temperature']) ? " ({$additionalInfo['temperature']}°C)" : "";
        $info[] = "- Fiebre" . $temp;
    }
    
    if (!empty($additionalInfo['hasPain'])) {
        $level = !empty($additionalInfo['painLevel']) ? " (Nivel {$additionalInfo['painLevel']}/10)" : "";
        $info[] = "- Dolor" . $level;
    }
    
    if (!empty($additionalInfo['hasBreathingDifficulty'])) {
        $info[] = "- Dificultad para respirar";
    }
    
    if (!empty($additionalInfo['hasVomiting'])) {
        $info[] = "- Vómito";
    }
    
    if (!empty($additionalInfo['hasConsciousnessLoss'])) {
        $info[] = "- Pérdida de consciencia";
    }
    
    return implode("\n", $info);
}

function logTriage($data, $result) {
    if (!defined('LOG_PATH')) return;
    
    try {
        $logEntry = date('Y-m-d H:i:s') . " | " .
                    "Nivel: {$result['level']} | " .
                    "Edad: {$data['age']} | " .
                    "Sexo: {$data['sex']} | " .
                    "Síntomas: {$data['symptoms']}\n";
        
        file_put_contents(LOG_PATH . '/triage_log.txt', $logEntry, FILE_APPEND);
    } catch (Exception $e) {
        error_log("Error al escribir en el log de triage: " . $e->getMessage());
    }
}

function determineDefaultLevel($data) {
    if (isset($data['additionalInfo'])) {
        if (!empty($data['additionalInfo']['hasConsciousnessLoss'])) return 1;
        if (!empty($data['additionalInfo']['hasBreathingDifficulty'])) return 2;
        if (!empty($data['additionalInfo']['hasFever']) && 
            !empty($data['additionalInfo']['temperature']) && 
            $data['additionalInfo']['temperature'] > 39) return 2;
    }
    return 3;
}
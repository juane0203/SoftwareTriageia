<?php
// Prevenir acceso directo
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Acceso no permitido');
}

define('DB_HOST', 'localhost');
define('DB_USER', 'opcriver_triage');
define('DB_PASS', ';%9v#zZvtiU1B0Us');
define('DB_NAME', 'opcriver_triagedb');

// Configuración básica
// Reemplazar la API key de Mistral por Gemini
define('GEMINI_API_KEY', 'AIzaSyAV3lfLeAf3xBDpl2TKK-sbMBXB7gjdO7U');
define('LOG_PATH', dirname(__DIR__) . '/logs');

// Zona horaria
date_default_timezone_set('America/Bogota');

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/error.log');

// Headers de seguridad (ELIMINADO EL HEADER JSON QUE ROMPÍA EL HTML)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Niveles de triage
define('TRIAGE_LEVELS', [
    1 => [
        'name' => 'Emergencia',
        'description' => 'Emergencia que requiere atención inmediata',
        'waitTime' => 'Inmediata',
        'canUseService' => false,
        'requiresAmbulance' => true
    ],
    2 => [
        'name' => 'Urgencia Grave',
        'description' => 'Urgencia que requiere atención pronta',
        'waitTime' => 'Menos de 1 hora',
        'canUseService' => false,
        'requiresAmbulance' => false
    ],
    3 => [
        'name' => 'Urgencia Moderada',
        'description' => 'Condición que requiere atención pero no es crítica',
        'waitTime' => '2-3 horas',
        'canUseService' => true,
        'requiresAmbulance' => false
    ],
    4 => [
        'name' => 'Urgencia Menor',
        'description' => 'Condición que puede esperar',
        'waitTime' => '4-6 horas',
        'canUseService' => true,
        'requiresAmbulance' => false
    ],
    5 => [
        'name' => 'No Urgente',
        'description' => 'Atención puede ser programada',
        'waitTime' => '24 horas',
        'canUseService' => true,
        'requiresAmbulance' => false
    ]
]);
<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarSesion() {
    // Verificar si hay sesión activa
    if (!isset($_SESSION['usuario_id'])) {
        // Si es una petición AJAX, devolver error JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Sesión no válida',
                'redirect' => '/triageadom/panel/login.php'
            ]);
            exit();
        }
        
        // Si es petición normal, redirigir a login
        header('Location: /triageadom/panel/login.php');
        exit();
    }
    
    // Verificar timeout (30 minutos)
    if (isset($_SESSION['ultimo_acceso'])) {
        $inactivo = time() - $_SESSION['ultimo_acceso'];
        if ($inactivo > 1800) { // 30 minutos
            session_unset();
            session_destroy();
            header('Location: /triageadom/panel/login.php?timeout=1');
            exit();
        }
    }
    
    // Actualizar timestamp
    $_SESSION['ultimo_acceso'] = time();
    
    return true;
}

function obtenerUsuarioActual() {
    return $_SESSION['usuario'] ?? null;
}
<?php
session_start();

// Cargar dependencias
if (file_exists(__DIR__ . '/../database/connection.php')) {
    require_once __DIR__ . '/../database/connection.php';
} elseif (file_exists(__DIR__ . '/db.php')) {
    require_once __DIR__ . '/db.php';
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Método no permitido']));
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
}

function handleLogin() {
    try {
        if (empty($_POST['email']) || empty($_POST['password'])) {
            throw new Exception('Email y contraseña son requeridos');
        }
        
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new Exception('Email no válido');
        }
        
        $password = $_POST['password'];
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT usuario_id, nombre, email, password_hash, rol, activo 
            FROM USUARIO 
            WHERE email = ? AND activo = 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception('Credenciales incorrectas');
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            throw new Exception('Credenciales incorrectas');
        }
        
        session_regenerate_id(true);
        
        $_SESSION['usuario_id'] = $user['usuario_id'];
        $_SESSION['usuario'] = [
            'id' => $user['usuario_id'],
            'nombre' => $user['nombre'],
            'apellido' => '',
            'email' => $user['email'],
            'rol' => $user['rol']
        ];
        $_SESSION['ultimo_acceso'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso'
        ]);
        
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

function handleLogout() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada'
    ]);
}
?>
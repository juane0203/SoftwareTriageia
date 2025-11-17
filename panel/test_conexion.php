<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h3>Probando conexiones...</h3>";

// 1. Verificar config.php
echo "1. Verificando config.php... ";
if (file_exists('../api/config.php')) {
    require_once '../api/config.php';
    echo "✅ OK<br>";
} else {
    echo "❌ NO EXISTE<br>";
}

// 2. Verificar session.php
echo "2. Verificando session.php... ";
if (file_exists('../api/middleware/session.php')) {
    echo "✅ OK<br>";
} else {
    echo "❌ NO EXISTE<br>";
}

// 3. Verificar db.php
echo "3. Verificando db.php... ";
if (file_exists('../api/db.php')) {
    require_once '../api/db.php';
    echo "✅ OK<br>";
} else {
    echo "❌ NO EXISTE<br>";
}

// 4. Verificar constantes DB
echo "4. Verificando constantes de DB... ";
if (defined('DB_HOST')) {
    echo "✅ DB_HOST: " . DB_HOST . "<br>";
    echo "✅ DB_NAME: " . DB_NAME . "<br>";
    echo "✅ DB_USER: " . DB_USER . "<br>";
} else {
    echo "❌ No definidas en config.php<br>";
}

// 5. Probar conexión
echo "5. Probando conexión a BD... ";
try {
    $db = Database::getInstance()->getConnection();
    echo "✅ CONEXIÓN EXITOSA<br>";
    
    // 6. Verificar tabla CASO_TRIAGE
    $stmt = $db->query("SHOW TABLES LIKE 'CASO_TRIAGE'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabla CASO_TRIAGE existe<br>";
    } else {
        echo "❌ Tabla CASO_TRIAGE NO existe<br>";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
}
?>
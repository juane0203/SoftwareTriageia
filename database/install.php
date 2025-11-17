<?php
/**
 * Script de Instalación de Base de Datos
 * Ejecutar UNA SOLA VEZ desde el navegador
 */

// Configuración
define('DB_HOST', 'localhost');
define('DB_USER', 'opcriver_triage');
define('DB_PASS', ';%9v#zZvtiU1B0Us');
define('DB_NAME', 'opcriver_triagedb');

echo "<h1>Instalación del Sistema de Triage ADOM</h1>";
echo "<pre>";

try {
    // Paso 1: Crear base de datos
    echo "✓ Conectando al servidor MySQL...\n";
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Creando base de datos '" . DB_NAME . "'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                DEFAULT CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci");
    
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Paso 2: Ejecutar schema.sql
    echo "✓ Creando tablas...\n";
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);
    
    // Paso 3: Ejecutar seed.sql
    echo "✓ Insertando datos iniciales...\n";
    $seed = file_get_contents(__DIR__ . '/seed.sql');
    $pdo->exec($seed);
    
    echo "\n";
    echo "════════════════════════════════════════════\n";
    echo "✅ INSTALACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "════════════════════════════════════════════\n\n";
    echo "📊 Base de datos: " . DB_NAME . "\n";
    echo "👤 Usuarios creados: 4 profesionales\n";
    echo "🔐 Contraseña de prueba: admin123\n\n";
    echo "Usuarios disponibles:\n";
    echo "  • juan.perez@adom.com\n";
    echo "  • maria.gonzalez@adom.com\n";
    echo "  • carlos.ramirez@adom.com\n";
    echo "  • admin@adom.com\n\n";
    echo "⚠️  IMPORTANTE: Por seguridad, elimina este archivo después de la instalación\n";
    echo "════════════════════════════════════════════\n";
    
} catch(PDOException $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
```

---

## 🎯 **INSTRUCCIONES DE INSTALACIÓN**

1. **Sube estos 4 archivos a tu hosting:**
```
   /database/
   ├── schema.sql
   ├── seed.sql
   ├── connection.php
   └── install.php
```

2. **Ejecuta desde el navegador:**
```
   https://tu-dominio.com/database/install.php
```

3. **Verifica que veas:**
```
   ✅ INSTALACIÓN COMPLETADA EXITOSAMENTE
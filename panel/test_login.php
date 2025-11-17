<?php
session_start();

echo "<h3>Test de Login</h3>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../api/config.php';
    require_once '../api/db.php';
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT id_usuario, nombre, apellido, email, password, rol 
            FROM USUARIO WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($password, $usuario['password'])) {
        // Guardar en sesión
        $_SESSION['usuario_id'] = $usuario['id_usuario'];
        $_SESSION['usuario'] = [
            'id' => $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'email' => $usuario['email'],
            'rol' => $usuario['rol']
        ];
        
        echo "<div style='background: green; color: white; padding: 20px;'>";
        echo "✅ LOGIN EXITOSO<br>";
        echo "Session ID: " . session_id() . "<br>";
        echo "Usuario ID: " . $_SESSION['usuario_id'] . "<br>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        echo "<a href='dashboard.php' style='color: white;'>IR AL DASHBOARD</a>";
        echo "</div>";
    } else {
        echo "<div style='background: red; color: white; padding: 20px;'>";
        echo "❌ Credenciales incorrectas";
        echo "</div>";
    }
} else {
?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">LOGIN</button>
    </form>
<?php
}
?>
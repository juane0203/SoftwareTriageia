<?php
session_start();

// Si ya está autenticado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Triage ADOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #2a4693 0%, #4649d6 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Sistema de Triage ADOM</h1>
            <p class="text-white text-opacity-90">Panel de Profesionales</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Iniciar Sesión</h2>
            
            <!-- Mensaje de error -->
            <div id="errorMessage" class="hidden mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <p id="errorText"></p>
            </div>

            <!-- Formulario -->
            <form id="loginForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="usuario@adom.com"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                </div>

                <button 
                    type="submit" 
                    id="loginBtn"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                >
                    Ingresar
                </button>
            </form>

            <!-- Info de prueba -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-800 font-medium mb-2">👤 Usuarios de prueba:</p>
                <p class="text-xs text-blue-700">• juan.perez@adom.com</p>
                <p class="text-xs text-blue-700">• maria.gonzalez@adom.com</p>
                <p class="text-xs text-blue-700 mt-2">🔑 Contraseña: <strong>admin123</strong></p>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-white text-sm mt-6">
            © 2025 IPS ADOM - Sistema de Triage Médico
        </p>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const errorMessage = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Deshabilitar botón
            loginBtn.disabled = true;
            loginBtn.textContent = 'Ingresando...';
            
            // Ocultar errores previos
            errorMessage.classList.add('hidden');

            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', document.getElementById('email').value);
            formData.append('password', document.getElementById('password').value);

            try {
                const response = await fetch('../api/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Login exitoso - redirigir
                    window.location.href = 'dashboard.php';
                } else {
                    // Mostrar error
                    errorText.textContent = data.error || 'Error al iniciar sesión';
                    errorMessage.classList.remove('hidden');
                }

            } catch (error) {
                errorText.textContent = 'Error de conexión. Intente nuevamente.';
                errorMessage.classList.remove('hidden');
            } finally {
                // Rehabilitar botón
                loginBtn.disabled = false;
                loginBtn.textContent = 'Ingresar';
            }
        });
    </script>
</body>
</html>
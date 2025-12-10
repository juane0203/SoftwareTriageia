<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de IA - ADOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-chart-pie text-purple-600 text-2xl mr-3"></i>
                <h1 class="text-xl font-bold text-gray-800">Métricas de Desempeño IA</h1>
            </div>
            <a href="dashboard.php" class="text-gray-600 hover:text-purple-600 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm font-bold uppercase">Precisión Global IA</p>
                <div class="flex items-end mt-2">
                    <h2 class="text-4xl font-bold text-gray-800" id="kpiPrecision">0%</h2>
                    <span class="text-green-500 ml-2 mb-1 text-sm font-medium"><i class="fas fa-check"></i> Meta > 70%</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">Coincidencia entre IA y Profesional</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                <p class="text-gray-500 text-sm font-bold uppercase">Correcciones Humanas</p>
                <div class="flex items-end mt-2">
                    <h2 class="text-4xl font-bold text-gray-800" id="kpiCorrecciones">0</h2>
                    <span class="text-gray-500 ml-2 mb-1 text-sm">Casos</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">Nivel final diferente al sugerido</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
                <p class="text-gray-500 text-sm font-bold uppercase">Emergencias Detectadas</p>
                <div class="flex items-end mt-2">
                    <h2 class="text-4xl font-bold text-gray-800" id="kpiEmergencias">0</h2>
                    <span class="text-red-500 ml-2 mb-1 text-sm font-medium">Nivel 1-2</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">Casos críticos filtrados correctamente</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="font-bold text-gray-700 mb-4">Distribución de Resultados</h3>
                <canvas id="chartResultados"></canvas>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm">
                <h3 class="font-bold text-gray-700 mb-4">Comparativa de Niveles (IA vs Real)</h3>
                <div class="flex items-center justify-center h-64 text-gray-400 border-2 border-dashed rounded-lg bg-gray-50">
                    <div class="text-center">
                        <i class="fas fa-chart-bar text-4xl mb-2"></i>
                        <p>Datos insuficientes para comparativa detallada</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', cargarReportes);

        async function cargarReportes() {
            try {
                const res = await fetch('../api/casos.php?action=reportes');
                const response = await res.json();
                
                if(response.success) {
                    const data = response.data;
                    
                    // Actualizar KPIs
                    document.getElementById('kpiPrecision').textContent = data.precision_ia + '%';
                    document.getElementById('kpiCorrecciones').textContent = data.correcciones;
                    document.getElementById('kpiEmergencias').textContent = data.emergencias_detectadas;

                    // Renderizar Gráfica
                    const ctx = document.getElementById('chartResultados').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Aciertos (Coincidencia)', 'Correcciones (Diferencias)', 'Rechazados'],
                            datasets: [{
                                data: [data.coincidencias, data.correcciones, data.rechazados],
                                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' }
                            }
                        }
                    });
                }
            } catch(e) {
                console.error(e);
                alert('Error cargando reportes');
            }
        }
    </script>
</body>
</html>
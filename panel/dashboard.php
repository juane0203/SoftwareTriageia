<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Profesional - ADOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* --- ESTILOS DE ESTADO (Colores Reales) --- */
        .bg-estado-pendiente { background-color: #fef3c7; color: #92400e; border: 1px solid #eab308; } /* Amarillo */
        .bg-estado-aprobado { background-color: #dcfce7; color: #166534; border: 1px solid #22c55e; } /* Verde */
        .bg-estado-rechazado { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; } /* Rojo */
        .bg-estado-emergencia { background-color: #fca5a5; color: #7f1d1d; border: 1px solid #b91c1c; animation: pulse 2s infinite; } /* Rojo Alerta */

        /* --- NIVELES DE TRIAGE --- */
        .level-1 { background-color: #fee2e2; color: #991b1b; }
        .level-2 { background-color: #ffedd5; color: #9a3412; }
        .level-3 { background-color: #fef9c3; color: #854d0e; }
        .level-4 { background-color: #dcfce7; color: #166534; }
        .level-5 { background-color: #dbeafe; color: #1e40af; }

        /* Badge General */
        .status-badge { 
            display: inline-flex; 
            align-items: center; 
            padding: 0.25rem 0.75rem; 
            border-radius: 9999px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
        }
        
        /* Animaciones */
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        
        @keyframes pulse { 
            0% { opacity: 1; } 
            50% { opacity: .7; } 
            100% { opacity: 1; } 
        }
        
        /* Fade In para la tabla */
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    
<nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex items-center mr-8">
                        <div class="bg-blue-600 p-2 rounded-lg mr-3">
                            <i class="fas fa-stethoscope text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-800 leading-none">Triage ADOM</h1>
                            <span class="text-xs text-gray-500 font-medium">Panel de Control IA</span>
                        </div>
                    </div>

                    <div class="hidden md:flex space-x-2">
                        <a href="dashboard.php" class="px-4 py-2 rounded-lg text-sm font-bold bg-blue-50 text-blue-700 flex items-center">
                            <i class="fas fa-columns mr-2"></i> Tablero
                        </a>
                        <a href="agenda.php" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 flex items-center transition-colors">
                            <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> Agenda
                        </a>
                        <a href="reportes.php" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 flex items-center transition-colors">
                            <i class="fas fa-chart-pie mr-2 text-purple-500"></i> Reportes IA
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-6">
                    <div class="text-right hidden sm:block border-r pr-6 border-gray-200">
                        <p class="text-sm font-bold text-gray-700"><?php echo htmlspecialchars($usuario['nombre']); ?></p>
                        <p class="text-xs text-blue-600 font-semibold uppercase"><?php echo htmlspecialchars($usuario['rol']); ?></p>
                    </div>
                    <button onclick="logout()" class="group flex items-center text-gray-500 hover:text-red-600 transition-colors" title="Cerrar Sesión">
                        <span class="text-sm font-medium mr-2 group-hover:text-red-600 hidden sm:block">Salir</span>
                        <i class="fas fa-sign-out-alt text-xl bg-gray-100 p-2 rounded-full group-hover:bg-red-50"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Emergencias</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1" id="stat-emergencias">0</p>
                    </div>
                    <div class="bg-red-50 p-3 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-400 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pendientes</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1" id="stat-pendientes">0</p>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded-lg">
                        <i class="fas fa-clock text-yellow-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Aprobados Hoy</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1" id="stat-validados">0</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Casos</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1" id="stat-total">0</p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100">
            <div class="flex items-center mb-4">
                <i class="fas fa-filter text-gray-400 mr-2"></i>
                <h2 class="text-sm font-bold text-gray-700 uppercase">Filtros de Búsqueda</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Estado del Caso</label>
                    <div class="relative">
                        <select id="filterEstado" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 pl-3 pr-10 py-2 text-sm appearance-none border bg-white">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" selected>Pendientes</option>
                            <option value="aprobado">Aprobados / Validados</option>
                            <option value="rechazado">Rechazados</option>
                            <option value="emergencia">Emergencias</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Nivel de Triage</label>
                    <div class="relative">
                        <select id="filterNivel" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 pl-3 pr-10 py-2 text-sm appearance-none border bg-white">
                            <option value="">Todos los niveles</option>
                            <option value="1">Nivel 1 - Emergencia</option>
                            <option value="2">Nivel 2 - Urgencia Grave</option>
                            <option value="3">Nivel 3 - Urgencia Moderada</option>
                            <option value="4">Nivel 4 - Urgencia Menor</option>
                            <option value="5">Nivel 5 - No Urgente</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">Fecha Solicitud</label>
                    <input type="date" id="filterFecha" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm border">
                </div>
                <div class="flex items-end">
                    <button onclick="cargarCasos()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all shadow-md font-bold text-sm flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-list-ul text-blue-500 mr-2"></i> Listado de Pacientes
                </h2>
                <button onclick="cargarCasos()" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center transition-colors">
                    <i class="fas fa-sync-alt mr-1"></i> Actualizar
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Perfil</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">IA Nivel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Síntomas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCasos" class="bg-white divide-y divide-gray-200 fade-in">
                        </tbody>
                </table>
            </div>
            
            <div id="noCases" class="hidden px-6 py-16 text-center">
                <div class="bg-gray-50 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-inbox text-gray-300 text-3xl"></i>
                </div>
                <h3 class="text-gray-900 font-medium text-lg">Sin resultados</h3>
                <p class="text-gray-500 text-sm mt-1">No se encontraron casos con los filtros seleccionados.</p>
                <button onclick="limpiarFiltros()" class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium">Limpiar filtros</button>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 py-4 text-center text-xs text-gray-400">
            &copy; 2025 Triage ADOM - Sistema Inteligente de Soporte a la Decisión Clínica
        </div>
    </footer>

    <script>
        // Ejecutar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            cargarEstadisticas();
            cargarCasos();
            // Auto-refresh cada 30 segundos
            setInterval(cargarCasos, 30000);
            setInterval(cargarEstadisticas, 30000);
        });

        // Función para cargar estadísticas
        async function cargarEstadisticas() {
            try {
                const response = await fetch('../api/casos.php?action=estadisticas');
                const data = await response.json();
                if (data.success) {
                    document.getElementById('stat-emergencias').textContent = data.stats.emergencias || 0;
                    document.getElementById('stat-pendientes').textContent = data.stats.pendientes || 0;
                    document.getElementById('stat-validados').textContent = data.stats.validados_hoy || 0;
                    document.getElementById('stat-total').textContent = data.stats.total || 0;
                }
            } catch (error) { console.error('Error stats:', error); }
        }

        // Función para cargar tabla de casos
        async function cargarCasos() {
            const estado = document.getElementById('filterEstado').value;
            const nivel = document.getElementById('filterNivel').value;
            const fecha = document.getElementById('filterFecha').value;
            
            const params = new URLSearchParams({
                action: 'listar',
                estado: estado,
                nivel: nivel,
                fecha: fecha
            });

            try {
                const response = await fetch('../api/casos.php?' + params);
                const data = await response.json();
                
                const tbody = document.getElementById('tablaCasos');
                const noCases = document.getElementById('noCases');
                
                if (!data.success || data.casos.length === 0) {
                    tbody.innerHTML = '';
                    noCases.classList.remove('hidden');
                    return;
                }
                
                noCases.classList.add('hidden');
                tbody.innerHTML = data.casos.map(c => renderFila(c)).join('');
                
            } catch (error) { console.error('Error listado:', error); }
        }

        // Renderizar cada fila
        function renderFila(c) {
            // Formatear fecha
            const fecha = new Date(c.fecha_creacion).toLocaleString('es-CO', {
                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });

            // Botón Validar solo si pendiente o emergencia
            const mostrarValidar = (c.estado.toLowerCase() === 'pendiente' || c.estado.toLowerCase() === 'emergencia');

            return `
                <tr class="hover:bg-blue-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500">
                        ${fecha}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-900">${c.nombre} ${c.apellido}</div>
                        <div class="text-xs text-gray-500 flex items-center mt-0.5">
                            <i class="fas fa-phone-alt mr-1 text-gray-300"></i> ${c.telefono || 'N/A'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <span class="font-medium">${c.edad} años</span> <span class="text-gray-300 mx-1">|</span> ${c.sexo}
                        ${c.embarazo == 1 ? '<div class="mt-1"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-pink-100 text-pink-600 border border-pink-200">EMBARAZO</span></div>' : ''}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge level-${c.nivel_triage_ia} border border-black border-opacity-5 shadow-sm">
                            Nivel ${c.nivel_triage_ia}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 max-w-xs truncate cursor-help" title="${c.sintomas}">
                            ${c.sintomas}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge ${getEstadoClass(c.estado)} shadow-sm">
                            ${c.estado}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-3">
                            <button onclick="window.location.href='caso_detalle.php?id=${c.id_caso}'" 
                                    class="text-blue-600 hover:text-blue-800 flex items-center transition-colors" 
                                    title="Ver Detalles">
                                <i class="fas fa-eye text-lg"></i>
                            </button>
                            
                            ${mostrarValidar ? `
                                <button onclick="window.location.href='caso_validar.php?id=${c.id_caso}'" 
                                        class="text-green-600 hover:text-green-800 flex items-center transition-colors" 
                                        title="Validar Caso">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }

        // Utilidad de colores
        function getEstadoClass(estado) {
            const map = {
                'pendiente': 'bg-estado-pendiente',
                'aprobado': 'bg-estado-aprobado',
                'validado': 'bg-estado-aprobado', // Alias
                'rechazado': 'bg-estado-rechazado',
                'emergencia': 'bg-estado-emergencia'
            };
            return map[estado.toLowerCase()] || 'bg-gray-200 text-gray-600';
        }

        function limpiarFiltros() {
            document.getElementById('filterEstado').value = '';
            document.getElementById('filterNivel').value = '';
            document.getElementById('filterFecha').value = '';
            cargarCasos();
        }

        // Logout
        async function logout() {
            if (confirm('¿Está seguro que desea cerrar sesión?')) {
                try {
                    await fetch('../api/auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=logout'
                    });
                } catch (e) {}
                window.location.href = 'login.php';
            }
        }
    </script>
</body>
</html>
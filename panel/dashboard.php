<?php
// FORZAMOS al navegador a entender que esto es HTML y UTF-8
header('Content-Type: text/html; charset=utf-8');

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Carga de archivos API
if (file_exists('../api/config.php')) {
    require_once '../api/config.php';
}

if (file_exists('../api/db.php')) {
    require_once '../api/db.php';
} elseif (file_exists('../database/connection.php')) {
    require_once '../database/connection.php';
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
        .status-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .level-1 { background-color: #fee2e2; color: #991b1b; }
        .level-2 { background-color: #fed7aa; color: #9a3412; }
        .level-3 { background-color: #fef3c7; color: #92400e; }
        .level-4 { background-color: #d1fae5; color: #065f46; }
        .level-5 { background-color: #dbeafe; color: #1e40af; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-stethoscope text-blue-600 text-2xl mr-3"></i>
                    <h1 class="text-xl font-bold text-gray-800">Sistema de Triage ADOM</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($usuario['nombre']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($usuario['rol']); ?></p>
                    </div>
                    <button onclick="logout()" class="text-red-600 hover:text-red-700"><i class="fas fa-sign-out-alt text-xl"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-md p-3"><i class="fas fa-exclamation-triangle text-red-600 text-xl"></i></div>
                    <div class="ml-4"><p class="text-sm font-medium text-gray-500">Emergencias</p><p class="text-2xl font-bold text-gray-900" id="stat-emergencias">0</p></div>
                </div>
            </div>
            <div class="stat-card bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i class="fas fa-clock text-yellow-600 text-xl"></i></div>
                    <div class="ml-4"><p class="text-sm font-medium text-gray-500">Pendientes</p><p class="text-2xl font-bold text-gray-900" id="stat-pendientes">0</p></div>
                </div>
            </div>
            <div class="stat-card bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i class="fas fa-check-circle text-green-600 text-xl"></i></div>
                    <div class="ml-4"><p class="text-sm font-medium text-gray-500">Validados Hoy</p><p class="text-2xl font-bold text-gray-900" id="stat-validados">0</p></div>
                </div>
            </div>
            <div class="stat-card bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i class="fas fa-users text-blue-600 text-xl"></i></div>
                    <div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Pacientes</p><p class="text-2xl font-bold text-gray-900" id="stat-total">0</p></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select id="filterEstado" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="PENDIENTE" selected>Pendientes</option>
                        <option value="VALIDADO">Validados</option>
                        <option value="RECHAZADO">Rechazados</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nivel de Triage</label>
                    <select id="filterNivel" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos los niveles</option>
                        <option value="1">Nivel 1 - Emergencia</option>
                        <option value="2">Nivel 2 - Urgencia Grave</option>
                        <option value="3">Nivel 3 - Urgencia Moderada</option>
                        <option value="4">Nivel 4 - Urgencia Menor</option>
                        <option value="5">Nivel 5 - No Urgente</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" id="filterFecha" class="w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="flex items-end">
                    <button onclick="aplicarFiltros()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800"><i class="fas fa-list-ul mr-2"></i>Casos de Triage</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha/Hora</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paciente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Edad/Sexo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nivel IA</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Síntomas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCasos" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            </div>
            <div id="noCases" class="hidden px-6 py-12 text-center">
                <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500">No hay casos que coincidan con los filtros aplicados</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            cargarEstadisticas();
            cargarCasos();
            setInterval(cargarCasos, 30000);
            setInterval(cargarEstadisticas, 30000);
        });

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
            } catch (error) { console.error('Error:', error); }
        }

        async function cargarCasos() {
            const estado = document.getElementById('filterEstado').value;
            const nivel = document.getElementById('filterNivel').value;
            const fecha = document.getElementById('filterFecha').value;
            const params = new URLSearchParams({ action: 'listar', estado: estado, nivel: nivel, fecha: fecha });
            try {
                const response = await fetch('../api/casos.php?' + params);
                const data = await response.json();
                if (data.success) mostrarCasos(data.casos);
            } catch (error) { console.error('Error:', error); }
        }

        function mostrarCasos(casos) {
            const tbody = document.getElementById('tablaCasos');
            const noCases = document.getElementById('noCases');
            if (casos.length === 0) { tbody.innerHTML = ''; noCases.classList.remove('hidden'); return; }
            noCases.classList.add('hidden');
            tbody.innerHTML = casos.map(caso => `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${formatearFecha(caso.fecha_creacion)}</td>
                    <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium">${caso.nombre} ${caso.apellido}</div><div class="text-sm text-gray-500">${caso.telefono || 'Sin teléfono'}</div></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">${caso.edad} años / ${caso.sexo === 'M' ? 'M' : 'F'} ${caso.embarazo == 1 ? '<br><span class="text-xs text-pink-600">⚠️ Embarazada</span>' : ''}</td>
                    <td class="px-6 py-4 whitespace-nowrap"><span class="status-badge level-${caso.nivel_triage_ia}">Nivel ${caso.nivel_triage_ia}</span></td>
                    <td class="px-6 py-4 text-sm"><div class="max-w-xs truncate">${caso.sintomas}</div></td>
                    <td class="px-6 py-4 whitespace-nowrap"><span class="status-badge ${getEstadoClass(caso.estado)}">${caso.estado}</span></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button onclick="verDetalle(${caso.id_caso})" class="text-blue-600 hover:text-blue-900 mr-3"><i class="fas fa-eye"></i> Ver</button>
                        ${caso.estado === 'PENDIENTE' ? `<button onclick="validarCaso(${caso.id_caso})" class="text-green-600 hover:text-green-900"><i class="fas fa-check"></i> Validar</button>` : ''}
                    </td>
                </tr>
            `).join('');
        }

        function getEstadoClass(estado) {
            const classes = { 'PENDIENTE': 'bg-yellow-100 text-yellow-800', 'VALIDADO': 'bg-green-100 text-green-800', 'RECHAZADO': 'bg-red-100 text-red-800' };
            return classes[estado] || 'bg-gray-100 text-gray-800';
        }

        function formatearFecha(fecha) {
            const d = new Date(fecha);
            return d.toLocaleDateString('es-CO') + ' ' + d.toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit'});
        }

        function aplicarFiltros() { cargarCasos(); }
        function verDetalle(idCaso) { window.location.href = 'caso_detalle.php?id=' + idCaso; }
        function validarCaso(idCaso) { window.location.href = 'caso_validar.php?id=' + idCaso; }
        async function logout() {
            if (confirm('¿Está seguro que desea cerrar sesión?')) {
                try { await fetch('../api/auth.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'action=logout' }); } catch (error) { console.error('Error:', error); }
                window.location.href = 'login.php';
            }
        }
    </script>
</body>
</html>
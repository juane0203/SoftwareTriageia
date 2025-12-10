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
    <title>Agenda de Servicios - ADOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .level-1 { border-left: 4px solid #ef4444; }
        .level-2 { border-left: 4px solid #f97316; }
        .level-3 { border-left: 4px solid #eab308; }
        .level-4 { border-left: 4px solid #22c55e; }
        .level-5 { border-left: 4px solid #3b82f6; }
        .fade-in { animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
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
                        <h1 class="text-xl font-bold text-gray-800">Triage ADOM</h1>
                    </div>
                    <div class="hidden md:flex space-x-2">
                        <a href="dashboard.php" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-columns mr-2 text-gray-400"></i> Tablero
                        </a>
                        <a href="agenda.php" class="px-4 py-2 rounded-lg text-sm font-bold bg-blue-50 text-blue-700 flex items-center">
                            <i class="fas fa-calendar-alt mr-2"></i> Agenda
                        </a>
                        <a href="reportes.php" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-chart-pie mr-2 text-purple-500"></i> Reportes IA
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <p class="text-sm font-bold text-gray-700 mr-4 hidden sm:block"><?php echo htmlspecialchars($usuario['nombre']); ?></p>
                    <button onclick="window.location.href='login.php'" class="text-red-500 hover:bg-red-50 p-2 rounded-full"><i class="fas fa-sign-out-alt"></i></button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Programación de Visitas</h2>
                <p class="text-gray-500 text-sm">Gestión de rutas y citas domiciliarias</p>
            </div>
            <div class="flex space-x-3 bg-white p-2 rounded-lg shadow-sm border">
                <input type="date" id="filtroFecha" class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <select id="filtroProf" class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500 w-48">
                    <option value="">Todos los Profesionales</option>
                    </select>
                <button onclick="cargarAgenda()" class="bg-blue-600 text-white px-4 py-1 rounded-md text-sm hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div id="listaCitas" class="space-y-4">
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                <p>Cargando agenda...</p>
            </div>
        </div>

        <div id="emptyState" class="hidden text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
            <div class="bg-gray-50 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-4">
                <i class="far fa-calendar-times text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-gray-900 font-medium text-lg">Sin visitas programadas</h3>
            <p class="text-gray-500 text-sm mt-1">No hay citas agendadas para la fecha y profesional seleccionados.</p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Poner fecha de hoy por defecto
            document.getElementById('filtroFecha').valueAsDate = new Date();
            
            await cargarProfesionales();
            cargarAgenda();
        });

        async function cargarProfesionales() {
            try {
                const res = await fetch('../api/profesionales.php');
                const data = await res.json();
                if(data.success) {
                    const select = document.getElementById('filtroProf');
                    data.profesionales.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.usuario_id;
                        opt.textContent = p.nombre;
                        select.appendChild(opt);
                    });
                }
            } catch(e) {}
        }

        async function cargarAgenda() {
            const fecha = document.getElementById('filtroFecha').value;
            const prof = document.getElementById('filtroProf').value;
            const contenedor = document.getElementById('listaCitas');
            const empty = document.getElementById('emptyState');

            contenedor.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-blue-500"></i></div>';
            empty.classList.add('hidden');

            try {
                const res = await fetch(`../api/agenda.php?fecha=${fecha}&profesional_id=${prof}`);
                const data = await res.json();

                contenedor.innerHTML = '';

                if (!data.success || data.citas.length === 0) {
                    empty.classList.remove('hidden');
                    return;
                }

                data.citas.forEach(cita => {
                    const hora = cita.hora_atencion.substring(0, 5); // HH:MM
                    const nivel = cita.nivel_triage_final || cita.nivel_triage_ia;
                    
                    const card = `
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-col md:flex-row items-start md:items-center hover:shadow-md transition-shadow fade-in level-${nivel}">
                            
                            <div class="flex flex-col items-center justify-center pr-6 border-r border-gray-100 min-w-[100px] mb-4 md:mb-0">
                                <span class="text-2xl font-bold text-gray-800">${hora}</span>
                                <span class="text-xs text-gray-500 uppercase font-bold">Hora Visita</span>
                            </div>

                            <div class="flex-grow pl-0 md:pl-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-800">${cita.p_nombre} ${cita.p_apellido}</h3>
                                        <p class="text-gray-600 text-sm flex items-center mt-1">
                                            <i class="fas fa-map-marker-alt text-red-500 mr-2 w-4 text-center"></i> ${cita.direccion}
                                        </p>
                                        <p class="text-gray-600 text-sm flex items-center mt-1">
                                            <i class="fas fa-phone text-green-500 mr-2 w-4 text-center"></i> ${cita.telefono}
                                        </p>
                                    </div>
                                    
                                    <div class="text-right hidden sm:block">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Nivel ${nivel}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between items-center">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-user-md text-indigo-500 mr-2"></i>
                                        <span class="font-medium mr-1">Asignado a:</span> 
                                        ${cita.prof_nombre || '<span class="text-red-400">Sin asignar</span>'} 
                                        <span class="text-gray-400 text-xs ml-1">(${cita.especialidad || 'General'})</span>
                                    </div>
                                    
                                    <button onclick="window.location.href='caso_detalle.php?id=${cita.caso_id}'" class="text-blue-600 hover:text-blue-800 text-sm font-bold flex items-center">
                                        Ver Caso <i class="fas fa-arrow-right ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    contenedor.innerHTML += card;
                });

            } catch (e) {
                console.error(e);
                contenedor.innerHTML = '<p class="text-red-500 text-center">Error cargando datos.</p>';
            }
        }
    </script>
</body>
</html>
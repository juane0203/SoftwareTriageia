<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Caso</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .level-1 { background-color: #fee2e2; color: #991b1b; }
        .level-2 { background-color: #ffedd5; color: #9a3412; }
        .level-3 { background-color: #fef9c3; color: #854d0e; }
        .level-4 { background-color: #dcfce7; color: #166534; }
        .level-5 { background-color: #dbeafe; color: #1e40af; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-8">
        
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 transition-colors flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <span class="text-gray-300">|</span>
                <h1 class="text-2xl font-bold text-gray-800">Caso #<span id="headerId">...</span></h1>
            </div>
            
            <button id="btnValidar" onclick="irAValidar()" class="hidden bg-green-600 text-white px-6 py-2.5 rounded-lg hover:bg-green-700 shadow-md font-semibold flex items-center">
                <i class="fas fa-user-md mr-2"></i> Validar Caso
            </button>
        </div>

        <div id="loading" class="text-center py-20">
            <i class="fas fa-circle-notch fa-spin text-5xl text-blue-500 mb-4"></i>
            <p class="text-gray-500 text-lg">Cargando datos...</p>
        </div>

        <div id="content" class="hidden space-y-6">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-4 flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-gray-500 uppercase">Estado</span>
                    <div id="badgeEstado" class="mt-1 px-3 py-1 rounded-full text-sm font-bold bg-gray-200 uppercase">...</div>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-gray-500 uppercase">Fecha Solicitud</span>
                    <p class="font-medium text-gray-800" id="dFecha"></p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="lg:col-span-1 bg-white rounded-xl shadow-sm p-6 border border-gray-100 h-fit">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-user mr-2 text-blue-500"></i>Paciente</h3>
                    <div class="space-y-4">
                        <div><p class="text-xs text-gray-500 uppercase">Nombre</p><p class="font-semibold text-lg" id="dNombre"></p></div>
                        <div><p class="text-xs text-gray-500 uppercase">Edad/Sexo</p><p class="font-medium" id="dEdadSexo"></p></div>
                        <div><p class="text-xs text-gray-500 uppercase">Contacto</p><p id="dTel"></p><p id="dEmail" class="text-sm break-all"></p></div>
                        <div><p class="text-xs text-gray-500 uppercase">Dirección</p><p id="dDir"></p></div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-notes-medical mr-2 text-blue-500"></i>Clínica</h3>
                        <div class="mb-6">
                            <p class="text-xs font-bold text-gray-500 uppercase mb-2">Síntomas</p>
                            <div class="bg-gray-50 p-4 rounded-lg text-gray-800" id="dSintomas"></div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6" id="gridSignos"></div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                            <div class="flex justify-between mb-2">
                                <span class="text-blue-800 font-bold text-xs uppercase">IA Sugiere</span>
                                <span id="dNivelIA" class="px-2 py-0.5 rounded text-xs font-bold bg-white shadow-sm"></span>
                            </div>
                            <p class="text-blue-900 text-sm italic" id="dJustIA"></p>
                        </div>
                    </div>

                    <div id="cardDictamen" class="hidden bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                        <h3 class="text-lg font-bold text-green-800 mb-4 border-b pb-2"><i class="fas fa-clipboard-check mr-2"></i>Dictamen Final</h3>
                        <div class="grid grid-cols-2 gap-6">
                            <div><p class="text-xs text-gray-500 uppercase">Nivel Final</p><div id="dNivelFinal" class="mt-1 font-bold text-xl text-gray-800"></div></div>
                            <div><p class="text-xs text-gray-500 uppercase">Validado Por</p><p class="mt-1 font-medium text-gray-800" id="dValidador"></p></div>
                        </div>
                        
                        <div id="bloqueAgenda" class="hidden mt-6 bg-green-50 p-4 rounded-lg border border-green-100">
                            <h4 class="font-bold text-green-900 mb-2 text-sm"><i class="fas fa-calendar-alt mr-2"></i>Cita Programada</h4>
                            <div class="text-sm text-green-800">
                                <p><strong>Médico:</strong> <span id="dAgendaDoc"></span> <span id="dAgendaEsp" class="text-xs opacity-75"></span></p>
                                <p><strong>Fecha:</strong> <span id="dAgendaFecha"></span> a las <span id="dAgendaHora"></span></p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500 uppercase mb-1">Observaciones</p>
                            <p class="text-gray-700" id="dJustFinal"></p>
                        </div>
                        
                        <div id="bloqueRechazo" class="hidden mt-4 bg-red-50 p-4 rounded border border-red-100">
                            <p class="text-xs text-red-600 uppercase font-bold">Motivo Rechazo</p>
                            <p class="text-red-800" id="dMotivoRechazo"></p>
                        </div>
                        <div class="mt-4 text-right text-xs text-gray-400">Fecha validación: <span id="dFechaVal"></span></div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2"><i class="fas fa-history mr-2 text-blue-500"></i>Historial de Acciones</h3>
                        <ul id="listaHistorial" class="space-y-4">
                            <li class="text-gray-400 text-sm italic text-center">Cargando historial...</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        const id = new URLSearchParams(window.location.search).get('id');

        document.addEventListener('DOMContentLoaded', async () => {
            if(!id) return window.location.href='dashboard.php';
            document.getElementById('headerId').textContent = id;
            
            try {
                const res = await fetch(`../api/casos.php?action=detalle&id=${id}`);
                const data = await res.json();
                if(data.success) {
                    render(data.caso);
                    renderHistorial(data.historial); // Renderizar historial
                }
            } catch(e) { alert('Error de conexión'); }
        });

        function irAValidar() { window.location.href = `caso_validar.php?id=${id}`; }

        function render(c) {
            document.getElementById('dNombre').textContent = `${c.nombre} ${c.apellido}`;
            document.getElementById('dEdadSexo').textContent = `${c.edad} años / ${c.sexo}`;
            document.getElementById('dTel').textContent = c.telefono || 'N/A';
            document.getElementById('dEmail').textContent = c.email || 'N/A';
            document.getElementById('dDir').textContent = c.direccion || 'N/A';
            document.getElementById('dFecha').textContent = new Date(c.fecha_creacion).toLocaleString();

            const badge = document.getElementById('badgeEstado');
            badge.textContent = c.estado;
            badge.className = `mt-1 px-3 py-1 rounded-full text-sm font-bold text-white uppercase ${getColorEstado(c.estado)}`;

            if (['pendiente', 'emergencia'].includes(c.estado.toLowerCase())) {
                document.getElementById('btnValidar').classList.remove('hidden');
            } else {
                mostrarDictamen(c);
            }

            document.getElementById('dSintomas').textContent = c.sintomas;
            document.getElementById('dNivelIA').textContent = `Nivel ${c.nivel_triage_ia}`;
            document.getElementById('dNivelIA').className += ` level-${c.nivel_triage_ia}`;
            document.getElementById('dJustIA').textContent = c.justificacion_ia;
            
            let signos = [];
            if(c.tiene_fiebre == 1) signos.push(cardSigno('Fiebre', c.temperatura + '°C', 'text-red-600', 'bg-red-50'));
            if(c.tiene_dolor == 1) signos.push(cardSigno('Dolor', c.nivel_dolor + '/10', 'text-orange-600', 'bg-orange-50'));
            if(c.esta_embarazada == 1) signos.push(cardSigno('Embarazo', 'Si', 'text-pink-600', 'bg-pink-50'));
            document.getElementById('gridSignos').innerHTML = signos.join('') || '<p class="text-gray-400 text-sm col-span-4 text-center">Sin signos de alarma</p>';

            document.getElementById('loading').classList.add('hidden');
            document.getElementById('content').classList.remove('hidden');
        }

        function renderHistorial(historial) {
            const lista = document.getElementById('listaHistorial');
            lista.innerHTML = '';

            if (!historial || historial.length === 0) {
                lista.innerHTML = '<li class="text-gray-400 text-sm text-center">No hay registros de auditoría disponibles.</li>';
                return;
            }

            historial.forEach(log => {
                let icono = 'fa-info-circle';
                let color = 'text-gray-500';
                
                if (log.accion.includes('APROBAR')) { icono = 'fa-check-circle'; color = 'text-green-500'; }
                else if (log.accion.includes('RECHAZAR')) { icono = 'fa-times-circle'; color = 'text-red-500'; }
                else if (log.accion.includes('ENVIO_EMAIL')) { icono = 'fa-envelope'; color = 'text-blue-500'; }

                const item = `
                    <li class="flex items-start space-x-3 pb-4 border-b border-gray-100 last:border-0">
                        <div class="mt-1"><i class="fas ${icono} ${color}"></i></div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-800">${log.accion.replace('_', ' ')}</p>
                            <p class="text-xs text-gray-600">${log.detalles || ''}</p>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-400"><i class="fas fa-user mr-1"></i>${log.usuario_nombre || 'Sistema'}</span>
                                <span class="text-xs text-gray-400">${new Date(log.fecha_accion).toLocaleString()}</span>
                            </div>
                        </div>
                    </li>
                `;
                lista.innerHTML += item;
            });
        }

        function mostrarDictamen(c) {
            const card = document.getElementById('cardDictamen');
            card.classList.remove('hidden');
            document.getElementById('dValidador').textContent = c.validador_nombre ? 'Dr(a). ' + c.validador_nombre : 'Sistema';
            document.getElementById('dJustFinal').textContent = c.justificacion_final || 'Sin notas.';
            document.getElementById('dFechaVal').textContent = new Date(c.fecha_validacion).toLocaleString();

            if (c.estado.toLowerCase() === 'aprobado') {
                const nv = c.nivel_triage_final || c.nivel_triage_ia;
                document.getElementById('dNivelFinal').innerHTML = `<span class="px-3 py-1 rounded-full text-white text-sm level-${nv}">Nivel ${nv}</span>`;
                
                if (c.profesional_nombre) {
                    document.getElementById('bloqueAgenda').classList.remove('hidden');
                    document.getElementById('dAgendaDoc').textContent = c.profesional_nombre;
                    document.getElementById('dAgendaEsp').textContent = c.profesional_especialidad ? `(${c.profesional_especialidad})` : '';
                    document.getElementById('dAgendaFecha').textContent = c.fecha_atencion;
                    document.getElementById('dAgendaHora').textContent = c.hora_atencion;
                }
            } else if (c.estado.toLowerCase() === 'rechazado') {
                card.classList.replace('border-green-500', 'border-red-500');
                document.getElementById('bloqueRechazo').classList.remove('hidden');
                document.getElementById('dMotivoRechazo').textContent = c.motivo_rechazo;
                document.getElementById('dNivelFinal').innerHTML = '<span class="text-red-600">RECHAZADO</span>';
            }
        }

        function cardSigno(label, val, txtColor, bgColor) {
            return `<div class="${bgColor} p-2 rounded text-center border border-opacity-10 border-current"><span class="block text-xs font-bold text-gray-500 uppercase">${label}</span><span class="font-bold ${txtColor}">${val}</span></div>`;
        }

        function getColorEstado(e) {
            const map = { 'pendiente':'bg-yellow-500', 'emergencia':'bg-red-600 animate-pulse', 'aprobado':'bg-green-600', 'rechazado':'bg-gray-500' };
            return map[e.toLowerCase()] || 'bg-gray-400';
        }
    </script>
</body>
</html>
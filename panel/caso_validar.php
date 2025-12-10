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
    <title>Validar y Agendar - Triage ADOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Gestión del Caso #<span id="casoIdDisplay">...</span></h1>
        </div>

        <div id="loading" class="text-center py-12"><i class="fas fa-spinner fa-spin text-4xl text-blue-600"></i></div>

        <div id="casoContent" class="hidden space-y-6 animate-fade-in">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-3"><i class="fas fa-user mr-2 text-blue-500"></i>Paciente</h3>
                    <p class="text-lg font-medium" id="pNombre"></p>
                    <p class="text-gray-500 text-sm" id="pEdadSexo"></p>
                    <p class="mt-2 text-gray-600"><i class="fas fa-map-marker-alt mr-1"></i> <span id="pDireccion"></span></p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                    <h3 class="font-bold text-gray-700 border-b pb-2 mb-3"><i class="fas fa-notes-medical mr-2 text-blue-500"></i>IA Sugiere</h3>
                    <div class="flex items-center justify-between mb-2">
                        <span id="iaBadge" class="px-3 py-1 rounded-full text-sm font-bold">...</span>
                    </div>
                    <p class="text-sm text-gray-600 italic bg-gray-50 p-2 rounded" id="iaJustificacion"></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden border-t-4 border-indigo-600">
                <div class="p-6 bg-gray-50 border-b">
                    <h2 class="text-xl font-bold text-gray-800 text-center">Dictamen y Agendamiento</h2>
                </div>
                
                <form id="validarForm" class="p-8 space-y-8">
                    
                    <div class="flex justify-center space-x-6">
                        <label class="cursor-pointer w-1/2">
                            <input type="radio" name="accion" value="aprobado" class="hidden peer" checked onchange="toggleFields()">
                            <div class="text-center p-4 border-2 rounded-xl transition-all peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 hover:border-green-300 bg-white h-full flex flex-col items-center justify-center">
                                <i class="fas fa-calendar-check text-3xl mb-2"></i>
                                <span class="font-bold block">Aprobar y Agendar</span>
                            </div>
                        </label>
                        <label class="cursor-pointer w-1/2">
                            <input type="radio" name="accion" value="rechazado" class="hidden peer" onchange="toggleFields()">
                            <div class="text-center p-4 border-2 rounded-xl transition-all peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 hover:border-red-300 bg-white h-full flex flex-col items-center justify-center">
                                <i class="fas fa-ban text-3xl mb-2"></i>
                                <span class="font-bold block">Rechazar Caso</span>
                            </div>
                        </label>
                    </div>

                    <div id="camposAprobar" class="space-y-6">
                        <div class="bg-green-50 p-5 rounded-lg border border-green-100">
                            <h3 class="font-bold text-green-800 mb-4"><i class="fas fa-clock mr-2"></i>Datos de la Visita</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Médico Asignado</label>
                                    <select id="profesional" class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-green-500 focus:border-green-500">
                                        <option value="">-- Seleccione --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Nivel Final</label>
                                    <select id="nivelFinal" class="w-full border-gray-300 rounded-lg p-2.5">
                                        <option value="3">Nivel 3 - Moderado</option>
                                        <option value="4">Nivel 4 - Leve</option>
                                        <option value="5">Nivel 5 - No Urgente</option>
                                        <option value="1">Nivel 1 - Emergencia</option>
                                        <option value="2">Nivel 2 - Urgencia</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Fecha</label>
                                    <input type="date" id="fechaAtencion" class="w-full border-gray-300 rounded-lg p-2.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Hora</label>
                                    <input type="time" id="horaAtencion" class="w-full border-gray-300 rounded-lg p-2.5">
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Observaciones Médicas</label>
                            <textarea id="justificacion" rows="2" class="w-full border-gray-300 rounded-lg p-3" placeholder="Instrucciones..."></textarea>
                        </div>
                    </div>

                    <div id="camposRechazo" class="hidden">
                        <div class="bg-red-50 p-5 rounded-lg border border-red-100">
                            <label class="block text-sm font-bold text-red-800 mb-2">Motivo del Rechazo *</label>
                            <textarea id="motivoRechazo" rows="3" class="w-full border-red-300 rounded-lg p-3 focus:ring-red-500" placeholder="Explique la razón..."></textarea>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-xl font-bold text-lg shadow hover:bg-indigo-700 transition-all">
                        Guardar Gestión
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const params = new URLSearchParams(window.location.search);
        const casoId = params.get('id');

        document.addEventListener('DOMContentLoaded', async () => {
            if(!casoId) return window.location.href='dashboard.php';
            document.getElementById('casoIdDisplay').textContent = casoId;
            
            await cargarProfesionales();
            await cargarCaso();
            
            document.getElementById('fechaAtencion').valueAsDate = new Date();
        });

        async function cargarProfesionales() {
            try {
                const res = await fetch('../api/profesionales.php');
                const data = await res.json();
                if(data.success) {
                    const select = document.getElementById('profesional');
                    data.profesionales.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.usuario_id;
                        opt.textContent = `${p.nombre} (${p.especialidad || 'General'})`;
                        select.appendChild(opt);
                    });
                }
            } catch(e) {}
        }

        async function cargarCaso() {
            try {
                const res = await fetch(`../api/casos.php?action=detalle&id=${casoId}`);
                const data = await res.json();
                if(data.success) {
                    const c = data.caso;
                    document.getElementById('pNombre').textContent = `${c.nombre} ${c.apellido}`;
                    document.getElementById('pEdadSexo').textContent = `${c.edad} años / ${c.sexo}`;
                    document.getElementById('pDireccion').textContent = c.direccion;
                    document.getElementById('iaJustificacion').textContent = c.justificacion_ia;
                    
                    const badge = document.getElementById('iaBadge');
                    badge.textContent = `Nivel ${c.nivel_triage_ia}`;
                    badge.className = `px-3 py-1 rounded-full text-sm font-bold text-white bg-blue-600 level-${c.nivel_triage_ia}`;
                    
                    document.getElementById('nivelFinal').value = c.nivel_triage_ia;
                    document.getElementById('loading').classList.add('hidden');
                    document.getElementById('casoContent').classList.remove('hidden');
                }
            } catch(e) { alert('Error cargando caso'); }
        }

        function toggleFields() {
            const accion = document.querySelector('input[name="accion"]:checked').value;
            document.getElementById('camposAprobar').className = accion === 'aprobado' ? 'space-y-6 animate-fade-in' : 'hidden';
            document.getElementById('camposRechazo').className = accion === 'rechazado' ? 'animate-fade-in' : 'hidden';
        }

        document.getElementById('validarForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            if(!confirm('¿Confirma guardar la gestión?')) return;

            const accion = document.querySelector('input[name="accion"]:checked').value;
            const body = {
                caso_id: casoId,
                estado: accion,
                nivel_final: document.getElementById('nivelFinal').value,
                justificacion: document.getElementById('justificacion').value,
                profesional_id: document.getElementById('profesional').value,
                fecha_atencion: document.getElementById('fechaAtencion').value,
                hora_atencion: document.getElementById('horaAtencion').value,
                motivo_rechazo: document.getElementById('motivoRechazo').value
            };

            if (accion === 'aprobado' && (!body.profesional_id || !body.fecha_atencion || !body.hora_atencion)) {
                alert('Para aprobar, complete los datos de la visita.'); return;
            }
            if (accion === 'rechazado' && !body.motivo_rechazo) {
                alert('Indique el motivo del rechazo.'); return;
            }

            try {
                const res = await fetch('../api/casos.php?action=validar', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(body)
                });
                const data = await res.json();
                if(data.success) {
                    alert('Gestión guardada con éxito');
                    window.location.href = 'dashboard.php';
                } else { throw new Error(data.error); }
            } catch(e) { alert('Error: ' + e.message); }
        });
    </script>
    <style>.level-1{background:#fee2e2;color:#991b1b}.level-2{background:#ffedd5;color:#9a3412}.level-3{background:#fef9c3;color:#854d0e}.level-4{background:#dcfce7;color:#166534}.level-5{background:#dbeafe;color:#1e40af}.animate-fade-in{animation:fadeIn .5s ease}@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}</style>
</body>
</html>
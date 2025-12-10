document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('triageForm');
    const sexSelect = form.querySelector('select[name="sex"]');
    const pregnancyQuestion = document.getElementById('pregnancyQuestion');
    const resultDiv = document.getElementById('triageResult');
    
    // --- MANEJO DE UI ---
    // Mostrar/Ocultar Temperatura
    document.getElementById('hasFever').addEventListener('change', function() {
        const el = document.getElementById('temperature');
        el.style.display = this.checked ? 'inline-block' : 'none';
        if(this.checked) el.focus();
    });

    // Mostrar/Ocultar Dolor
    document.getElementById('hasPain').addEventListener('change', function() {
        const el = document.getElementById('painLevel');
        el.style.display = this.checked ? 'inline-block' : 'none';
        if(this.checked) el.focus();
    });

    // Embarazo solo para mujeres
    sexSelect.addEventListener('change', function() {
        const isFemale = this.value === 'F' || this.value === 'Femenino';
        pregnancyQuestion.style.display = isFemale ? 'block' : 'none';
        if (!isFemale) form.dataset.isPregnant = 'false';
    });

    // Botones de embarazo
    window.selectPregnancy = function(val) {
        form.dataset.isPregnant = val;
        // Visual feedback (opcional)
        document.querySelectorAll('#pregnancyQuestion button').forEach(b => b.classList.remove('bg-blue-200'));
        event.target.classList.add('bg-blue-200');
    };

    // --- ENVÍO DEL FORMULARIO ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = form.querySelector('button[type="submit"]');
        
        // Validación básica HTML
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // UI Loading
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analizando con IA...';
        resultDiv.classList.add('hidden');

        try {
            // Construir objeto de datos LIMPIO
            const formData = {
                nombre: form.querySelector('[name="nombre"]').value.trim(),
                apellido: form.querySelector('[name="apellido"]').value.trim(),
                direccion: form.querySelector('[name="direccion"]').value.trim(),
                telefono: form.querySelector('[name="telefono"]').value.trim(),
                email: form.querySelector('[name="email"]').value.trim(),
                age: parseInt(form.querySelector('[name="age"]').value),
                sex: form.querySelector('[name="sex"]').value,
                isPregnant: form.dataset.isPregnant === 'true',
                symptoms: form.querySelector('[name="symptoms"]').value.trim(),
                additionalInfo: {
                    hasFever: document.getElementById('hasFever').checked,
                    temperature: document.getElementById('temperature').value ? parseFloat(document.getElementById('temperature').value) : null,
                    hasPain: document.getElementById('hasPain').checked,
                    painLevel: document.getElementById('painLevel').value ? parseInt(document.getElementById('painLevel').value) : 0,
                    hasBreathingDifficulty: document.getElementById('hasBreathingDifficulty').checked,
                    hasVomiting: document.getElementById('hasVomiting').checked,
                    hasConsciousnessLoss: document.getElementById('hasConsciousnessLoss').checked
                }
            };

            console.log("Enviando datos:", formData); // Debug

            const response = await fetch('api/triage.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const textResponse = await response.text();
            
            let result;
            try {
                result = JSON.parse(textResponse);
            } catch(err) {
                console.error("Respuesta no JSON:", textResponse);
                throw new Error("Error de comunicación con el servidor.");
            }

            if (result.error) throw new Error(result.error);
            
            displayResult(result);

        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '🔍 Evaluar Triage';
        }
    });
});

function displayResult(result) {
    const resultDiv = document.getElementById('triageResult');
    const level = result.level;
    const isEmergency = level <= 2;

    // Definir colores según nivel
    let colorClass = 'bg-blue-100 border-blue-500 text-blue-900'; // Nivel 5
    if (level === 4) colorClass = 'bg-green-100 border-green-500 text-green-900';
    if (level === 3) colorClass = 'bg-yellow-100 border-yellow-500 text-yellow-900';
    if (level === 2) colorClass = 'bg-orange-100 border-orange-500 text-orange-900';
    if (level === 1) colorClass = 'bg-red-100 border-red-500 text-red-900';

    let html = `
        <div class="p-6 rounded-lg border-l-8 ${colorClass} shadow-xl animate__animated animate__fadeInUp">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-3xl font-bold uppercase">NIVEL ${level}: ${getLabel(level)}</h2>
                    <p class="text-sm opacity-75">Caso ID: #${result.caseId || 'Pendiente'}</p>
                </div>
                <div class="text-4xl">${getIcon(level)}</div>
            </div>
            
            ${isEmergency ? `
                <div class="bg-red-600 text-white p-4 rounded-lg text-center mb-6 shadow-lg animate-pulse">
                    <p class="text-xl font-bold uppercase">¡Atención Inmediata Requerida!</p>
                    <p class="text-sm mb-3">Su condición requiere valoración urgente.</p>
                    <a href="tel:123" class="inline-block bg-white text-red-600 font-bold py-3 px-6 rounded-full hover:scale-105 transition-transform">
                        <i class="fas fa-phone-alt mr-2"></i> LLAMAR AL 123
                    </a>
                </div>
            ` : ''}

            <div class="bg-white bg-opacity-60 p-4 rounded-md space-y-3">
                <div>
                    <span class="font-bold block text-sm uppercase opacity-70">Análisis Clínico:</span>
                    <p class="text-lg leading-relaxed">${result.description}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div>
                        <span class="font-bold block text-sm uppercase opacity-70">Tiempo de Espera:</span>
                        <span>${result.waitTime}</span>
                    </div>
                    <div>
                        <span class="font-bold block text-sm uppercase opacity-70">Recomendación:</span>
                        <span>${result.recommendation}</span>
                    </div>
                </div>

                ${result.warningSigns && result.warningSigns.length > 0 ? `
                    <div class="mt-3 pt-3 border-t border-black border-opacity-10">
                        <span class="font-bold text-sm uppercase text-red-600">Signos de Alarma:</span>
                        <ul class="list-disc ml-5 text-sm">
                            ${result.warningSigns.map(s => `<li>${s}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    resultDiv.innerHTML = html;
    resultDiv.classList.remove('hidden');
    resultDiv.scrollIntoView({ behavior: 'smooth' });
}

function getLabel(level) {
    const labels = {1: 'RESUCITACIÓN', 2: 'EMERGENCIA', 3: 'URGENCIA', 4: 'PRIORITARIO', 5: 'NO URGENTE'};
    return labels[level] || 'CONSULTA';
}

function getIcon(level) {
    const icons = {1: '🚑', 2: '🚨', 3: '⚠️', 4: '🩺', 5: '✅'};
    return icons[level] || '📋';
}
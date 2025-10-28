document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('triageForm');
    const sexSelect = form.querySelector('select[name="sex"]');
    const pregnancyQuestion = document.getElementById('pregnancyQuestion');
    const vitalSigns = document.getElementById('vitalSigns');
    const resultDiv = document.getElementById('triageResult');
    
    // Control de campos adicionales con validación
    document.getElementById('hasFever').addEventListener('change', function() {
        const tempField = document.getElementById('temperature');
        tempField.style.display = this.checked ? 'inline-block' : 'none';
        if (this.checked) {
            tempField.required = true;
        } else {
            tempField.required = false;
            tempField.value = '';
        }
    });

    document.getElementById('hasPain').addEventListener('change', function() {
        const painField = document.getElementById('painLevel');
        painField.style.display = this.checked ? 'inline-block' : 'none';
        if (this.checked) {
            painField.required = true;
        } else {
            painField.required = false;
            painField.value = '';
        }
    });

    // Mostrar/ocultar pregunta de embarazo con validación
    sexSelect.addEventListener('change', function() {
        pregnancyQuestion.style.display = this.value === 'F' ? 'block' : 'none';
        if (this.value !== 'F') {
            form.dataset.isPregnant = 'false';
        }
    });

    // Manejo del tipo de consulta mejorado
    window.selectConsultType = function(type) {
        document.querySelectorAll('#callBtn, #platformBtn').forEach(btn => {
            btn.classList.remove('bg-blue-50', 'border-blue-500');
        });
        document.getElementById(type + 'Btn').classList.add('bg-blue-50', 'border-blue-500');
        vitalSigns.style.display = type === 'call' ? 'block' : 'none';
        form.dataset.consultType = type;
    };

    // Manejo de la selección de embarazo mejorado
    window.selectPregnancy = function(isPregnant) {
        const buttons = pregnancyQuestion.querySelectorAll('button');
        buttons.forEach(btn => btn.classList.remove('bg-blue-50', 'border-blue-500'));
        buttons[isPregnant ? 0 : 1].classList.add('bg-blue-50', 'border-blue-500');
        form.dataset.isPregnant = isPregnant;
    };

    // Validación del formulario
    function validateForm(formData) {
        if (!formData.consultType) {
            throw new Error('Debe seleccionar un tipo de consulta');
        }
        if (formData.sex === 'F' && formData.isPregnant === undefined) {
            throw new Error('Debe indicar si está embarazada');
        }
        if (formData.additionalInfo.hasFever && !formData.additionalInfo.temperature) {
            throw new Error('Debe ingresar la temperatura');
        }
        if (formData.additionalInfo.hasPain && !formData.additionalInfo.painLevel) {
            throw new Error('Debe ingresar el nivel de dolor');
        }
        return true;
    }

    // Manejo del envío del formulario mejorado
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Procesando...';
        resultDiv.innerHTML = '';
        resultDiv.classList.add('hidden');

        try {
            const formData = {
                consultType: form.dataset.consultType || 'platform',
                age: form.querySelector('[name="age"]').value,
                sex: form.querySelector('[name="sex"]').value,
                isPregnant: form.dataset.isPregnant === 'true',
                symptoms: form.querySelector('[name="symptoms"]').value,
                additionalInfo: {
                    hasFever: document.getElementById('hasFever').checked,
                    temperature: document.getElementById('temperature').value,
                    hasPain: document.getElementById('hasPain').checked,
                    painLevel: document.getElementById('painLevel').value,
                    hasBreathingDifficulty: document.getElementById('hasBreathingDifficulty').checked,
                    hasVomiting: document.getElementById('hasVomiting').checked,
                    hasConsciousnessLoss: document.getElementById('hasConsciousnessLoss').checked
                }
            };

            validateForm(formData);

            const response = await fetch(CONFIG.API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Error del servidor: ${response.status}\n${errorText}`);
            }

            const result = await response.json();
            displayResult(result);
        } catch (error) {
            displayError(error.message);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Evaluar Triage';
        }
    });
});

// Función mejorada para mostrar el resultado
function displayResult(result) {
    const resultDiv = document.getElementById('triageResult');
    const levelColors = {
        1: 'bg-red-100 text-red-800',
        2: 'bg-red-100 text-red-800',
        3: 'bg-yellow-100 text-yellow-800',
        4: 'bg-green-100 text-green-800',
        5: 'bg-blue-100 text-blue-800'
    };

    resultDiv.innerHTML = `
        <div class="p-6 rounded-lg ${levelColors[result.level]} animate__animated animate__fadeIn">
            <h3 class="text-xl font-bold mb-4">Resultado del Triage</h3>
            <div class="space-y-2">
                <p><strong>Nivel ${result.level}:</strong> ${result.description}</p>
                <p><strong>Tiempo de espera estimado:</strong> ${result.waitTime}</p>
                <p><strong>Acción recomendada:</strong> ${result.recommendation}</p>
                ${result.canUseService ? 
                    '<p class="text-green-700 mt-2 font-medium">✓ Elegible para atención domiciliaria</p>' :
                    '<p class="text-red-700 mt-2 font-medium">⚠️ Requiere atención en centro médico</p>'
                }
                ${result.warningSigns ? `
                    <div class="mt-4 p-4 bg-white bg-opacity-50 rounded-lg">
                        <p class="font-bold">⚠️ Signos de Alarma:</p>
                        <ul class="list-disc ml-4 mt-2">
                            ${result.warningSigns.map(sign => `<li>${sign}</li>`).join('')}
                        </ul>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    resultDiv.classList.remove('hidden');
    resultDiv.scrollIntoView({ behavior: 'smooth' });
}

// Función mejorada para mostrar errores
function displayError(message) {
    const resultDiv = document.getElementById('triageResult');
    resultDiv.innerHTML = `
        <div class="p-6 bg-red-100 text-red-800 rounded-lg animate__animated animate__fadeIn">
            <p class="font-bold">⚠️ Error:</p>
            <p class="mt-2">${message}</p>
        </div>
    `;
    resultDiv.classList.remove('hidden');
    resultDiv.scrollIntoView({ behavior: 'smooth' });
}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Triage ADOM</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-[#2a4693] to-[#4649d6] min-h-screen p-4">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-2">Sistema de Triage ADOM</h1>
            <p class="text-xl text-white opacity-90">Evaluación Inteligente de Síntomas</p>
            <div class="mt-4 text-sm bg-white bg-opacity-20 p-4 rounded-lg text-white">
                Este sistema le ayudará a determinar la urgencia de su caso médico. Por favor, complete todos los campos marcados con * para una evaluación precisa.
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-2xl p-6">
            <div class="mb-6 text-sm text-gray-600 border-l-4 border-blue-500 pl-3">
                La información que proporcione será evaluada por un sistema médico inteligente para determinar el nivel de atención requerido.
            </div>

            <form id="triageForm" class="space-y-6">
                <!-- SECCIÓN 1: DATOS PERSONALES -->
                <div class="border-2 border-blue-200 rounded-lg p-5 bg-blue-50">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Datos Personales
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre *
                                <span class="text-xs text-gray-500 ml-1">(requerido)</span>
                            </label>
                            <input type="text" name="nombre" required
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200"
                                   placeholder="Ej: Juan">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Apellido *
                                <span class="text-xs text-gray-500 ml-1">(requerido)</span>
                            </label>
                            <input type="text" name="apellido" required
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200"
                                   placeholder="Ej: Pérez">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dirección Completa *
                            <span class="text-xs text-gray-500 ml-1">(calle, número, barrio, ciudad)</span>
                        </label>
                        <input type="text" name="direccion" required
                               class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200"
                               placeholder="Ej: Calle 45 #12-34, Barrio Chapinero, Bogotá">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Teléfono *
                                <span class="text-xs text-gray-500 ml-1">(10 dígitos)</span>
                            </label>
                            <input type="tel" name="telefono" required pattern="[0-9]{10}" maxlength="10"
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200"
                                   placeholder="3001234567">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email *
                                <span class="text-xs text-gray-500 ml-1">(correo electrónico)</span>
                            </label>
                            <input type="email" name="email" required
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200"
                                   placeholder="ejemplo@correo.com">
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: INFORMACIÓN DEMOGRÁFICA -->
                <div class="border-2 border-purple-200 rounded-lg p-5 bg-purple-50">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Información Demográfica
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Edad * 
                                <span class="text-xs text-gray-500">(años)</span>
                            </label>
                            <input type="number" name="age" required min="0" max="120"
                                   class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200"
                                   placeholder="Ej: 35">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sexo *
                            </label>
                            <select name="sex" required
                                    class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-200">
                                <option value="">Seleccione...</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                    </div>

                    <div id="pregnancyQuestion" class="hidden mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ¿Está en embarazo? *
                            <span class="text-xs text-gray-500">(solo para pacientes femeninas)</span>
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" onclick="selectPregnancy(true)"
                                    class="p-3 border-2 rounded-lg hover:bg-blue-50 focus:ring-2 focus:ring-blue-200 transition-all">
                                Sí
                            </button>
                            <button type="button" onclick="selectPregnancy(false)"
                                    class="p-3 border-2 rounded-lg hover:bg-blue-50 focus:ring-2 focus:ring-blue-200 transition-all">
                                No
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: SÍNTOMAS -->
                <div class="border-2 border-red-200 rounded-lg p-5 bg-red-50">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Síntomas Principales
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Describa sus síntomas *
                            <span class="text-xs text-gray-500">(sea lo más detallado posible)</span>
                        </label>
                        <textarea name="symptoms" required
                                  class="w-full p-4 h-32 border rounded-lg focus:ring-2 focus:ring-blue-200"
                                  placeholder="Describa todos los síntomas que presenta, cuándo comenzaron, si han empeorado y cualquier otro detalle relevante...&#10;&#10;Ejemplo: Dolor de cabeza intenso desde hace 3 horas, acompañado de náuseas y sensibilidad a la luz."></textarea>
                        <p class="text-xs text-gray-500 mt-1">Mientras más detalles proporcione, más precisa será la evaluación</p>
                    </div>
                </div>

                <!-- SECCIÓN 4: INFORMACIÓN ADICIONAL -->
                <div id="vitalSigns" class="border-2 border-yellow-200 rounded-lg p-5 bg-yellow-50">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Información Adicional
                        <span class="text-sm font-normal text-gray-500 ml-2">(marque lo que corresponda)</span>
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center bg-white p-3 rounded-lg">
                            <input type="checkbox" id="hasFever" class="mr-3">
                            <label for="hasFever" class="text-sm font-medium flex-1">Presenta fiebre</label>
                            <input type="number" id="temperature" placeholder="°C" step="0.1"
                                   class="ml-4 w-24 p-2 border rounded-lg hidden"
                                   min="35" max="43">
                        </div>

                        <div class="flex items-center bg-white p-3 rounded-lg">
                            <input type="checkbox" id="hasPain" class="mr-3">
                            <label for="hasPain" class="text-sm font-medium flex-1">Presenta dolor</label>
                            <input type="number" id="painLevel" placeholder="1-10" min="1" max="10"
                                   class="ml-4 w-24 p-2 border rounded-lg hidden">
                        </div>

                        <div class="bg-white p-3 rounded-lg">
                            <input type="checkbox" id="hasBreathingDifficulty" class="mr-3">
                            <label for="hasBreathingDifficulty" class="text-sm font-medium">Dificultad para respirar</label>
                        </div>
                        
                        <div class="bg-white p-3 rounded-lg">
                            <input type="checkbox" id="hasVomiting" class="mr-3">
                            <label for="hasVomiting" class="text-sm font-medium">Vómito</label>
                        </div>
                        
                        <div class="bg-white p-3 rounded-lg">
                            <input type="checkbox" id="hasConsciousnessLoss" class="mr-3">
                            <label for="hasConsciousnessLoss" class="text-sm font-medium">Pérdida de consciencia</label>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
    <label class="flex items-start space-x-3 cursor-pointer">
        <input type="checkbox" required class="mt-1 w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
        <span class="text-sm text-gray-600">
            Autorizo el tratamiento de mis datos personales sensibles (salud) para el proceso de Triage Médico, conforme a la <strong>Ley 1581 de 2012</strong> y la política de privacidad de IPS ADOM. Entiendo que la clasificación es realizada por Inteligencia Artificial y verificada por humanos.
        </span>
    </label>
</div>

                <!-- BOTÓN DE ENVÍO -->
                <div class="border-t-2 pt-6">
                    <button type="submit" 
                            class="w-full p-4 bg-blue-600 text-white rounded-lg font-bold text-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-colors shadow-lg">
                        🔍 Evaluar Triage
                    </button>
                    <p class="text-xs text-gray-500 mt-2 text-center">
                        * Todos los campos son obligatorios para una evaluación precisa
                    </p>
                </div>
            </form>

            <div id="triageResult" class="hidden mt-6"></div>
        </div>

        <div class="mt-4 text-xs text-white text-center opacity-75">
            En caso de emergencia evidente, diríjase inmediatamente al servicio de urgencias más cercano o llame al 123
        </div>
    </div>

    <script>
        const CONFIG = {
            API_URL: 'api/triage.php'
        };
    </script>
    <script src="js/triage.js"></script>
</body>
</html>
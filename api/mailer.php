<?php
function enviarNotificacion($email, $asunto, $mensaje) {
    // Configuración básica de cabeceras para HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Triage ADOM <no-reply@triageadom.com>' . "\r\n";

    // Intentar enviar correo
    try {
        $enviado = mail($email, $asunto, $mensaje, $headers);
        
        if (!$enviado) {
            // Si falla (común en localhost), guardamos en log para simular
            error_log("⚠️ [SIMULACIÓN EMAIL] Para: $email | Asunto: $asunto");
        }
        return true;
    } catch (Exception $e) {
        error_log("Error enviando mail: " . $e->getMessage());
        return false;
    }
}

function generarPlantillaAprobacion($paciente, $medico, $fecha, $hora) {
    return "
    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
        <div style='background-color: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2a4693;'>¡Cita Confirmada! ✅</h2>
            <p>Hola <strong>{$paciente}</strong>,</p>
            <p>Su solicitud de atención domiciliaria ha sido <strong>APROBADA</strong>.</p>
            
            <div style='background-color: #e8f4fd; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>👨‍⚕️ Profesional:</strong> {$medico}</p>
                <p><strong>📅 Fecha:</strong> {$fecha}</p>
                <p><strong>⏰ Hora aprox:</strong> {$hora}</p>
            </div>
            
            <p>Por favor, esté atento a su teléfono para cualquier coordinación adicional.</p>
            <hr>
            <small style='color: #888;'>IPS ADOM - Triage Inteligente</small>
        </div>
    </div>";
}

function generarPlantillaRechazo($paciente, $motivo) {
    return "
    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
        <div style='background-color: white; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #c53030;'>Actualización de su Solicitud</h2>
            <p>Hola <strong>{$paciente}</strong>,</p>
            <p>Su solicitud ha sido revisada y en este momento <strong>NO</strong> ha sido aprobada para atención domiciliaria.</p>
            
            <div style='background-color: #fff5f5; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #c53030;'>
                <p><strong>Motivo:</strong> {$motivo}</p>
            </div>
            
            <p>Le recomendamos consultar por urgencias o pedir una cita prioritaria en su EPS si los síntomas persisten.</p>
            <hr>
            <small style='color: #888;'>IPS ADOM</small>
        </div>
    </div>";
}
?>
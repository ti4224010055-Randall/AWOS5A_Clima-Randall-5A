<?php
// Capa de Procesos: Lógica para obtener datos del servicio
$ciudad = "Randall, Carmen, Campeche";
// Usamos una API pública de prueba (7-Timer) que no requiere registro para esta práctica inicial
$apiUrl = "http://www.7timer.info/bin/api.pl?lon=91.413&lat=18.453&product=civil&output=json";

$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

// Capa de Acceso: Presentación de los datos al usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mashup SOA - Unidad I</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; text-align: center; }
        .card { background: white; padding: 20px; border-radius: 8px; display: inline-block; margin-top: 50px; shadow: 0 2px 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="card">
        <h1>Servicio del Clima (5A)</h1>
        <p><strong>Ciudad:</strong> <?php echo $ciudad; ?></p>
        <p><strong>Pronóstico:</strong> <?php echo $data['dataseries'][0]['weather']; ?></p>
        <p><strong>Temperatura:</strong> <?php echo $data['dataseries'][0]['temp2m']; ?>°C</p>
        <p><strong>Viento:</strong> <?php echo $data['dataseries'][0]['wind10m']['direction']; ?> a <?php echo $data['dataseries'][0]['wind10m']['speed']; ?> km/h</p>
        
        <p><strong>Nivel de Nublado:</strong> <?php echo $data['dataseries'][0]['cloudcover']; ?> </p>
        <p><strong>Humedad:</strong> <?php echo $data['dataseries'][0]['rh2m']; ?></p>
        <p><strong>Precipitación:</strong> <?php echo $data['dataseries'][0]['prec_type']; ?></p>
        
    </div>
</body>
</html>
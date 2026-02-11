<?php
// Capa de Procesos: Lógica para obtener datos del servicio
$ciudad = "Carmen, Campeche, México";
$latitud = 18.453;
$longitud = -91.413;

// Usamos una API pública de prueba (7-Timer)
$apiUrl = "http://www.7timer.info/bin/api.pl?lon=" . $longitud . "&lat=" . $latitud . "&product=civil&output=json";

// Manejo de errores y excepciones
try {
    // Verificar si file_get_contents está habilitado para URLs
    if (!ini_get('allow_url_fopen')) {
        throw new Exception('allow_url_fopen está deshabilitado en la configuración PHP');
    }
    
    // Obtener datos de la API con timeout
    $context = stream_context_create([
        'http' => ['timeout' => 10]
    ]);
    
    $response = @file_get_contents($apiUrl, false, $context);
    
    if ($response === false) {
        throw new Exception('No se pudo conectar con el servicio meteorológico');
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar la respuesta JSON: ' . json_last_error_msg());
    }
    
    if (!isset($data['dataseries'][0])) {
        throw new Exception('No se encontraron datos meteorológicos para esta ubicación');
    }
    
    $pronostico = $data['dataseries'][0];
    
    // Mapear códigos meteorológicos a descripciones en español
    $climaDescripciones = [
        'clear' => 'Despejado',
        'pcloudy' => 'Parcialmente nublado',
        'mcloudy' => 'Mayormente nublado',
        'cloudy' => 'Nublado',
        'humid' => 'Húmedo',
        'lightrain' => 'Lluvia ligera',
        'rain' => 'Lluvia',
        'oshower' => 'Chubascos ocasionales',
        'ishower' => 'Chubascos aislados',
        'lightsnow' => 'Nieve ligera',
        'snow' => 'Nieve',
        'rainsnow' => 'Aguanieve'
    ];
    
    $clima = isset($pronostico['weather']) ? 
             ($climaDescripciones[$pronostico['weather']] ?? $pronostico['weather']) : 
             'No disponible';
    
    // Mapear dirección del viento
    $direccionesViento = [
        'N' => 'Norte',
        'NE' => 'Noreste',
        'E' => 'Este',
        'SE' => 'Sureste',
        'S' => 'Sur',
        'SW' => 'Suroeste',
        'W' => 'Oeste',
        'NW' => 'Noroeste'
    ];
    
    $direccionViento = isset($pronostico['wind10m']['direction']) ? 
                      ($direccionesViento[$pronostico['wind10m']['direction']] ?? $pronostico['wind10m']['direction']) : 
                      'No disponible';
    
    // Mapear tipo de precipitación
    $tiposPrecipitacion = [
        'none' => 'Sin precipitación',
        'rain' => 'Lluvia',
        'snow' => 'Nieve'
    ];
    
    $precipitacion = isset($pronostico['prec_type']) ? 
                    ($tiposPrecipitacion[$pronostico['prec_type']] ?? $pronostico['prec_type']) : 
                    'No disponible';
    
} catch (Exception $e) {
    $error = $e->getMessage();
    $clima = 'No disponible';
    $temperatura = '--';
    $direccionViento = 'No disponible';
    $velocidadViento = '--';
    $nubosidad = '--';
    $humedad = '--';
    $precipitacion = 'No disponible';
}

// Capa de Presentación: Mostrar los datos al usuario
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mashup SOA - Servicio Meteorológico</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            width: 100%;
        }
        
        .card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
            background: linear-gradient(90deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .ciudad {
            text-align: center;
            font-size: 1.5em;
            color: #555;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .datos-clima {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .dato-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid #667eea;
        }
        
        .dato-label {
            font-size: 0.9em;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .dato-valor {
            font-size: 1.4em;
            color: #333;
            font-weight: 700;
        }
        
        .icono {
            display: inline-block;
            margin-right: 10px;
            color: #667eea;
        }
        
        .error {
            background: #fee;
            border: 1px solid #fcc;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
            color: #c33;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #888;
            font-size: 0.9em;
        }
        
        @media (max-width: 600px) {
            .card {
                padding: 20px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .datos-clima {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🌤 Servicio Meteorológico</h1>
            
            <div class="ciudad">
                📍 <?php echo htmlspecialchars($ciudad); ?>
                <div style="font-size: 0.8em; color: #888; margin-top: 5px;">
                    Lat: <?php echo $latitud; ?>, Lon: <?php echo $longitud; ?>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error">
                    ⚠️ Error: <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="datos-clima">
                <div class="dato-item">
                    <div class="dato-label">
                        <span class="icono">☁️</span> Estado del Clima
                    </div>
                    <div class="dato-valor"><?php echo htmlspecialchars($clima); ?></div>
                </div>
                
                <div class="dato-item">
                    <div class="dato-label">
                        <span class="icono">🌡️</span> Temperatura
                    </div>
                    <div class="dato-valor">
                        <?php echo isset($pronostico['temp2m']) ? htmlspecialchars($pronostico['temp2m']) : '--'; ?>°C
                    </div>
                </div>
                
                <div class="dato-item">
                    <div class="dato-label">
                        <span class="icono">💨</span> Viento
                    </div>
                    <div class="dato-valor">
                        <?php echo $direccionViento; ?> a 
                        <?php echo isset($pronostico['wind10m']['speed']) ? 
                              htmlspecialchars($pronostico['wind10m']['speed']) : '--'; ?> km/h
                    </div>
                </div>
                
                <div class="dato-item">
                    <div class="dato-label">
                        <span class="icono">☁️</span> Nubosidad
                    </div>
                    <div class="dato-valor">
                        <?php echo isset($pronostico['cloudcover']) ? 
                              htmlspecialchars($pronostico['cloudcover']) . '%' : '--'; ?>
                    </div>
                </div>
                
                <div class="dato-item">
                    <div class="dato-label">
                        <span class="icono">💧</span> Humedad
                    </div>
                    <div class="dato-valor">
                        <?php echo isset($pronostico['rh2m']) ? 
                              htmlspecialchars($pronostico['rh2m']) . '%' : '--'; ?>
                    </div>
                </div>
                
                <div class="dato-item">
                    <div class="dato-label">
                        <span class="icono">🌧️</span> Precipitación
                    </div>
                    <div class="dato-valor"><?php echo $precipitacion; ?></div>
                </div>
            </div>
            
            <div class="footer">
                <p>📡 Datos proporcionados por 7Timer! API</p>
                <p>🔄 Última actualización: <?php echo date('d/m/Y H:i:s'); ?></p>
                <p>⚙️ Mashup SOA - Unidad I - Ejemplo de integración de servicios</p>
            </div>
        </div>
    </div>
    
    <script>
        // Pequeño script para mejorar la experiencia de usuario
        document.addEventListener('DOMContentLoaded', function() {
            // Agregar efecto de carga inicial
            const card = document.querySelector('.card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
            
            // Actualizar automáticamente cada 5 minutos (300000 ms)
            setTimeout(() => {
                window.location.reload();
            }, 300000);
        });
    </script>
</body>
</html>
<?php
/**
 * Script de prueba para verificar que la API funciona correctamente
 * Ejecutar desde navegador: http://localhost/gestion_academica/backend/test_api.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de API - Sistema de Gestión Académica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #007bff;
            margin-top: 20px;
        }
        .endpoint {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 10px 0;
        }
        .method {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            margin-right: 10px;
        }
        .get { background: #28a745; color: white; }
        .post { background: #007bff; color: white; }
        .put { background: #ffc107; color: black; }
        .delete { background: #dc3545; color: white; }
        .test-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        .test-button:hover {
            background: #0056b3;
        }
        .result {
            background: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .success { border-left: 4px solid #28a745; }
        .error { border-left: 4px solid #dc3545; }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }
        .status.ok { background: #28a745; color: white; }
        .status.fail { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Prueba de API - Sistema de Gestión Académica</h1>
        <p>Este script prueba los endpoints principales de la API.</p>
    </div>

    <div class="container">
        <h2>📋 Endpoints de Docentes</h2>
        
        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/docentes</strong> - Listar todos los docentes
            <button class="test-button" onclick="testEndpoint('api/docentes', 'GET', 'docentes-list')">Probar</button>
            <div id="docentes-list" class="result" style="display:none;"></div>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/docentes/stats</strong> - Estadísticas de docentes
            <button class="test-button" onclick="testEndpoint('api/docentes/stats', 'GET', 'docentes-stats')">Probar</button>
            <div id="docentes-stats" class="result" style="display:none;"></div>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/docentes/1</strong> - Obtener docente por ID
            <button class="test-button" onclick="testEndpoint('api/docentes/1', 'GET', 'docente-single')">Probar</button>
            <div id="docente-single" class="result" style="display:none;"></div>
        </div>
    </div>

    <div class="container">
        <h2>🎫 Endpoints de Incidencias</h2>
        
        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/incidencias</strong> - Listar todas las incidencias
            <button class="test-button" onclick="testEndpoint('api/incidencias', 'GET', 'incidencias-list')">Probar</button>
            <div id="incidencias-list" class="result" style="display:none;"></div>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/incidencias/stats</strong> - Estadísticas de incidencias
            <button class="test-button" onclick="testEndpoint('api/incidencias/stats', 'GET', 'incidencias-stats')">Probar</button>
            <div id="incidencias-stats" class="result" style="display:none;"></div>
        </div>
    </div>

    <div class="container">
        <h2>📊 Endpoints de Reportes</h2>
        
        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/reportes?tipo=dashboard</strong> - Dashboard principal
            <button class="test-button" onclick="testEndpoint('api/reportes?tipo=dashboard', 'GET', 'reportes-dashboard')">Probar</button>
            <div id="reportes-dashboard" class="result" style="display:none;"></div>
        </div>

        <div class="endpoint">
            <span class="method get">GET</span>
            <strong>/api/reportes?tipo=docentes_por_academia</strong> - Docentes por academia
            <button class="test-button" onclick="testEndpoint('api/reportes?tipo=docentes_por_academia', 'GET', 'reportes-academia')">Probar</button>
            <div id="reportes-academia" class="result" style="display:none;"></div>
        </div>
    </div>

    <script>
        function testEndpoint(url, method, resultId) {
            const resultDiv = document.getElementById(resultId);
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Cargando...';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                const status = response.status;
                return response.json().then(data => ({
                    status: status,
                    data: data
                }));
            })
            .then(result => {
                const className = result.data.success ? 'success' : 'error';
                resultDiv.className = 'result ' + className;
                resultDiv.innerHTML = `<strong>Status: ${result.status}</strong>\n\n${JSON.stringify(result.data, null, 2)}`;
            })
            .catch(error => {
                resultDiv.className = 'result error';
                resultDiv.innerHTML = `Error: ${error.message}`;
            });
        }
    </script>
</body>
</html>

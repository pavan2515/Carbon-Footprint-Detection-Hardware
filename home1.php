<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emission Heat Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .controls-panel {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }

        .control-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        select {
            padding: 10px;
            border-radius: 10px;
            border: none;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1em;
        }

        #map {
            height: 600px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .legend {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 10px;
            color: #333;
            margin-top: 20px;
        }

        .legend h3 {
            margin-bottom: 10px;
            color: #667eea;
        }

        .legend-scale {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .color-bar {
            width: 300px;
            height: 30px;
            background: linear-gradient(to right, #0000ff, #00ffff, #00ff00, #ffff00, #ff0000);
            border-radius: 5px;
        }

        .stats-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .stat-value {
            font-size: 2.5em;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            font-size: 1em;
            opacity: 0.8;
        }

        .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
        }

        .popup-content {
            color: #333;
        }

        .popup-content h4 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .emission-high {
            color: #ff0000;
            font-weight: bold;
        }

        .emission-medium {
            color: #ff9800;
            font-weight: bold;
        }

        .emission-low {
            color: #4CAF50;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌍 Emission Heat Map Dashboard</h1>
            <p>Real-time Regional Emission Monitoring</p>
        </div>

        <div class="controls-panel">
            <div class="control-row">
                <label>Pollutant Type:</label>
                <select id="pollutantType" onchange="updateHeatMap()">
                    <option value="total">Total Emissions</option>
                    <option value="co">Carbon Monoxide (CO)</option>
                    <option value="co2">Carbon Dioxide (CO₂)</option>
                    <option value="nox">Nitrogen Oxides (NOx)</option>
                </select>

                <label>Time Range:</label>
                <select id="timeRange" onchange="updateHeatMap()">
                    <option value="1">Last Hour</option>
                    <option value="3">Last 3 Hours</option>
                    <option value="24">Last 24 Hours</option>
                    <option value="168">Last Week</option>
                </select>

                <button class="btn" onclick="refreshData()">🔄 Refresh Data</button>
                <button class="btn" onclick="exportHeatMapData()">📊 Export Data</button>
            </div>
        </div>

        <div id="map"></div>

        <div class="legend">
            <h3>Emission Level Legend</h3>
            <div class="legend-scale">
                <span>Low</span>
                <div class="color-bar"></div>
                <span>High</span>
            </div>
            <p><strong>Blue:</strong> Safe (0-800 ppm) | <strong>Cyan/Green:</strong> Moderate (800-1500 ppm) | <strong>Yellow/Orange:</strong> Elevated (1500-2000 ppm) | <strong>Red:</strong> Critical (2000+ ppm)</p>
        </div>

        <div class="stats-panel">
            <div class="stat-card">
                <div class="stat-label">Total Sensors</div>
                <div class="stat-value" id="totalSensors">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Average Emission</div>
                <div class="stat-value" id="avgEmission">0</div>
                <div class="stat-label">ppm</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Critical Zones</div>
                <div class="stat-value" id="criticalZones">0</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Data Points</div>
                <div class="stat-value" id="dataPoints">0</div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    <script>
        // Initialize map centered on India (you can change to your region)
        const map = L.map('map').setView([20.5937, 78.9629], 5);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(map);

        let heatLayer;
        let markers = [];
        let sensorData = [];

        // Simulated sensor locations across different regions
        function generateSensorData() {
            const regions = [
                // Major cities in India with different emission levels
                { name: "Delhi", lat: 28.7041, lng: 77.1025, base: 2500 },
                { name: "Mumbai", lat: 19.0760, lng: 72.8777, base: 2000 },
                { name: "Bangalore", lat: 12.9716, lng: 77.5946, base: 1500 },
                { name: "Chennai", lat: 13.0827, lng: 80.2707, base: 1600 },
                { name: "Kolkata", lat: 22.5726, lng: 88.3639, base: 1800 },
                { name: "Hyderabad", lat: 17.3850, lng: 78.4867, base: 1400 },
                { name: "Pune", lat: 18.5204, lng: 73.8567, base: 1300 },
                { name: "Ahmedabad", lat: 23.0225, lng: 72.5714, base: 1700 },
                { name: "Jaipur", lat: 26.9124, lng: 75.7873, base: 1200 },
                { name: "Lucknow", lat: 26.8467, lng: 80.9462, base: 1500 },
                
                // Additional data points for better heat map
                { name: "Mysuru", lat: 12.2958, lng: 76.6394, base: 900 },
                { name: "Chandigarh", lat: 30.7333, lng: 76.7794, base: 1100 },
                { name: "Kochi", lat: 9.9312, lng: 76.2673, base: 800 },
                { name: "Indore", lat: 22.7196, lng: 75.8577, base: 1300 },
                { name: "Visakhapatnam", lat: 17.6868, lng: 83.2185, base: 1000 }
            ];

            sensorData = [];

            regions.forEach(region => {
                // Create multiple sensor points around each city
                for (let i = 0; i < 5; i++) {
                    const lat = region.lat + (Math.random() - 0.5) * 0.3;
                    const lng = region.lng + (Math.random() - 0.5) * 0.3;
                    
                    const co = Math.floor(region.base * 0.3 + Math.random() * 100);
                    const co2 = Math.floor(region.base * 0.5 + Math.random() * 200);
                    const nox = Math.floor(region.base * 0.2 + Math.random() * 80);
                    const total = co + co2 + nox;

                    sensorData.push({
                        location: region.name,
                        lat: lat,
                        lng: lng,
                        co: co,
                        co2: co2,
                        nox: nox,
                        total: total,
                        timestamp: new Date()
                    });
                }
            });

            updateStatistics();
        }

        // Create heat map layer
        function createHeatMap() {
            // Remove existing heat layer
            if (heatLayer) {
                map.removeLayer(heatLayer);
            }

            // Remove existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            const pollutantType = document.getElementById('pollutantType').value;
            const heatData = [];

            sensorData.forEach(sensor => {
                let intensity;
                switch(pollutantType) {
                    case 'co':
                        intensity = sensor.co / 10;
                        break;
                    case 'co2':
                        intensity = sensor.co2 / 20;
                        break;
                    case 'nox':
                        intensity = sensor.nox / 5;
                        break;
                    default:
                        intensity = sensor.total / 30;
                }

                heatData.push([sensor.lat, sensor.lng, intensity]);

                // Add marker with popup
                const level = sensor.total < 800 ? 'low' : sensor.total < 1500 ? 'medium' : 'high';
                const markerColor = level === 'low' ? 'green' : level === 'medium' ? 'orange' : 'red';

                const marker = L.circleMarker([sensor.lat, sensor.lng], {
                    radius: 8,
                    fillColor: markerColor,
                    color: '#fff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.7
                }).addTo(map);

                const popupContent = `
                    <div class="popup-content">
                        <h4>📍 ${sensor.location}</h4>
                        <p><strong>CO:</strong> ${sensor.co} ppm</p>
                        <p><strong>CO₂:</strong> ${sensor.co2} ppm</p>
                        <p><strong>NOx:</strong> ${sensor.nox} ppm</p>
                        <p><strong>Total:</strong> <span class="emission-${level}">${sensor.total} ppm</span></p>
                        <p><small>${sensor.timestamp.toLocaleString()}</small></p>
                    </div>
                `;

                marker.bindPopup(popupContent);
                markers.push(marker);
            });

            // Create heat layer
            heatLayer = L.heatLayer(heatData, {
                radius: 40,
                blur: 35,
                maxZoom: 17,
                max: 1.0,
                gradient: {
                    0.0: 'blue',
                    0.3: 'cyan',
                    0.5: 'lime',
                    0.7: 'yellow',
                    1.0: 'red'
                }
            }).addTo(map);
        }

        // Update statistics
        function updateStatistics() {
            document.getElementById('totalSensors').textContent = 
                [...new Set(sensorData.map(s => s.location))].length;
            
            const avgEmission = Math.round(
                sensorData.reduce((sum, s) => sum + s.total, 0) / sensorData.length
            );
            document.getElementById('avgEmission').textContent = avgEmission;

            const criticalCount = sensorData.filter(s => s.total > 2000).length;
            document.getElementById('criticalZones').textContent = criticalCount;

            document.getElementById('dataPoints').textContent = sensorData.length;
        }

        // Update heat map
        function updateHeatMap() {
            createHeatMap();
        }

        // Refresh data (simulate new data)
        function refreshData() {
            generateSensorData();
            createHeatMap();
            console.log('Data refreshed');
        }

        // Export data
        function exportHeatMapData() {
            const dataStr = JSON.stringify(sensorData, null, 2);
            const blob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `emission_heatmap_${new Date().toISOString()}.json`;
            a.click();
            console.log('Heat map data exported');
        }

        // Auto-refresh every 30 seconds
        setInterval(refreshData, 30000);

        // Initialize on page load
        window.onload = function() {
            generateSensorData();
            createHeatMap();
        };
    </script>
</body>
</html>
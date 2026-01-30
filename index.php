<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | SOC Command Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #05080a;
            --panel: rgba(10, 20, 30, 0.9);
            --accent: #00f2ff;
            --danger: #ff3e3e;
            --warning: #ffbe00;
            --success: #00ff88;
            --border: rgba(0, 242, 255, 0.3);
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #fff;
            overflow: hidden;
        }

        .main-container {
            display: flex;
            height: calc(100vh - 30px);
            width: 100%;
        }

        /* Left Panel - CCTV */
        .left-panel {
            flex: 0 0 20%;
            background: #000;
            border-right: 2px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .feed-header {
            background: var(--panel);
            padding: 10px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.7rem;
            color: var(--accent);
            border-bottom: 1px solid var(--border);
        }

        .video-box {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.6;
            filter: grayscale(0.5);
        }

        .scan-line {
            position: absolute;
            top: 0;
            width: 100%;
            height: 2px;
            background: rgba(0, 242, 255, 0.2);
            box-shadow: 0 0 10px var(--accent);
            animation: scan 4s linear infinite;
            pointer-events: none;
        }

        @keyframes scan {
            from {
                top: 0
            }

            to {
                top: 100%
            }
        }

        /* Right Panel - GIS */
        .right-panel {
            flex: 1;
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
            background: #000;
        }

        /* Overlays */
        .branding {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1000;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            color: var(--accent);
            background: var(--panel);
            padding: 10px 20px;
            border: 1px solid var(--border);
            letter-spacing: 2px;
        }

        .telemetry {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 4px;
            width: 180px;
            backdrop-filter: blur(5px);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 0.6rem;
            color: #888;
            text-transform: uppercase;
        }

        .stat-card .val {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            color: var(--accent);
            margin-top: 5px;
        }

        .ticker {
            height: 30px;
            background: #000;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            overflow: hidden;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.65rem;
            color: var(--accent);
        }

        .ticker-wrap {
            white-space: nowrap;
            padding-left: 100%;
            animation: ticker 30s linear infinite;
        }

        @keyframes ticker {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--panel);
            padding: 10px;
            border: 1px solid var(--border);
            font-size: 0.7rem;
        }

        .l-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
    </style>
</head>

<body>
    <div class="branding">OPTISPACE // SOC</div>

    <div class="main-container">
        <div class="left-panel">
            <div class="feed-header">LIVE FEED | CAM-01</div>
            <div class="video-box">
                <div class="scan-line"></div>
                <video autoplay muted loop playsinline src="parking_loop.mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        <div class="right-panel">
            <div id="map"></div>

            <div class="telemetry">
                <div class="stat-card">
                    <h3>Operational Revenue</h3>
                    <div class="val" id="revenue">₹0.00</div>
                </div>
                <div class="stat-card">
                    <h3>CO2 Emissions Saved</h3>
                    <div class="val" id="co2">0.00 kg</div>
                </div>
            </div>

            <div class="legend">
                <div class="l-item"><span class="dot" style="background:var(--success)"></span> Secure (Free)</div>
                <div class="l-item"><span class="dot" style="background:var(--danger)"></span> Active (Occupied)</div>
            </div>
        </div>
    </div>

    <div class="ticker">
        <div class="ticker-wrap">
            CONNECTED TO ESRI ARCGIS SERVICES... SYNCING TRV GROUND LOGISTICS DATA... STATUS: OPTIMAL... ENFORCING
            GEOFENCE PROTOCOLS...
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ESRI Layers
        const worldImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
        const worldLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });

        const map = L.map('map', {
            center: [8.488000, 76.923000],
            zoom: 19,
            zoomControl: false,
            attributionControl: false,
            layers: [worldImagery, worldLabels]
        });

        let polygons = {};

        async function updateDashboard() {
            try {
                const response = await fetch('logic.php?action=fetch_status');
                const data = await response.json();

                if (!data || !data.stats) {
                    console.warn("Invalid telemetry data received");
                    return;
                }

                document.getElementById('revenue').innerText = '₹' + data.stats.revenue.toFixed(2);
                document.getElementById('co2').innerText = data.stats.co2_saved.toFixed(2) + ' kg';

                data.slots.forEach(slot => {
                    const color = slot.status === 'free' ? '#00ff88' : '#ff3e3e';

                    if (polygons[slot.id]) {
                        polygons[slot.id].setStyle({ fillColor: color, color: color });
                    } else {
                        // Using precise circle markers for TRV layout
                        const marker = L.circle([slot.lat, slot.lng], {
                            radius: 1,
                            fillColor: color,
                            fillOpacity: 0.6,
                            color: color,
                            weight: 2
                        }).addTo(map);

                        marker.bindPopup(`
                            <div style="font-family:'Orbitron'; font-size:0.8rem; color:var(--accent);">
                                <b>${slot.slot_id}</b><br>
                                <span style="color:#888;">Zone:</span> ${slot.zone_type.toUpperCase()}<br>
                                <span style="color:#888;">Status:</span> ${slot.status.toUpperCase()}
                            </div>
                        `);
                        polygons[slot.id] = marker;
                    }
                });

            } catch (err) {
                console.error("Dashboard Sync Error:", err);
            }
        }

        setInterval(updateDashboard, 2000);
        updateDashboard();
    </script>
</body>

</html>
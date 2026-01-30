<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | TRV Airport SOC</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@300;400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #030507;
            --panel: rgba(8, 12, 16, 0.95);
            --accent: #00f2ff;
            --danger: #ff3e3e;
            --warning: #ffbe00;
            --success: #00ff88;
            --border: rgba(0, 242, 255, 0.25);
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

        .soc-container {
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

        .panel-header {
            background: var(--panel);
            padding: 12px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.7rem;
            color: var(--accent);
            border-bottom: 1px solid var(--border);
            letter-spacing: 1px;
        }

        .cctv-feed {
            flex: 1;
            background: #080808;
            position: relative;
            overflow: hidden;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(0.4) brightness(0.8);
            opacity: 0.7;
        }

        .scanline {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: rgba(0, 242, 255, 0.15);
            box-shadow: 0 0 10px var(--accent);
            animation: scan 6s linear infinite;
            pointer-events: none;
            z-index: 10;
        }

        @keyframes scan {
            from {
                top: 0%
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

        /* Overlay Elements */
        .soc-header {
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 1000;
            font-family: 'Orbitron', sans-serif;
            background: var(--panel);
            padding: 10px 20px;
            border: 1px solid var(--border);
            border-left: 5px solid var(--accent);
        }

        .overlay-right {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 200px;
        }

        .stat-box {
            background: var(--panel);
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 4px;
            backdrop-filter: blur(5px);
        }

        .stat-box h3 {
            margin: 0;
            font-size: 0.6rem;
            color: #888;
            text-transform: uppercase;
        }

        .stat-box .val {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            color: var(--accent);
            margin-top: 5px;
        }

        .lidar-btn {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            font-size: 0.7rem;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
        }

        .lidar-btn:hover {
            background: #fff;
            box-shadow: 0 0 25px var(--accent);
            transform: translateY(-2px);
        }

        .soc-ticker {
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

        .ticker-mover {
            white-space: nowrap;
            padding-left: 100%;
            animation: move 25s linear infinite;
        }

        @keyframes move {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-100%);
            }
        }

        /* Legend */
        .legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--panel);
            border: 1px solid var(--border);
            padding: 10px;
            font-size: 0.7rem;
            border-radius: 4px;
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
    <div class="soc-container">
        <div class="left-panel">
            <div class="panel-header">TRV-AIRPORT // CCTV_T2</div>
            <div class="cctv-feed">
                <div class="scanline"></div>
                <!-- Placeholder loop video from mixkit -->
                <video autoplay muted loop playsinline
                    src="https://assets.mixkit.co/videos/preview/mixkit-security-camera-at-the-entrance-of-a-toll-road-33825-large.mp4"></video>
            </div>
        </div>
        <div class="right-panel">
            <div class="soc-header">TRV TERMINAL 2 DASHBOARD</div>
            <div id="map"></div>

            <div class="overlay-right">
                <div class="stat-box">
                    <h3>Operational Load</h3>
                    <div class="val" id="entries">0</div>
                </div>
                <div class="stat-box">
                    <h3>Security Alerts</h3>
                    <div class="val" id="alerts" style="color: var(--danger);">0</div>
                </div>
                <button class="lidar-btn"
                    onclick="alert('NeST Cloud: LiDAR Integration Initializing... [Access Denied]')">
                    LOAD LiDAR 3D VIEW
                </button>
            </div>

            <div class="legend">
                <div class="l-item"><span class="dot" style="background:var(--success)"></span> Secure (Free)</div>
                <div class="l-item"><span class="dot" style="background:var(--danger)"></span> Active (Occupied)</div>
                <div class="l-item"><span class="dot" style="background:var(--warning)"></span> Audit (Inefficient)
                </div>
            </div>
        </div>
    </div>
    <div class="soc-ticker">
        <div class="ticker-mover">
            CONNECTED TO NeST DATA LAKE... SYNCING TRV TERMINAL 2 GROUND LOGISTICS... ESRI ARCGIS SERVICES
            OPERATIONAL... MONITORING GEOFENCE ALPHA... ZONE L PROTOCOLS ACTIVE...
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const imagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
        const labels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });

        const map = L.map('map', { center: [8.488000, 76.923000], zoom: 19, zoomControl: false, layers: [imagery, labels] });

        let markers = {};

        function updateSOC() {
            fetch('logic.php?action=get_status')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('entries').innerText = data.stats.total_entries;
                    document.getElementById('alerts').innerText = data.stats.alerts_triggered;

                    data.slots.forEach(slot => {
                        const color = slot.status === 'free' ? '#00ff88' : (slot.status === 'occupied' ? '#ff3e3e' : '#ffbe00');

                        if (markers[slot.id]) {
                            markers[slot.id].setStyle({ fillColor: color, color: color });
                        } else {
                            // Circular representation for slots in TRV
                            const marker = L.circle([slot.lat, slot.lng], {
                                radius: 1,
                                fillColor: color,
                                fillOpacity: 0.6,
                                color: color,
                                weight: 2
                            }).addTo(map);

                            marker.bindPopup(`
                                <div style="font-family:'Orbitron'; font-size:0.75rem; color:#00f2ff;">
                                    <b>${slot.slot_id}</b><br>
                                    <span style="color:#888;">Zone:</span> ${slot.zone_type.toUpperCase()}<br>
                                    <span style="color:#888;">Vehicle:</span> ${slot.current_vehicle || 'NONE'}
                                </div>
                            `);
                            markers[slot.id] = marker;
                        }
                    });
                });
        }

        setInterval(updateSOC, 2000);
        updateSOC();
    </script>
</body>

</html>
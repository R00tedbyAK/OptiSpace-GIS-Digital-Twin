<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | Decision Support System v11</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link
        href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --accent: #00f2ff;
            --glass: rgba(0, 0, 0, 0.8);
            --border: rgba(0, 242, 255, 0.25);
            --suv: #FFD700;
            --logistics: #D000FF;
            --bike: #00FFFF;
            --free: #00FF00;
            --occupied: #FF0000;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: 'Roboto Mono', monospace;
            background: #050709;
            color: #fff;
        }

        /* ZONE A: THE MAP (Full Background) */
        #map {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        /* HUD LAYER */
        .hud {
            position: fixed;
            z-index: 1000;
            pointer-events: auto;
        }

        .glass {
            background: var(--glass);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
        }

        /* ZONE C: TOP BAR (Performance Header) */
        #top-bar {
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-bottom: 2px solid var(--border);
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 2px;
            color: var(--accent);
        }

        .metrics {
            display: flex;
            gap: 40px;
        }

        .metric {
            text-align: right;
        }

        .metric-value {
            font-family: 'Orbitron';
            font-size: 1rem;
            display: block;
        }

        .metric-label {
            font-size: 0.6rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background: #00ff00;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            animation: pulse 1.5s infinite;
            box-shadow: 0 0 8px #00ff00;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        /* ZONE C: RIGHT SIDEBAR (Zone Breakdown + Event Log) */
        #sidebar {
            top: 80px;
            right: 20px;
            bottom: 20px;
            width: 300px;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
        }

        .panel-title {
            font-family: 'Orbitron';
            font-size: 0.75rem;
            color: var(--accent);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .zone-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            margin-bottom: 10px;
        }

        .zone-row span:first-child {
            color: #aaa;
        }

        #event-log {
            flex: 1;
            overflow-y: auto;
            margin-top: 20px;
            border-top: 1px solid var(--border);
            padding-top: 15px;
        }

        .log-entry {
            font-size: 0.65rem;
            color: #bbb;
            margin-bottom: 10px;
            padding-left: 10px;
            border-left: 2px solid var(--accent);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* ZONE B: CCTV PANEL (Bottom-Left) */
        #cctv-panel {
            bottom: 20px;
            left: 20px;
            width: 340px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .cam-box {
            position: relative;
            height: 150px;
            background: #000;
            border: 1px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }

        .cam-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.85) contrast(1.15) grayscale(0.15);
        }

        .cam-rotate {
            transform: rotate(180deg);
        }

        .cam-label {
            position: absolute;
            top: 8px;
            left: 8px;
            font-family: 'Orbitron';
            font-size: 0.5rem;
            color: #fff;
            background: rgba(0, 0, 0, 0.7);
            padding: 3px 8px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 5;
        }

        .rec-dot {
            width: 5px;
            height: 5px;
            background: #ff0000;
            border-radius: 50%;
            animation: pulse 1s infinite;
        }

        .ar-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.75);
            padding: 8px 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.7rem;
            color: #00ff00;
            font-weight: bold;
            z-index: 5;
            text-shadow: 0 0 5px rgba(0, 255, 0, 0.5);
        }

        .payment-flash {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #00ff00;
            color: #000;
            padding: 8px 12px;
            font-family: 'Orbitron';
            font-size: 0.65rem;
            font-weight: bold;
            display: none;
            z-index: 10;
            box-shadow: 0 0 20px #00ff00;
            white-space: nowrap;
        }

        /* DATA PACKET ANIMATION */
        .data-trail {
            opacity: 0.25;
        }

        .data-packet {
            filter: drop-shadow(0 0 6px #fff) drop-shadow(0 0 10px var(--accent));
        }
    </style>
</head>

<body>
    <!-- ZONE A: THE MAP -->
    <div id="map"></div>

    <!-- ZONE C: TOP BAR -->
    <div class="hud glass" id="top-bar">
        <div class="brand">OPTISPACE // COMMAND CENTER</div>
        <div class="metrics">
            <div class="metric">
                <span class="metric-label">DAILY REVENUE</span>
                <span class="metric-value">₹ <span id="rev-counter">0</span></span>
            </div>
            <div class="metric">
                <span class="metric-label">SITE OCCUPANCY</span>
                <span class="metric-value"><span id="occ-counter">0</span> / <span id="total-slots">0</span></span>
            </div>
            <div class="metric">
                <span class="metric-label">STATUS</span>
                <span class="metric-value"><span class="live-dot"></span>LIVE</span>
            </div>
        </div>
    </div>

    <!-- ZONE C: RIGHT SIDEBAR -->
    <div class="hud glass" id="sidebar">
        <div class="panel-title">ZONE BREAKDOWN</div>
        <div class="zone-row"><span>🚗 GENERAL</span><span id="z-gen">0 / 0</span></div>
        <div class="zone-row" style="color: var(--suv)"><span>🚙 SUV (PREMIUM)</span><span id="z-suv">0 / 0</span></div>
        <div class="zone-row" style="color: var(--logistics)"><span>🚚 LOGISTICS</span><span id="z-log">0 / 0</span>
        </div>
        <div class="zone-row" style="color: var(--bike)"><span>🏍️ BIKES</span><span id="z-bike">0 / 0</span></div>

        <div class="panel-title" style="margin-top: 25px;">LIVE EVENT LOG</div>
        <div id="event-log"></div>
    </div>

    <!-- ZONE B: CCTV PANEL -->
    <div class="hud" id="cctv-panel">
        <!-- EXIT CAM (Top, Rotated) -->
        <div class="cam-box">
            <div class="cam-label"><span class="rec-dot"></span> CAM-02 [EXIT]</div>
            <div class="payment-flash" id="pay-flash">✔ PAYMENT RECEIVED: ₹150</div>
            <video class="cam-rotate" id="vid-exit" src="cctv/VID2.mp4" autoplay muted loop></video>
            <div class="ar-overlay" id="exit-ar">> MONITORING EXIT LANE...</div>
        </div>
        <!-- ENTRY CAM (Bottom, Normal) -->
        <div class="cam-box">
            <div class="cam-label"><span class="rec-dot"></span> CAM-01 [ENTRY]</div>
            <video id="vid-entry" src="cctv/VID1.mp4" autoplay muted loop></video>
            <div class="ar-overlay" id="entry-ar">> SCANNING ARRIVALS...</div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // === ZONE A: MAP INITIALIZATION ===
        const map = L.map('map', {
            center: [8.488533, 76.921948],
            zoom: 19,
            zoomControl: false,
            attributionControl: false
        });
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(map);

        const GATE = [8.488533, 76.921948];
        let slotMarkers = {};
        let slotsData = [];
        let totalRevenue = 0;

        // Force 0.5x Playback Speed
        document.getElementById('vid-entry').playbackRate = 0.5;
        document.getElementById('vid-exit').playbackRate = 0.5;

        // === DATA LOADING ===
        async function init() {
            const res = await fetch('logic.php?action=fetch_status');
            const data = await res.json();
            slotsData = data.slots;

            data.slots.forEach(s => {
                let color = s.status === 'free' ?
                    (s.zone_type === 'suv' ? '#FFD700' : s.zone_type === 'logistics' ? '#D000FF' : s.zone_type === 'bike' ? '#00FFFF' : '#00FF00')
                    : '#FF0000';

                const m = L.circle([parseFloat(s.lat), parseFloat(s.lng)], {
                    radius: 2.5, fillColor: color, fillOpacity: 0.9, color: color, weight: 1
                }).addTo(map);
                slotMarkers[s.slot_id] = { m, type: s.zone_type };
            });

            updateHUD();
            startAutopilot();
        }

        // === HUD UPDATES ===
        function updateHUD() {
            document.getElementById('rev-counter').innerText = totalRevenue.toLocaleString();
            let occ = slotsData.filter(s => s.status !== 'free').length;
            document.getElementById('occ-counter').innerText = occ;
            document.getElementById('total-slots').innerText = slotsData.length;

            let zones = { general: { o: 0, t: 0 }, suv: { o: 0, t: 0 }, logistics: { o: 0, t: 0 }, bike: { o: 0, t: 0 } };
            slotsData.forEach(s => {
                zones[s.zone_type].t++;
                if (s.status !== 'free') zones[s.zone_type].o++;
            });
            document.getElementById('z-gen').innerText = `${zones.general.o} / ${zones.general.t}`;
            document.getElementById('z-suv').innerText = `${zones.suv.o} / ${zones.suv.t}`;
            document.getElementById('z-log').innerText = `${zones.logistics.o} / ${zones.logistics.t}`;
            document.getElementById('z-bike').innerText = `${zones.bike.o} / ${zones.bike.t}`;
        }

        function pushLog(msg) {
            const log = document.getElementById('event-log');
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            const time = new Date().toLocaleTimeString([], { hour12: false });
            entry.innerHTML = `<span style="color:var(--accent)">[${time}]</span> ${msg}`;
            log.prepend(entry);
            if (log.children.length > 25) log.lastChild.remove();
        }

        // === AUTOPILOT ENGINE ===
        function startAutopilot() {
            const entryAR = document.getElementById('entry-ar');
            const exitAR = document.getElementById('exit-ar');

            // Entry Loop (3s)
            setInterval(async () => {
                entryAR.innerText = "> SCANNING...";
                await sleep(800);
                entryAR.innerText = "> CLASS: SUV";
                await sleep(800);
                entryAR.innerText = "> ALLOCATING SLOT...";

                const free = slotsData.filter(s => s.status === 'free');
                if (free.length > 0) {
                    const target = free[Math.floor(Math.random() * free.length)];
                    fireDataPacket(target);
                }
            }, 3000);

            // Exit Loop (10s)
            setInterval(async () => {
                exitAR.innerText = "> VEHICLE EXITING...";
                await sleep(1500);
                exitAR.innerText = "> CALCULATING FEE...";

                const occ = slotsData.filter(s => s.status !== 'free');
                if (occ.length > 0) {
                    const target = occ[Math.floor(Math.random() * occ.length)];
                    handleExit(target);
                }
            }, 10000);
        }

        function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }

        // === DATA PACKET ANIMATION ===
        function fireDataPacket(slot) {
            const start = L.latLng(GATE);
            const end = L.latLng(slot.lat, slot.lng);
            const trail = L.polyline([start, start], { color: '#00f2ff', weight: 1, className: 'data-trail' }).addTo(map);
            const packet = L.circleMarker(start, { radius: 3, fillColor: '#fff', color: '#fff', fillOpacity: 1, className: 'data-packet' }).addTo(map);

            const startTime = performance.now();
            const duration = 1200;

            const animate = (time) => {
                const p = Math.min((time - startTime) / duration, 1);
                const currentPos = [start.lat + (end.lat - start.lat) * p, start.lng + (end.lng - start.lng) * p];
                packet.setLatLng(currentPos);
                trail.setLatLngs([start, currentPos]);

                if (p < 1) {
                    requestAnimationFrame(animate);
                } else {
                    map.removeLayer(trail);
                    map.removeLayer(packet);
                    slotMarkers[slot.slot_id].m.setStyle({ fillColor: '#FF0000', color: '#FF0000' });
                    slot.status = 'occupied';
                    pushLog(`${slot.slot_name} : ALLOCATED`);
                    updateHUD();
                }
            };
            requestAnimationFrame(animate);
        }

        // === EXIT HANDLER ===
        function handleExit(slot) {
            const flash = document.getElementById('pay-flash');
            const exitAR = document.getElementById('exit-ar');
            const m = slotMarkers[slot.slot_id].m;

            m.setStyle({ fillColor: '#fff', color: '#fff' });

            setTimeout(() => {
                let color = slot.zone_type === 'suv' ? '#FFD700' : slot.zone_type === 'logistics' ? '#D000FF' : slot.zone_type === 'bike' ? '#00FFFF' : '#00FF00';
                m.setStyle({ fillColor: color, color: color });
                slot.status = 'free';
                totalRevenue += 150;

                exitAR.innerText = "> PAYMENT: RECEIVED";
                flash.style.display = 'block';
                setTimeout(() => flash.style.display = 'none', 1500);

                pushLog(`${slot.slot_name} : VACATED (+₹150)`);
                updateHUD();
            }, 1000);
        }

        init();
    </script>
</body>

</html>
<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | COMMAND CENTER v9.1</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto+Mono&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --accent: #00f2ff;
            --bg: #050709;
            --panel: rgba(13, 17, 23, 0.85);
            --border: rgba(0, 242, 255, 0.3);
        }

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Roboto Mono', monospace;
            background: var(--bg);
            color: #fff;
            overflow: hidden;
        }

        #map {
            position: absolute;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
        }

        .hud {
            position: absolute;
            z-index: 1000;
            pointer-events: none;
        }

        .glass {
            pointer-events: auto;
            background: var(--panel);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            border-radius: 8px;
        }

        /* Top HUD */
        .header-hud {
            top: 0;
            left: 0;
            width: 100%;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            border-radius: 0;
            border-bottom: 2px solid var(--border);
            box-sizing: border-box;
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
            color: var(--accent);
        }

        .stats-row {
            display: flex;
            gap: 40px;
        }

        .stat-val {
            font-family: 'Orbitron';
            color: #fff;
            font-size: 0.9rem;
        }

        .stat-label {
            font-size: 0.6rem;
            color: #888;
            text-transform: uppercase;
        }

        /* Cinematic Surveillance HUD (v9.2) */
        #cctv-panel {
            bottom: 25px;
            left: 25px;
            width: 320px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 2000;
        }

        .cam-box {
            position: relative;
            height: 160px;
            background: #000;
            border: 2px solid var(--border);
            border-radius: 6px;
            overflow: hidden;
        }

        .cam-box video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: grayscale(0.2) contrast(1.1);
        }

        .cam-rotate {
            transform: rotate(180deg);
        }

        /* UPSIDE DOWN FOR TOP-DOWN VIEW */

        .cam-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            font-family: 'Orbitron';
            font-size: 0.55rem;
            color: #fff;
            background: rgba(0, 0, 0, 0.6);
            padding: 2px 8px;
            border-radius: 4px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .rec-dot {
            width: 6px;
            height: 6px;
            background: #ff0000;
            border-radius: 50%;
            animation: blink 1s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.2;
            }
        }

        /* Terminal Style Overlays */
        .terminal-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 8px;
            border-top: 1px solid var(--border);
            z-index: 5;
        }

        .terminal-text {
            font-family: 'Courier New', Courier, monospace;
            color: #00ff00;
            font-size: 0.7rem;
            font-weight: bold;
            text-shadow: 0 0 5px rgba(0, 255, 0, 0.5);
        }

        /* Scan Line (Entry Only) */
        .scan-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--accent);
            box-shadow: 0 0 15px var(--accent);
            z-index: 8;
            animation: scan 4s linear infinite;
        }

        @keyframes scan {
            0% {
                top: 0;
            }

            100% {
                top: 100%;
            }
        }

        /* Sidebar (Right) */
        .sidebar {
            right: 25px;
            top: 85px;
            bottom: 25px;
            width: 280px;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .log-box {
            flex: 1;
            overflow-y: auto;
            font-size: 0.65rem;
            color: #ccc;
            margin-top: 15px;
        }

        .log-entry {
            margin-bottom: 8px;
            border-left: 2px solid var(--accent);
            padding-left: 8px;
        }

        /* Animation class for the laser */
        .laser-beam {
            stroke-dasharray: 8;
            animation: dash 0.8s linear infinite;
        }

        @keyframes dash {
            to {
                stroke-dashoffset: -16;
            }
        }
    </style>
</head>

<body>
    <div id="map"></div>

    <!-- HEADER -->
    <div class="hud glass header-hud">
        <div class="brand">OPTISPACE // COMMAND CENTER T2</div>
        <div class="stats-row">
            <div class="stat-item"><span class="stat-label">REVENUE</span><span class="stat-val" id="rev-val">₹
                    0.00</span></div>
            <div class="stat-item"><span class="stat-label">OCCUPANCY</span><span class="stat-val" id="occ-val">0 /
                    0</span></div>
            <div class="stat-item"><span class="stat-label">SYSTEM</span><span class="stat-val"
                    style="color:#00ff00">ONLINE</span></div>
        </div>
    </div>

    <!-- CCTV PANEL (Bottom Left) -->
    <div class="hud" id="cctv-panel">
        <!-- TOP: CAM-02 EXIT (ROTATED) -->
        <div class="cam-box">
            <div class="cam-tag">
                <div class="rec-dot"></div> CAM-02 [EXIT_LANE]
            </div>
            <div class="terminal-overlay">
                <div class="terminal-text" id="exit-text">> ANALYZING_EXIT...</div>
            </div>
            <video class="cam-rotate" src="cctv/VID2.mp4" autoplay muted loop id="vid-exit"></video>
        </div>
        <!-- BOTTOM: CAM-01 ENTRY -->
        <div class="cam-box">
            <div class="cam-tag">
                <div class="rec-dot"></div> CAM-01 [ENTRY_GATE]
            </div>
            <div class="terminal-overlay">
                <div class="terminal-text" id="entry-text">> SCANNING_TARGET...</div>
            </div>
            <video src="cctv/VID1.mp4" autoplay muted loop id="vid-entry"></video>
        </div>
    </div>

    <!-- SIDEBAR -->
    <div class="hud glass sidebar">
        <div
            style="font-family:'Orbitron'; font-size: 0.75rem; color: var(--accent); border-bottom: 1px solid var(--border); padding-bottom: 10px;">
            ZONE ANALYSIS</div>
        <div style="padding-top:15px; font-size: 0.7rem;">
            <div style="display:flex; justify-content:space-between; margin-bottom:5px;"><span>🚗 GENERAL</span><span
                    id="c-general">0 / 0</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#FFD700"><span>🚙
                    SUV</span><span id="c-suv">0 / 0</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#00FFFF"><span>🏍️
                    BIKE</span><span id="c-bike">0 / 0</span></div>
            <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#D000FF"><span>🚚
                    LOGISTICS</span><span id="c-logistics">0 / 0</span></div>
        </div>
        <div style="margin-top:20px; font-family:'Orbitron'; font-size: 0.65rem; color: var(--accent);">EVENT LOG</div>
        <div class="log-box" id="log"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map', { center: [8.488533, 76.921948], zoom: 19, zoomControl: false, attributionControl: false });
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(map);

        let slotMarkers = {};
        let currentSlots = [];
        const GATE = [8.488533, 76.921948];

        async function init() {
            const res = await fetch('logic.php?action=fetch_status');
            const data = await res.json();
            currentSlots = data.slots;

            data.slots.forEach(s => {
                let color = (s.status === 'free') ?
                    (s.zone_type === 'suv' ? '#FFD700' : s.zone_type === 'logistics' ? '#D000FF' : s.zone_type === 'bike' ? '#00FFFF' : '#00ff00') : '#FF0000';

                const m = L.circle([s.lat, s.lng], { radius: 2.2, fillColor: color, fillOpacity: 0.8, color: color, weight: 1.5 }).addTo(map);
                slotMarkers[s.slot_id] = { m: m, type: s.zone_type, status: s.status };
            });
            updateStats(data);
            startSimulatedLoops();
        }

        function updateStats(data) {
            document.getElementById('rev-val').innerText = '₹ ' + data.stats.revenue;
            document.getElementById('occ-val').innerText = data.stats.total_entries + ' / ' + data.slots.length;

            let zones = { general: { o: 0, t: 0 }, suv: { o: 0, t: 0 }, bike: { o: 0, t: 0 }, logistics: { o: 0, t: 0 } };
            data.slots.forEach(s => {
                zones[s.zone_type].t++;
                if (s.status !== 'free') zones[s.zone_type].o++;
            });
            for (let z in zones) document.getElementById('c-' + z).innerText = zones[z].o + ' / ' + zones[z].t;
        }

        function addLog(msg) {
            const log = document.getElementById('log');
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            entry.innerHTML = `<span style="color:var(--accent)">[${new Date().toLocaleTimeString()}]</span> ${msg}`;
            log.prepend(entry);
            if (log.children.length > 15) log.lastChild.remove();
        }

        // --- AUTOMATIC SIMULATION ENGINE (v9.2) ---
        function startSimulatedLoops() {
            const entryMsgs = ["> SCANNING...", "> FITMENT: OK", "> ALLOCATING..."];
            const exitMsgs = ["> VEHICLE LEAVING...", "> FEE: CALCULATING", "> PAYMENT: DONE"];

            // Force 0.5x Speed
            const v1 = document.getElementById('vid-entry');
            const v2 = document.getElementById('vid-exit');
            if (v1) v1.playbackRate = 0.5;
            if (v2) v2.playbackRate = 0.5;

            // ENTRY CYCLE (3s)
            setInterval(async () => {
                const term = document.getElementById('entry-text');
                if (!term) return;
                term.innerText = entryMsgs[0];
                await new Promise(r => setTimeout(r, 1000));
                term.innerText = entryMsgs[1];
                await new Promise(r => setTimeout(r, 1000));
                term.innerText = entryMsgs[2];

                const freeSlots = currentSlots.filter(s => s.status === 'free');
                if (freeSlots.length > 0) {
                    const target = freeSlots[Math.floor(Math.random() * freeSlots.length)];
                    fireLaser(target);
                }
            }, 3000);

            // EXIT CYCLE (5s)
            setInterval(async () => {
                const term = document.getElementById('exit-text');
                if (!term) return;
                const occSlots = currentSlots.filter(s => s.status !== 'free');
                if (occSlots.length > 0) {
                    term.innerText = exitMsgs[0];
                    await new Promise(r => setTimeout(r, 1500));
                    term.innerText = exitMsgs[1];
                    await new Promise(r => setTimeout(r, 1500));
                    term.innerText = exitMsgs[2];

                    const target = occSlots[Math.floor(Math.random() * occSlots.length)];
                    vacateSlot(target);
                } else {
                    term.innerText = "> STANDBY_MODE";
                }
            }, 5000);
        }

        function fireLaser(slot) {
            const start = L.latLng(GATE);
            const end = L.latLng(slot.lat, slot.lng);
            const line = L.polyline([start, end], { color: '#00f2ff', weight: 2, className: 'laser-beam' }).addTo(map);
            const dot = L.circleMarker(start, { radius: 4, fillColor: '#fff', color: '#00f2ff', fillOpacity: 1 }).addTo(map);

            let p = 0;
            const anim = () => {
                p += 0.05;
                if (p <= 1) {
                    dot.setLatLng([start.lat + (end.lat - start.lat) * p, start.lng + (end.lng - start.lng) * p]);
                    requestAnimationFrame(anim);
                } else {
                    map.removeLayer(line); map.removeLayer(dot);
                    slotMarkers[slot.slot_id].m.setStyle({ fillColor: '#FF0000', color: '#FF0000' });
                    slot.status = 'occupied';
                    addLog(`NODE ${slot.slot_id} : ALLOCATED`);
                    refreshStats();
                }
            };
            anim();
        }

        function vacateSlot(slot) {
            // Flash effect
            const m = slotMarkers[slot.slot_id].m;
            m.setStyle({ fillColor: '#fff', color: '#fff' });

            setTimeout(() => {
                let color = slot.zone_type === 'suv' ? '#FFD700' : slot.zone_type === 'logistics' ? '#D000FF' : slot.zone_type === 'bike' ? '#00FFFF' : '#00ff00';
                m.setStyle({ fillColor: color, color: color });
                slot.status = 'free';
                addLog(`NODE ${slot.slot_id} : VACATED (+₹150)`);
                refreshStats();
            }, 1000);
        }

        async function refreshStats() {
            const res = await fetch('logic.php?action=fetch_status');
            const data = await res.json();
            updateStats(data);
        }

        init();
    </script>
</body>

</html>
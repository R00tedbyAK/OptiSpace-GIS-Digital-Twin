<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | COMMAND CENTER v9.0</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto+Mono&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #00f2ff; --bg: #050709; --panel: rgba(13, 17, 23, 0.85); --border: rgba(0, 242, 255, 0.3); }
        body, html { margin:0; padding:0; height:100%; font-family: 'Roboto Mono', monospace; background: var(--bg); color: #fff; overflow: hidden; }
        #map { position: absolute; top:0; left:0; width:100vw; height:100vh; z-index: 1; }

        .hud { position: absolute; z-index: 1000; pointer-events: none; }
        .glass { pointer-events: auto; background: var(--panel); backdrop-filter: blur(12px); border: 1px solid var(--border); box-shadow: 0 0 20px rgba(0,0,0,0.5); border-radius: 8px; }

        /* Top HUD */
        .header-hud { top: 0; left: 0; width: 100%; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; border-radius: 0; border: none; border-bottom: 2px solid var(--border); box-sizing: border-box; }
        .brand { font-family: 'Orbitron', sans-serif; letter-spacing: 2px; color: var(--accent); }
        .stats-row { display: flex; gap: 40px; }
        .stat-item { text-align: right; }
        .stat-val { font-family: 'Orbitron'; color: #fff; display: block; }
        .stat-label { font-size: 0.6rem; color: #888; text-transform: uppercase; }

        /* Dual-Camera Stack (Bottom Left) */
        .cam-stack { bottom: 25px; left: 25px; width: 340px; display: flex; flex-direction: column; gap: 15px; }
        .cam-box { position: relative; height: 160px; border: 1px solid var(--border); overflow: hidden; border-radius: 4px; background: #000; }
        .cam-box video { width: 100%; height: 100%; object-fit: cover; filter: brightness(0.8) contrast(1.2); }
        .cam-rotate { transform: rotate(180deg); } /* UPSIDE DOWN CAM */
        .cam-label { position: absolute; top: 10px; left: 10px; font-size: 0.55rem; background: rgba(0,0,0,0.7); padding: 2px 6px; color: var(--accent); border: 1px solid var(--accent); font-family: 'Orbitron'; z-index: 10; }
        .ai-overlay { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0, 242, 255, 0.2); padding: 8px; color: var(--accent); text-align: center; font-weight: bold; font-size: 0.7rem; border-top: 1px solid var(--border); }

        /* QR Overlay on Exit Cam */
        .qr-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 80px; height: 80px; background: #fff; padding: 5px; border-radius: 4px; display: none; z-index: 20; }
        .payment-status { position: absolute; top: 15px; right: 15px; color: #00ff00; font-family:'Orbitron'; font-size: 0.6rem; text-shadow: 0 0 5px #00ff00; display: none; z-index: 21; }

        /* Sidebar (Right) */
        .sidebar { right: 25px; top: 85px; bottom: 25px; width: 280px; padding: 20px; display: flex; flex-direction: column; }
        .log-box { flex: 1; overflow-y: auto; font-size: 0.65rem; color: #ccc; margin-top: 15px; }
        .log-entry { margin-bottom: 8px; border-left: 2px solid var(--accent); padding-left: 8px; }
        .zone-pill { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.7rem; }

        /* Animations */
        .laser-beam { stroke-dasharray: 8; animation: dash 0.8s linear infinite; }
        @keyframes dash { to { stroke-dashoffset: -16; } }

        @keyframes flash {
            0% { background: #fff; }
            100% { background: transparent; }
        }
        .impact-flash { animation: flash 0.4s ease-out; }
    </style>
</head>
<body>
    <div id="map"></div>

    <div class="hud glass header-hud">
        <div class="brand">OPTISPACE // COMMAND CENTER T2</div>
        <div class="stats-row">
            <div class="stat-item"><span class="stat-label">REVENUE</span><span class="stat-val" id="rev-val">₹ 0.00</span></div>
            <div class="stat-item"><span class="stat-label">OCCUPANCY</span><span class="stat-val" id="occ-val">0 / 0</span></div>
            <div class="stat-item"><span class="stat-label">STATUS</span><span class="stat-val" style="color:#00ff00; text-shadow: 0 0 5px #00ff00">ONLINE</span></div>
        </div>
    </div>

    <div class="hud cam-stack">
        <!-- TOP: EXIT CAM (UPSIDE DOWN) -->
        <div class="cam-box">
            <span class="cam-label">CAM-02 [EXIT LANE]</span>
            <div class="qr-overlay" id="qr-box"><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=OPTISPACE_PAYMENT" style="width:100%"></div>
            <span class="payment-status" id="pay-status">● PAYMENT RECEIVED: ₹150</span>
            <video class="cam-rotate" id="exit-vid" src="cctv/VID1.mp4" autoplay muted loop></video>
        </div>
        <!-- BOTTOM: ENTRY CAM -->
        <div class="cam-box">
            <span class="cam-label">CAM-01 [ENTRY GATE]</span>
            <video id="entry-vid" src="cctv/VID1.mp4" autoplay muted loop></video>
            <div class="ai-overlay" id="entry-status">SYSTEM IDLE</div>
        </div>
    </div>

    <div class="hud glass sidebar">
        <div style="font-family:'Orbitron'; font-size: 0.75rem; color: var(--accent); border-bottom: 1px solid var(--border); padding-bottom: 10px;">ZONE ANALYSIS</div>
        <div style="padding-top:15px;">
            <div class="zone-pill"><span>🚗 GENERAL</span><span id="c-general">0 / 0</span></div>
            <div class="zone-pill" style="color:var(--suv)"><span>🚙 SUV</span><span id="c-suv">0 / 0</span></div>
            <div class="zone-pill" style="color:var(--bike)"><span>🏍️ BIKE</span><span id="c-bike">0 / 0</span></div>
            <div class="zone-pill" style="color:var(--logistics)"><span>🚚 LOGISTICS</span><span id="c-logistics">0 / 0</span></div>
        </div>
        <div style="margin-top:20px; font-family:'Orbitron'; font-size: 0.65rem; color: var(--accent);">LIVE EVENT LOG</div>
        <div class="log-box" id="log"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('map', { center: [8.488533, 76.921948], zoom: 19, zoomControl: false, attributionControl: false });
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(map);
        
        let slotMarkers = {};
        let currentSlots = [];
        const GATE = [8.488533, 76.921948];
        const vids = [document.getElementById('entry-vid'), document.getElementById('exit-vid')];
        vids.forEach(v => v.playbackRate = 0.75);

        async function init() {
            const res = await fetch('logic.php?action=fetch_status');
            const data = await res.json();
            currentSlots = data.slots;
            
            data.slots.forEach(s => {
                let color = '#00ff00';
                if (s.zone_type === 'suv') color = '#FFD700';
                if (s.zone_type === 'logistics') color = '#D000FF';
                if (s.zone_type === 'bike') color = '#00FFFF';
                if (s.status === 'occupied' || s.status === 'inefficient') color = '#FF0000';

                const m = L.circle([s.lat, s.lng], { radius: 2.2, fillColor: color, fillOpacity: 0.8, color: color, weight: 1.5 }).addTo(map);
                slotMarkers[s.slot_id] = { m: m, type: s.zone_type };
            });
            updateStats(data);
            startLoops();
        }

        function updateStats(data) {
            document.getElementById('rev-val').innerText = '₹ ' + data.stats.revenue;
            document.getElementById('occ-val').innerText = data.stats.total_entries + ' / ' + data.slots.length;
            
            let zones = { general: {o:0, t:0}, suv: {o:0, t:0}, bike: {o:0, t:0}, logistics: {o:0, t:0} };
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
            if (log.children.length > 20) log.lastChild.remove();
        }

        async function startLoops() {
            // ENTRY LOOP (3s)
            setInterval(async () => {
                const status = document.getElementById('entry-status');
                status.innerText = "SCANNING...";
                await new Promise(r => setTimeout(r, 1000));
                status.innerText = "FASTag DETECTED";
                await new Promise(r => setTimeout(r, 1000));
                status.innerText = "ALLOCATING SLOT...";
                
                const free = currentSlots.filter(s => s.status === 'free');
                if (free.length > 0) {
                    const target = free[Math.floor(Math.random() * free.length)];
                    fireLaser(target);
                }
            }, 3000);

            // EXIT LOOP (5s)
            setInterval(async () => {
                const occupied = currentSlots.filter(s => s.status !== 'free');
                if (occupied.length > 0) {
                    const target = occupied[Math.floor(Math.random() * occupied.length)];
                    handleExit(target);
                }
            }, 5000);
        }

        function fireLaser(slot) {
            const start = L.latLng(GATE);
            const end = L.latLng(slot.lat, slot.lng);
            const line = L.polyline([start, end], { color: '#00f2ff', weight: 2, opacity: 0.6, className: 'laser-beam' }).addTo(map);
            const dot = L.circleMarker(start, { radius: 4, fillColor: '#fff', fillOpacity: 1, color: '#00f2ff', weight: 1 }).addTo(map);

            let progress = 0;
            const anim = () => {
                progress += 0.05;
                if (progress <= 1) {
                    dot.setLatLng([start.lat + (end.lat - start.lat) * progress, start.lng + (end.lng - start.lng) * progress]);
                    requestAnimationFrame(anim);
                } else {
                    map.removeLayer(line); map.removeLayer(dot);
                    slotMarkers[slot.slot_id].m.setStyle({ fillColor: '#FF0000', color: '#FF0000' });
                    slot.status = 'occupied';
                    addLog(`NODE ${slot.slot_id} : ALLOCATED`);
                    refreshData();
                }
            };
            anim();
        }

        async function handleExit(slot) {
            const qr = document.getElementById('qr-box');
            const ps = document.getElementById('pay-status');
            qr.style.display = 'block'; ps.style.display = 'block';
            
            // Flash map slot
            const m = slotMarkers[slot.slot_id].m;
            const originalColor = slotMarkers[slot.slot_id].type === 'suv' ? '#FFD700' : 
                                 slotMarkers[slot.slot_id].type === 'logistics' ? '#D000FF' :
                                 slotMarkers[slot.slot_id].type === 'bike' ? '#00FFFF' : '#00ff00';
            
            m.setStyle({ fillColor: '#fff', color: '#fff' });
            setTimeout(() => {
                m.setStyle({ fillColor: originalColor, color: originalColor });
                slot.status = 'free';
                qr.style.display = 'none'; ps.style.display = 'none';
                addLog(`NODE ${slot.slot_id} : VACATED (+₹150)`);
                refreshData();
            }, 2000);
        }

        async function refreshData() {
            const res = await fetch('logic.php?action=fetch_status');
            const data = await res.json();
            updateStats(data);
        }

        init();
    </script>
</body>
</html>
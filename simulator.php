<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | Airport Command Node</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Inter:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #050709;
            --accent: #00f2ff;
            --panel: #0d1117;
        }

        body {
            background: var(--bg);
            color: #fff;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .command-panel {
            background: var(--panel);
            border: 2px solid rgba(0, 242, 255, 0.2);
            padding: 40px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 0 50px rgba(0, 242, 255, 0.1);
        }

        h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            color: var(--accent);
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        button {
            background: transparent;
            border: 1px solid var(--accent);
            color: var(--accent);
            padding: 15px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        button:hover {
            background: var(--accent);
            color: #000;
            box-shadow: 0 0 20px var(--accent);
        }

        .reset-btn {
            border-color: #ff3e3e;
            color: #ff3e3e;
            margin-top: 20px;
            font-size: 0.6rem;
        }

        .reset-btn:hover {
            background: #ff3e3e;
            color: #fff;
            box-shadow: 0 0 20px #ff3e3e;
        }

        #terminal {
            margin-top: 30px;
            background: #000;
            padding: 15px;
            font-family: monospace;
            font-size: 0.75rem;
            text-align: left;
            height: 100px;
            overflow-y: auto;
            border: 1px solid #333;
            color: #00ff88;
        }
    </style>
</head>

<body>
    <div class="command-panel">
        <h1>OPTISPACE // AIRPORT COMMAND NODE</h1>
        <div class="btn-group">
            <button onclick="sendVehicle('suv')">VIP SUV Arrival (Zone A)</button>
            <button onclick="sendVehicle('car')">General Car Entry (Zone B)</button>
            <button onclick="sendVehicle('truck')">Logistics Truck Entry (Zone L)</button>
            <button onclick="sendVehicle('bike')" style="border-style: dashed;">Unregistered Bike (Zone Audit)</button>
        </div>
        <button class="reset-btn" onclick="resetSim()">RESET GROUND LOGISTICS</button>
        <div id="terminal">> SYSTEM STANDBY...</div>
    </div>

    <script>
        const terminal = document.getElementById('terminal');

        function log(msg, color = '#00ff88') {
            const span = document.createElement('div');
            span.style.color = color;
            span.innerText = `> ${new Date().toLocaleTimeString()}: ${msg}`;
            terminal.prepend(span);
        }

        async function sendVehicle(type) {
            log(`INITIATING ${type.toUpperCase()} ENTRY PROTOCOL...`, '#fff');
            const formData = new FormData();
            formData.append('vehicle_type', type);

            try {
                const r = await fetch('logic.php?action=enter', { method: 'POST', body: formData });
                const d = await r.json();
                if (d.success) {
                    log(d.message, d.status === 'inefficient' ? '#ffbe00' : '#00ff88');
                } else {
                    log(d.message, '#ff3e3e');
                }
            } catch (e) {
                log("COMMUNICATION ERROR", "#ff3e3e");
            }
        }

        async function resetSim() {
            if (!confirm("Confirm hard reset of TRV Terminal 2 ground data?")) return;
            await fetch('logic.php?action=reset');
            log("LOGISTICS RESET COMPLETE", "#ff3e3e");
        }
    </script>
</body>

</html>
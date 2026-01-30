<?php require 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiSpace | EMERGENCY CONTROLS</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&family=Roboto+Mono&display=swap" rel="stylesheet">
    <style>
        body {
            background: #050709;
            color: #fff;
            font-family: 'Roboto Mono', monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .panel {
            background: rgba(13, 17, 23, 0.9);
            border: 1px solid rgba(0, 242, 255, 0.3);
            padding: 40px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 0 30px rgba(0, 242, 255, 0.1);
        }

        h2 {
            font-family: 'Orbitron';
            color: #00f2ff;
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(0, 242, 255, 0.2);
            padding-bottom: 10px;
        }

        .btn {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: none;
            border-radius: 4px;
            font-family: 'Orbitron';
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-entry {
            background: #00f2ff;
            color: #000;
        }

        .btn-entry:hover {
            background: #fff;
            box-shadow: 0 0 15px #00f2ff;
        }

        .override-section {
            margin-top: 40px;
            border: 1px dashed #ff3b3b;
            padding: 20px;
            border-radius: 8px;
            background: rgba(255, 59, 59, 0.05);
        }

        .override-title {
            color: #ff3b3b;
            font-size: 0.7rem;
            font-weight: bold;
            margin-bottom: 15px;
            display: block;
        }

        input {
            width: 100%;
            padding: 12px;
            background: #000;
            border: 1px solid #ff3b3b;
            color: #fff;
            margin-bottom: 15px;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: 'Roboto Mono';
        }

        .btn-emergency {
            background: #ff3b3b;
            color: #fff;
        }

        .btn-emergency:hover {
            background: #fff;
            color: #ff3b3b;
            box-shadow: 0 0 15px #ff3b3b;
        }

        #status {
            text-align: center;
            margin-top: 20px;
            font-size: 0.7rem;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="panel">
        <h2>CMD // SIMULATOR</h2>
        <button class="btn btn-entry" onclick="simEntry('car')">Standard Car Arrival</button>
        <button class="btn btn-entry" onclick="simEntry('suv')">SUV Fitment Arrival</button>
        <button class="btn btn-entry" onclick="simEntry('truck')">Logistics Arrival</button>
        <button class="btn btn-entry" onclick="simEntry('bike')">Bike Arrival</button>

        <div class="override-section">
            <span class="override-title">⚠ MANUAL SYSTEM OVERRIDE</span>
            <input type="text" id="slot_id" placeholder="ENTER SLOT ID (e.g., A-05)">
            <button class="btn btn-emergency" onclick="emergencyVacate()">EMERGENCY VACATE</button>
        </div>

        <div id="status">READY FOR SECTOR COMMS</div>
    </div>

    <script>
        async function simEntry(type) {
            const formData = new FormData();
            formData.append('vehicle_type', type);
            const res = await fetch('logic.php?action=entry', { method: 'POST', body: formData });
            const data = await res.json();
            document.getElementById('status').innerText = data.message.toUpperCase();
            setTimeout(() => document.getElementById('status').innerText = 'READY', 2000);
        }

        async function emergencyVacate() {
            const slotId = document.getElementById('slot_id').value;
            if (!slotId) return alert("SLOT ID REQUIRED");

            const formData = new FormData();
            formData.append('slot_name', slotId);
            const res = await fetch('logic.php?action=emergency_vacate', { method: 'POST', body: formData });
            const data = await res.json();
            document.getElementById('status').innerText = data.message.toUpperCase();
            document.getElementById('slot_id').value = '';
            setTimeout(() => document.getElementById('status').innerText = 'READY', 2000);
        }
    </script>
</body>

</html>
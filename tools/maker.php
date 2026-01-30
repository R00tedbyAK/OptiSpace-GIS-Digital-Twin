<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OptiSpace | Coordinate Maker</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: sans-serif;
        }

        #map {
            height: 100%;
            width: 100%;
            cursor: crosshair;
        }

        #context-menu {
            display: none;
            position: absolute;
            z-index: 1000;
            background: white;
            padding: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        select,
        input,
        button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            padding: 5px;
        }

        h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>

<body>

    <div id="map"></div>

    <div id="context-menu">
        <h4>New Parking Slot</h4>
        <input type="text" id="slot-name" placeholder="Slot Name">
        <select id="vehicle-type">
            <option value="car">Car</option>
            <option value="suv">SUV</option>
            <option value="truck">Truck</option>
            <option value="bike">Bike</option>
        </select>
        <button id="save-btn">SAVE COORDINATES</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const imagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 19 });
        const map = L.map('map', {
            center: [8.488000, 76.923000],
            zoom: 19,
            layers: [imagery]
        });

        const menu = document.getElementById('context-menu');
        let activeLatLng = null;

        map.on('contextmenu', function (e) {
            activeLatLng = e.latlng;
            menu.style.display = 'block';
            menu.style.left = e.containerPoint.x + 'px';
            menu.style.top = e.containerPoint.y + 'px';
            document.getElementById('slot-name').value = 'Slot-' + Math.floor(Math.random() * 10000);
        });

        map.on('click', () => { menu.style.display = 'none'; });

        document.getElementById('save-btn').onclick = async function () {
            const name = document.getElementById('slot-name').value;
            const type = document.getElementById('vehicle-type').value;

            const formData = new FormData();
            formData.append('lat', activeLatLng.lat);
            formData.append('lng', activeLatLng.lng);
            formData.append('name', name);
            formData.append('type', type);

            try {
                const resp = await fetch('save_slot.php', { method: 'POST', body: formData });
                if (resp.ok) {
                    L.circle(activeLatLng, { radius: 0.5, color: 'cyan', fillOpacity: 0.8 }).addTo(map);
                    menu.style.display = 'none';
                    console.log("Saved: " + name);
                }
            } catch (err) { alert("Save failed!"); }
        };
    </script>
</body>

</html>
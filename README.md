# OptiSpace | Enterprise-Grade GIS Digital Twin

OptiSpace is a professional-tier Smart Parking Solution and Digital Twin dashboard designed for advanced logistics and urban planning. This project features high-fidelity GIS integration, real-time vehicle tracking, and automated zoning logic.

![OptiSpace UI](https://img.shields.io/badge/Status-Enterprise--Ready-00f2ff?style=for-the-badge&logo=esri)

## 🚀 Key Features

- **Esri GIS Integration**: Military-grade satellite imagery and reference layers centered on Kochi, India.
- **Strict Zoning Logic**: Automated enforcement of parking rules for Heavy Vehicles (Buses/Trucks) in dedicated Logistics Hub zones.
- **Smart Inefficiency Detection**: Real-time flagging of "inefficiently" parked vehicles (e.g., bikes in XL slots).
- **Enterprise Dashboard**: Split-screen interface with a Live Camera Feed and GIS tracking.
- **LiDAR Ready**: Built-in hooks for NeST Cloud LiDAR 3D services.

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3 (Cyber-tech aesthetic), JavaScript, Leaflet.js
- **Backend**: PHP 8.x
- **Database**: MySQL/MariaDB
- **Maps**: Esri ArcGIS Online Services

## 📥 Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/JithuMon10/OptiSpace-GIS-Digital-Twin.git
   ```
2. **Setup Database**:
   - Import `database.sql` into your local MySQL server (XAMPP/WAMP recommended).
3. **Configure Connection**:
   - Update `db_connect.php` with your database credentials.
4. **Deploy**:
   - Place the project in your `htdocs` or equivalent web root directory.
   - Access via `localhost/optispace`.

## 📸 Dashboard Preview

The dashboard features a split-screen layout:
- **Left Panel**: 24/7 Live Security Camera loop.
- **Right Panel**: Interactive Esri Map with real-time slot statuses.
- **Bottom Ticker**: Live system status updates and ArcGIS sync status.


## 👷 Team Collaboration Guide (Data Collection)

Internal tools are available for high-speed mapping of parking coordinates. Use **Smart Map Maker v5.0** for the best experience.

### 🌐 Accessing the Tool
Open `http://localhost/optispace/tools/maker.php` in your browser.

### 📍 Mapping Workflow (v5.0)
1.  **Select Zone**: Use the **A-N Grid** in the sidebar to select your target zone. The tool will automatically calculate the next available number (e.g., if A-2 exists, it suggests A-3).
2.  **Left-Click**: Click anywhere on the map to place a slot.
3.  **Confirm Type**: A popup appears at your click location. Select the **Vehicle Type** (Car, SUV, Truck, Bike) and click **SAVE**.
4.  **Sticky Memory**: The tool remembers your vehicle selection for the next click to speed up repetitive mapping.

### 🎨 Visual Legend
- 🟢 **Green**: Car (General)
- 🟣 **Purple**: SUV (Premium)
- 🔴 **Red**: Truck (Logistics)
- 🟡 **Yellow**: Bike
- *Numbers are displayed inside each dot for easy identification.*

### 💾 Syncing Data
1.  When finished, click **DOWNLOAD slots.sql** in the sidebar.
2.  Import the generated SQL into your local `optispace_db` via phpMyAdmin or the MySQL command line.
3.  Refresh the main OptiSpace Dashboard to see your new slots live!

---
*Enterprise Solutions by NeST Digital.*

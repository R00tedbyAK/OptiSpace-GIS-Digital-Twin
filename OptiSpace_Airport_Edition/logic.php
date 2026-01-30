<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_status') {
    $slots = $pdo->query("SELECT * FROM parking_slots")->fetchAll();
    $stats = $pdo->query("SELECT * FROM soc_stats WHERE id = 1")->fetch();
    echo json_encode(['slots' => $slots, 'stats' => $stats]);
    exit;
}

if ($action === 'enter') {
    $vehicle_type = strtolower($_POST['vehicle_type'] ?? '');

    // Strict Geofencing Rules
    $target_zone = '';
    $status_override = 'occupied';

    if ($vehicle_type === 'bus' || $vehicle_type === 'truck') {
        // MUST go to Zone L
        $target_zone = 'logistics';
    } elseif ($vehicle_type === 'suv') {
        // Preferred Zone A
        $target_zone = 'premium';
    } elseif ($vehicle_type === 'car') {
        // Preferred Zone B
        $target_zone = 'general';
    } elseif ($vehicle_type === 'bike') {
        // Bikes can take General, but if they take Premium -> Inefficient
        $target_zone = 'any';
    }

    // Logic for Bus/Truck (Restriction)
    if ($vehicle_type === 'bus' || $vehicle_type === 'truck') {
        $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'logistics' LIMIT 1");
        $stmt->execute();
        $slot = $stmt->fetch();

        if (!$slot) {
            echo json_encode(['success' => false, 'message' => 'ACCESS DENIED: Logistics Bay L Full. Heavy vehicles restricted from other zones.']);
            exit;
        }
    } else {
        // Standard search for other vehicles
        if ($target_zone === 'premium') {
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'premium' LIMIT 1");
        } elseif ($target_zone === 'general') {
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'general' LIMIT 1");
        } else {
            // Bike or fallback: Find first available
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' ORDER BY zone_type ASC LIMIT 1");
        }
        $stmt->execute();
        $slot = $stmt->fetch();

        if (!$slot) {
            echo json_encode(['success' => false, 'message' => 'Parking Capacity Reached']);
            exit;
        }

        // Bike in Premium Flag
        if ($vehicle_type === 'bike' && $slot['zone_type'] === 'premium') {
            $status_override = 'inefficient';
            $pdo->query("UPDATE soc_stats SET alerts_triggered = alerts_triggered + 1 WHERE id = 1");
        }
    }

    // Update Slot
    $stmt = $pdo->prepare("UPDATE parking_slots SET status = ?, current_vehicle = ? WHERE id = ?");
    $stmt->execute([$status_override, $vehicle_type, $slot['id']]);

    // Update Stats
    $pdo->query("UPDATE soc_stats SET total_entries = total_entries + 1 WHERE id = 1");

    echo json_encode([
        'success' => true,
        'message' => "Vehicle {$vehicle_type} cleared for {$slot['slot_id']} (" . strtoupper($slot['zone_type']) . ")",
        'status' => $status_override
    ]);
    exit;
}

if ($action === 'reset') {
    $pdo->query("UPDATE parking_slots SET status = 'free', current_vehicle = NULL");
    $pdo->query("UPDATE soc_stats SET total_entries = 0, alerts_triggered = 0 WHERE id = 1");
    echo json_encode(['success' => true]);
    exit;
}
?>
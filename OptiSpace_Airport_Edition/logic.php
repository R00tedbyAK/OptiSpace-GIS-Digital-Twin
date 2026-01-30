<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_status' || $action === 'fetch_status') {
        $slots = $pdo->query("SELECT * FROM parking_slots")->fetchAll();
        $stats = $pdo->query("SELECT * FROM soc_stats WHERE id = 1")->fetch();

        // Ensure accurate types and structure
        echo json_encode([
            'slots' => $slots,
            'stats' => [
                'total_entries' => (int) $stats['total_entries'],
                'alerts_triggered' => (int) $stats['alerts_triggered'],
                'revenue' => (float) $stats['revenue'],
                'co2_saved' => (float) $stats['co2_saved']
            ]
        ]);
        exit;
    }

    if ($action === 'enter') {
        $vehicle_type = strtolower($_POST['vehicle_type'] ?? '');

        // Rules and Zoning
        $target_zone = '';
        $status_override = 'occupied';
        $fee = 0;
        $co2 = 0;

        // Pricing/CO2 Reference
        if ($vehicle_type === 'suv') {
            $target_zone = 'premium';
            $fee = 100;
            $co2 = 2.5;
        } elseif ($vehicle_type === 'car') {
            $target_zone = 'general';
            $fee = 50;
            $co2 = 1.2;
        } elseif ($vehicle_type === 'bus' || $vehicle_type === 'truck') {
            $target_zone = 'logistics';
            $fee = 200;
            $co2 = 5.0;
        } elseif ($vehicle_type === 'bike') {
            $target_zone = 'any';
            $fee = 20;
            $co2 = 0.5;
        }

        if ($vehicle_type === 'bus' || $vehicle_type === 'truck') {
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'logistics' LIMIT 1");
            $stmt->execute();
            $slot = $stmt->fetch();
            if (!$slot) {
                echo json_encode(['success' => false, 'message' => 'ACCESS DENIED: Logistics Bay L Full.']);
                exit;
            }
        } else {
            if ($target_zone === 'premium')
                $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'premium' LIMIT 1");
            elseif ($target_zone === 'general')
                $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'general' LIMIT 1");
            else
                $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' ORDER BY zone_type ASC LIMIT 1");

            $stmt->execute();
            $slot = $stmt->fetch();
            if (!$slot) {
                echo json_encode(['success' => false, 'message' => 'Parking Capacity Reached']);
                exit;
            }

            if ($vehicle_type === 'bike' && $slot['zone_type'] === 'premium') {
                $status_override = 'inefficient';
                $pdo->query("UPDATE soc_stats SET alerts_triggered = alerts_triggered + 1 WHERE id = 1");
            }
        }

        // Apply Entry
        $stmt = $pdo->prepare("UPDATE parking_slots SET status = ?, current_vehicle = ? WHERE id = ?");
        $stmt->execute([$status_override, $vehicle_type, $slot['id']]);
        $pdo->prepare("UPDATE soc_stats SET total_entries = total_entries + 1, revenue = revenue + ?, co2_saved = co2_saved + ? WHERE id = 1")
            ->execute([$fee, $co2]);

        echo json_encode([
            'success' => true,
            'message' => "Vehicle {$vehicle_type} cleared for {$slot['slot_id']} (" . strtoupper($slot['zone_type']) . ")",
            'status' => $status_override
        ]);
        exit;
    }

    if ($action === 'reset') {
        $pdo->query("UPDATE parking_slots SET status = 'free', current_vehicle = NULL");
        $pdo->query("UPDATE soc_stats SET total_entries = 0, alerts_triggered = 0, revenue = 0, co2_saved = 0 WHERE id = 1");
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid Action']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
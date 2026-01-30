<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? '';

    if ($action === 'fetch_status' || $action === 'get_status') {
        $slots = $pdo->query("SELECT * FROM parking_slots")->fetchAll();
        $stats = $pdo->query("SELECT * FROM soc_stats WHERE id = 1")->fetch();

        echo json_encode([
            'slots' => $slots,
            'stats' => [
                'total_entries' => (int) $stats['total_entries'],
                'revenue' => (float) $stats['revenue'],
                'co2_saved' => (float) $stats['co2_saved']
            ]
        ]);
        exit;
    }

    if ($action === 'entry') {
        $vehicle_type = strtolower($_POST['vehicle_type'] ?? '');
        $fee = 0;
        $co2 = 0;
        $status = 'occupied';

        // Zoning and Pricing
        if ($vehicle_type === 'truck' || $vehicle_type === 'bus') {
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'logistics' LIMIT 1");
            $fee = 250;
            $co2 = 6.0;
        } elseif ($vehicle_type === 'suv') {
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'premium' LIMIT 1");
            $fee = 100;
            $co2 = 3.0;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM parking_slots WHERE status = 'free' AND zone_type = 'general' LIMIT 1");
            $fee = 50;
            $co2 = 1.5;
        }

        $stmt->execute();
        $slot = $stmt->fetch();

        if (!$slot) {
            echo json_encode(['success' => false, 'message' => 'Requested zone full.']);
            exit;
        }

        // Apply
        $pdo->prepare("UPDATE parking_slots SET status = ?, current_vehicle = ? WHERE id = ?")
            ->execute([$status, $vehicle_type, $slot['id']]);

        $pdo->prepare("UPDATE soc_stats SET total_entries = total_entries + 1, revenue = revenue + ?, co2_saved = co2_saved + ? WHERE id = 1")
            ->execute([$fee, $co2]);

        echo json_encode(['success' => true, 'message' => "Parked in {$slot['slot_id']}"]);
        exit;
    }

    if ($action === 'exit') {
        $slot_id = $_POST['slot_id'] ?? null;
        if ($slot_id) {
            $pdo->prepare("UPDATE parking_slots SET status = 'free', current_vehicle = NULL WHERE slot_id = ?")
                ->execute([$slot_id]);
            echo json_encode(['success' => true, 'message' => "Slot {$slot_id} freed."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Slot ID required."]);
        }
        exit;
    }

    if ($action === 'reset') {
        $pdo->query("UPDATE parking_slots SET status = 'free', current_vehicle = NULL");
        $pdo->query("UPDATE soc_stats SET total_entries = 0, revenue = 0, co2_saved = 0 WHERE id = 1");
        echo json_encode(['success' => true]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
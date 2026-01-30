<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'] ?? 0;
    $lng = $_POST['lng'] ?? 0;
    $type = $_POST['type'] ?? 'car';
    $name = $_POST['name'] ?? 'Unnamed';

    // Zoning Mapping
    $zone = 'general';
    if ($type === 'truck')
        $zone = 'logistics';
    elseif ($type === 'suv')
        $zone = 'premium';

    // SQL String Generation
    $sql = "INSERT INTO parking_slots (slot_id, slot_name, status, lat, lng, zone_type) VALUES (NULL, '$name', 'free', $lat, $lng, '$zone');" . PHP_EOL;

    // Append to file
    file_put_contents('slots.sql', $sql, FILE_APPEND);

    echo json_encode(['success' => true]);
}
?>
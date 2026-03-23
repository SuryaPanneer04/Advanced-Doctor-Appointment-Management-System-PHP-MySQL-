<?php
require_once '../includes/db.php';
header('Content-Type: application/json');

if (isset($_GET['doctor_id']) && isset($_GET['date'])) {
    $doctor_id = (int)$_GET['doctor_id'];
    $date = $_GET['date'];

    // Define working hours
    $start_time = "09:00:00";
    $end_time = "17:00:00";
    $interval = 30; // minutes

    // Fetch existing appointments for this doctor on this date
    $stmt = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status != 'Cancelled'");
    $stmt->execute([$doctor_id, $date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $slots = [];
    $current = strtotime($date . ' ' . $start_time);
    $end = strtotime($date . ' ' . $end_time);

    while ($current < $end) {
        $time_str = date('H:i:s', $current);
        $display_time = date('h:i A', $current);
        
        $is_booked = in_array($time_str, $booked_slots);
        $is_past = (strtotime($date . ' ' . $time_str) < time());

        $slots[] = [
            'time' => $time_str,
            'display' => $display_time,
            'available' => (!$is_booked && !$is_past)
        ];
        
        $current = strtotime("+$interval minutes", $current);
    }

    echo json_encode(['success' => true, 'slots' => $slots]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
?>

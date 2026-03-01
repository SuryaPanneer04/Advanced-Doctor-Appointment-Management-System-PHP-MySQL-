<?php
// admin/toggle-availability.php
require_once '../includes/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctor_id']) && isset($_POST['is_available'])) {
    $doctor_id = (int)$_POST['doctor_id'];
    $is_available = (int)$_POST['is_available'];
    
    // Ensure we only ever set 0 or 1
    $is_available = ($is_available === 1) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE doctors SET is_available = ? WHERE id = ?");
    if ($stmt->execute([$is_available, $doctor_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>

<?php
// user/get-doctor-reviews.php
require_once '../includes/db.php';

if (isset($_GET['doctor_id'])) {
    $doctor_id = (int)$_GET['doctor_id'];
    
    $stmt = $pdo->prepare("
        SELECT r.rating, r.comment, r.created_at, u.name as patient_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.doctor_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$doctor_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format date
    foreach ($reviews as &$rev) {
        $rev['date_formatted'] = date('M d, Y', strtotime($rev['created_at']));
    }
    
    header('Content-Type: application/json');
    echo json_encode($reviews);
    exit;
}
?>

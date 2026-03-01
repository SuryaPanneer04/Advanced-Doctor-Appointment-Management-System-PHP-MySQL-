<?php
// user/get-doctors.php
require_once '../includes/db.php';

if (isset($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    $stmt = $pdo->prepare("
        SELECT d.id, d.name, d.photo, d.details, d.is_available,
               COALESCE(AVG(r.rating), 0) as avg_rating, 
               COUNT(r.id) as review_count 
        FROM doctors d 
        LEFT JOIN reviews r ON d.id = r.doctor_id 
        WHERE d.category_id = ? 
        GROUP BY d.id, d.name, d.photo, d.details, d.is_available
        ORDER BY avg_rating DESC, d.name ASC
    ");
    $stmt->execute([$category_id]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format avg_rating to 1 decimal place
    foreach ($doctors as &$doc) {
        $doc['avg_rating'] = number_format((float)$doc['avg_rating'], 1, '.', '');
    }
    
    header('Content-Type: application/json');
    echo json_encode($doctors);
    exit;
}
?>

<?php
// user/get-doctors.php
require_once '../includes/db.php';

if (isset($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    $stmt = $pdo->prepare("
        SELECT d.*, c.name as category_name, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(r.id) as review_count
        FROM doctors d 
        LEFT JOIN categories c ON d.category_id = c.id 
        LEFT JOIN reviews r ON d.id = r.doctor_id
        WHERE d.category_id = ? 
        GROUP BY d.id
        ORDER BY d.rating DESC
    ");
    $stmt->execute([$category_id]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format ratings to 1 decimal place
    foreach ($doctors as &$doc) {
        $doc['avg_rating'] = number_format((float)$doc['avg_rating'], 1, '.', '');
        $doc['rating'] = number_format((float)$doc['rating'], 1, '.', '');
    }
    
    header('Content-Type: application/json');
    echo json_encode($doctors);
    exit;
}
?>

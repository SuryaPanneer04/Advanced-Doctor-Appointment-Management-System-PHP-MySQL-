<?php
// user/api-search-doctors.php
require_once '../includes/db.php';
session_start();
header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$availability = isset($_GET['availability']) ? $_GET['availability'] : 'all'; // 'all', 'available', 'unavailable'
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'rating'; // 'rating', 'reviews', 'recent'
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$rating_min = isset($_GET['rating_min']) ? (float)$_GET['rating_min'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$params = [];
$whereClauses = [];

if ($category_id > 0) {
    $whereClauses[] = "d.category_id = ?";
    $params[] = $category_id;
}

if ($availability === 'available') {
    $whereClauses[] = "d.is_available = 1";
} elseif ($availability === 'unavailable') {
    $whereClauses[] = "d.is_available = 0";
}

if ($location !== '') {
    $whereClauses[] = "d.location LIKE ?";
    $params[] = "%$location%";
}

if ($rating_min > 0) {
    $whereClauses[] = "d.rating >= ?";
    $params[] = $rating_min;
}

if ($search !== '') {
    $whereClauses[] = "(d.name LIKE ? OR d.details LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = "";
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

$orderBySQL = "ORDER BY d.rating DESC, d.name ASC";
if ($sort === 'reviews') {
    $orderBySQL = "ORDER BY review_count DESC, d.rating DESC";
} elseif ($sort === 'recent') {
    $orderBySQL = "ORDER BY d.id DESC"; // Assuming highest ID is most recently added
}

// Fetch doctors
$sql = "
    SELECT d.id, d.name, d.photo, d.details, d.is_available, d.category_id, d.rating, d.location, d.fees, c.name as category_name,
           COALESCE(AVG(r.rating), 0) as avg_rating, 
           COUNT(r.id) as review_count 
    FROM doctors d 
    LEFT JOIN categories c ON d.category_id = c.id
    LEFT JOIN reviews r ON d.id = r.doctor_id 
    $whereSQL
    GROUP BY d.id, d.name, d.photo, d.details, d.is_available, d.category_id, d.rating, d.location, d.fees, c.name
    $orderBySQL
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'doctors' => $doctors]);
?>

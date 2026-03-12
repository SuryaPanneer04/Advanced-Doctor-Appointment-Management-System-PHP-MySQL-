<?php
require_once 'includes/db.php';

$categories = [
    "Cardiology", "Neurology", "Pediatrics", "Orthopedics", "Dermatology",
    "Oncology", "Psychiatry", "Gastroenterology", "Endocrinology", "Ophthalmology",
    "Urology", "Gynecology", "Pulmonology", "Rheumatology", "Nephrology",
    "Otolaryngology (ENT)", "Anesthesiology", "Pathology", "Radiology", "General Practice"
];

$firstNames = ["James", "John", "Robert", "Michael", "William", "David", "Richard", "Joseph", "Thomas", "Charles", "Mary", "Patricia", "Jennifer", "Linda", "Elizabeth", "Barbara", "Susan", "Jessica", "Sarah", "Karen"];
$lastNames = ["Smith", "Johnson", "Williams", "Brown", "Jones", "Garcia", "Miller", "Davis", "Rodriguez", "Martinez", "Hernandez", "Lopez", "Gonzalez", "Wilson", "Anderson", "Thomas", "Taylor", "Moore", "Jackson", "Martin"];

echo "Starting Seed...\n";

foreach ($categories as $catName) {
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$catName]);
    $catId = $stmt->fetchColumn();

    if (!$catId) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$catName]);
        $catId = $pdo->lastInsertId();
        echo "Created Category: $catName\n";
    }

    // First generate doctors with ratings for this category
    $numDoctors = rand(7, 10);
    $categoryDoctors = [];
    for ($i = 0; $i < $numDoctors; $i++) {
        $name = "Dr. " . $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
        $email = strtolower(str_replace([' ', '.'], '', $name)) . rand(10,99) . "@healthyhub.local";
        $phone = "555-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $photoRawName = urlencode($name);
        $photo = "https://ui-avatars.com/api/?name={$photoRawName}&background=random&size=200";
        $details = "Experienced specialist in $catName with over " . rand(5, 25) . " years of clinical practice. Dedicated to providing compassionate and comprehensive care.";
        $rating = rand(10, 50) / 10.0;
        
        $categoryDoctors[] = [
            'category_id' => $catId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'photo' => $photo,
            'details' => $details,
            'rating' => $rating,
            'is_available' => 0 // Default
        ];
    }

    // Sort by rating DESC to find the top doctor
    usort($categoryDoctors, function($a, $b) {
        return $b['rating'] <=> $a['rating'];
    });

    // Assign availability: Top 1 + rand(2, 4) others
    $categoryDoctors[0]['is_available'] = 1;
    $numAvailableTotal = rand(3, 5);
    $remainingIndices = range(1, $numDoctors - 1);
    shuffle($remainingIndices);
    $additionalAvailCount = $numAvailableTotal - 1;
    for ($i = 0; $i < $additionalAvailCount; $i++) {
        $idx = $remainingIndices[$i];
        $categoryDoctors[$idx]['is_available'] = 1;
    }

    // Insert into database
    foreach ($categoryDoctors as $doc) {
        $password = password_hash('doctor123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO doctors (category_id, name, email, password, phone, photo, details, rating, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $doc['category_id'], $doc['name'], $doc['email'], $password, $doc['phone'], 
            $doc['photo'], $doc['details'], $doc['rating'], $doc['is_available']
        ]);
        echo "  Added Doctor: {$doc['name']} (Rating: {$doc['rating']}, Available: {$doc['is_available']})\n";
    }
}

echo "Seeding Complete!\n";
?>

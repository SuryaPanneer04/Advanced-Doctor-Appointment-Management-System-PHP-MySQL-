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

    // Add 2-3 doctors per category
    $numDoctors = rand(2, 3);
    for ($i = 0; $i < $numDoctors; $i++) {
        $name = "Dr. " . $firstNames[array_rand($firstNames)] . " " . $lastNames[array_rand($lastNames)];
        $email = strtolower(str_replace([' ', '.'], '', $name)) . rand(10,99) . "@medcare.local";
        $phone = "555-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Use ui-avatars for a clean placeholder image based on their name
        $photoRawName = urlencode($name);
        $photo = "https://ui-avatars.com/api/?name={$photoRawName}&background=random&size=200";
        
        $details = "Experienced specialist in $catName with over " . rand(5, 25) . " years of clinical practice. Dedicated to providing compassionate and comprehensive care.";

        $stmt = $pdo->prepare("INSERT INTO doctors (category_id, name, email, phone, photo, details, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$catId, $name, $email, $phone, $photo, $details]);
        echo "  Added Doctor: $name\n";
    }
}

echo "Seeding Complete!\n";
?>

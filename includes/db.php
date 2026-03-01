<?php
// includes/db.php

$host = 'localhost';
$db   = 'doctor_appointment_system';
$user = 'root'; // Change as needed
$pass = 'Surya@123';     // Change as needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        // Attempt to create database if it does not exist (for local dev ease)
        try {
            $temp_pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, $options);
            $temp_pdo->exec("CREATE DATABASE `$db`");
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Read and execute initial schema if possible
            $sql_file = __DIR__ . '/../database.sql';
            if (file_exists($sql_file)) {
                $sql = file_get_contents($sql_file);
                $pdo->exec($sql);
            }
        } catch (\PDOException $e2) {
             throw new \PDOException($e2->getMessage(), (int)$e2->getCode());
        }
    } else {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
?>

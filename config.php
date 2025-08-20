<?php
// config.php

$host = "localhost";      // XAMPP runs MySQL locally
$dbname = "kings_escape"; // Change this to your actual database name
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password is empty

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Set error mode to exception for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Stop script and show error if connection fails
    die("Database connection failed: " . $e->getMessage());
}

<?php
// test_mysql.php
$host = 'localhost';
$user = 'root';
$pass = ''; // put the user password here 

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "✅ MySQL is running!<br>";

    // Try to create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS social_network");
    echo "✅ Database 'social_network' created or exists!<br>";

    // Test connecting to the database
    $pdo = new PDO("mysql:host=$host;dbname=social_network", $user, $pass);
    echo "✅ Connected to database successfully!";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

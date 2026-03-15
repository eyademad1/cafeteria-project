<?php
$dbType = "mysql";
$dbName = "cafeteria";
$host = "localhost"; 
$userName = "cafeteria_app";
$password = "StrongAppPassword123!";

try {
    $connection = new PDO("$dbType:host=$host;dbname=$dbName;charset=utf8", $userName, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start();
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
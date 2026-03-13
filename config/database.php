<?php
$dbType = "mysql";
$dbName = "cafeteria";
$host = "localhost"; 
$userName = "root";
$password = "";

try {
    $connection = new PDO("$dbType:host=$host;dbname=$dbName;charset=utf8", $userName, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    session_start();
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}
?>
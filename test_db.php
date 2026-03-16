<?php
$dbType = "mysql";
$dbName = "cafeteria";
$host = "localhost";
$userName = "root";
$password = "";

try {
    $connection = new PDO("$dbType:host=$host;dbname=$dbName;charset=utf8", $userName, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!\n";

    // Test query
    $stmt = $connection->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Found " . $result['count'] . " products in database.\n";

} catch (PDOException $e) {
    echo "Database Connection failed: " . $e->getMessage() . "\n";
}
?>
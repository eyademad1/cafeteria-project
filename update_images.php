<?php
require_once 'config/database.php';

echo "Starting image update process...\n";

try {
    $pdo = $connection; // Use the connection from database.php

    // Read the SQL file
    $sql = file_get_contents('update_images.sql');
    echo "SQL file loaded successfully.\n";

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $updated = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
            $updated++;
        }
    }

    echo "Successfully updated $updated product images!\n";

} catch (Exception $e) {
    echo "Error updating images: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
<?php

$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections']['mysql'];

$dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['hostport']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$username = $dbConfig['username'];
$password = $dbConfig['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlFile = __DIR__ . '/sql/demo_data.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile\n");
    }

    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);

    echo "Demo data seeded successfully.\n";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}


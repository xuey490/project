<?php
// Load database config
$config = require __DIR__ . '/../config/database.php';
$dbConfig = $config['connections']['mysql'];

$dsn = "mysql:host={$dbConfig['hostname']};port={$dbConfig['hostport']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$username = $dbConfig['username'];
$password = $dbConfig['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    $sqlFile = __DIR__ . '/sql/init.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split SQL by semicolon to execute statements one by one if needed, 
    // or just execute raw if the driver supports multiple statements.
    // MySQL PDO supports multiple statements if configured, but let's try raw first.
    
    $pdo->exec($sql);
    
    echo "Migration completed successfully.\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

<?php
// Database configuration
$config = [
    'host' => 'localhost', // Change this to your server's IP address for remote access
    'dbname' => 'cinemoto',
    'username' => 'root', // Use 'remote_user' for remote connections
    'password' => '', // Use the password you set for remote_user
    'port' => 3306 // Default MySQL port
];

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 
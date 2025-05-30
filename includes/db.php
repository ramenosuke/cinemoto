<?php
// Database connection settings
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'plantshop';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?> 
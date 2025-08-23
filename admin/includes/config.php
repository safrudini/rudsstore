<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'ruds_store');
define('DB_USER', 'root');
define('DB_PASS', 'Rud@l123');

// API configuration
define('API_URL', 'http://213.163.206.110:3333/api');
define('API_RESELLER_CODE', 'NF00087');
define('API_PIN', '999105');
define('API_PASSWORD', 'Rudal123');
define('API_KEY', '552C6741-18D6-450A-BC82-FD5082B02477');

// Base URL
define('BASE_URL', 'http://panel-ruds.my.id');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include functions
require_once 'functions.php';
?>
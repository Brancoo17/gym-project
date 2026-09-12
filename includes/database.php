<?php

$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbUser = $_ENV['DB_USER'] ?? 'root';
$dbPass = $_ENV['DB_PASS'] ?? 'root';
$dbName = $_ENV['DB_NAME'] ?? 'gym_mvc';
$dbPort = (int)($_ENV['DB_PORT'] ?? 3306);

$db = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if (!$db) {
    echo "Error: No se pudo conectar a MySQL.";
    echo "errno de depuración: " . mysqli_connect_errno();
    echo "error de depuración: " . mysqli_connect_error();
    exit;
}

$db->set_charset('utf8mb4');
$db->query("SET time_zone = '-03:00'");

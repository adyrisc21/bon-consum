<?php

$host = 'localhost';
$db   = 'adyrisce_bon';   // ← vezi mai jos dacă e corect
$user = 'adyrisce_bon';
$pass = 'adyrisc21'; // ← pune parola reală

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Eroare conexiune DB: " . $e->getMessage());
}
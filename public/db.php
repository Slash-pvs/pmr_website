<?php
// db.php

$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];

try {
    $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
} catch (PDOException $e) {
    // Dev ou prod ?
    $env = $config['env'] ?? 'prod';

    if ($env === 'dev') {
        die("Erreur PDO : " . $e->getMessage());
    } else {
        error_log("Erreur BDD : " . $e->getMessage());
        die("Une erreur est survenue lors de la connexion à la base.");
    }
}

<?php
$config = require __DIR__ . '/config/config.php';
$db = $config['db'];

try {
    $dsn = "mysql:host=" . $db['host'] . ";dbname=" . $db['name'] . ";charset=" . $db['charset'];
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Optionnel mais conseillé

    return $pdo; // <---- ajout ici

} catch (PDOException $e) {
    if (isset($config['env'])) {
        $env = $config['env'];
    } else {
        $env = 'prod';
    }

    if ($env === 'dev') {
        die("Erreur PDO : " . $e->getMessage());
    } else {
        error_log("Erreur BDD : " . $e->getMessage());
        http_response_code(500);
        exit("Erreur de connexion à la base de données.");
    }
}

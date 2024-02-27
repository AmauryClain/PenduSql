<?php
try {

    $mysqlClient = new PDO(
        'mysql:host=localhost:8889;dbname=pendu_sql;charset=utf8',
        'root',
        'root',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erreur: ' . $e->getMessage());
}

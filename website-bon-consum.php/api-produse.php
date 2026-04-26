<?php
require_once 'config.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id, denumire, cod_produs, cod_gestiune, um, pret_unitar, stoc_curent
    FROM produse
    WHERE activ = 1
      AND (
          denumire LIKE ?
          OR cod_produs LIKE ?
      )
    ORDER BY denumire ASC
    LIMIT 10
");

$like = "%$q%";
$stmt->execute([$like, $like]);

echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: magazie.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // verificăm dacă produsul e folosit
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bonuri_pozitii WHERE produs_id = ?");
    $stmt->execute([$id]);
    $countBonuri = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM miscari_stoc WHERE produs_id = ?");
    $stmt->execute([$id]);
    $countMiscari = $stmt->fetchColumn();

    if ($countBonuri > 0 || $countMiscari > 0) {
        // NU ștergem, doar dezactivăm
        $stmt = $pdo->prepare("UPDATE produse SET activ = 0 WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();

        header('Location: magazie.php?dezactivat=1');
        exit;
    }

    // dacă nu e folosit nicăieri -> ștergere completă
    $stmt = $pdo->prepare("DELETE FROM produse WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    header('Location: magazie.php?deleted=1');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Eroare la ștergere: ' . $e->getMessage());
}
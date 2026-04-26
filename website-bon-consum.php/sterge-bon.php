<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$bonId = (int)($_GET['id'] ?? 0);

if ($bonId <= 0) {
    header('Location: arhiva-bonuri.php');
    exit;
}

try {
    $pdo->beginTransaction();

    $stmtBon = $pdo->prepare("SELECT id FROM bonuri WHERE id = ? LIMIT 1");
    $stmtBon->execute([$bonId]);
    if (!$stmtBon->fetch()) {
        throw new Exception('Bonul nu există.');
    }

    $stmtMis = $pdo->prepare("
        SELECT produs_id, cantitate
        FROM miscari_stoc
        WHERE document_tip = 'BON_CONSUM' AND document_id = ?
    ");
    $stmtMis->execute([$bonId]);
    $miscari = $stmtMis->fetchAll();

    $stmtProdus = $pdo->prepare("
        SELECT id, stoc_curent
        FROM produse
        WHERE id = ?
        LIMIT 1
    ");

    $stmtUpdateStoc = $pdo->prepare("
        UPDATE produse
        SET stoc_curent = ?
        WHERE id = ?
    ");

    // Returnăm stocul
    foreach ($miscari as $m) {
        $produsId = (int)$m['produs_id'];
        $cantitate = (float)$m['cantitate'];

        if ($produsId <= 0 || $cantitate <= 0) {
            continue;
        }

        $stmtProdus->execute([$produsId]);
        $produs = $stmtProdus->fetch();
        if ($produs) {
            $stocNou = (float)$produs['stoc_curent'] + $cantitate;
            $stmtUpdateStoc->execute([$stocNou, $produsId]);
        }
    }

    // Ștergem mișcările, pozițiile și bonul
    $stmtDeleteMis = $pdo->prepare("
        DELETE FROM miscari_stoc
        WHERE document_tip = 'BON_CONSUM' AND document_id = ?
    ");
    $stmtDeleteMis->execute([$bonId]);

    $stmtDeletePoz = $pdo->prepare("DELETE FROM bonuri_pozitii WHERE bon_id = ?");
    $stmtDeletePoz->execute([$bonId]);

    $stmtDeleteBon = $pdo->prepare("DELETE FROM bonuri WHERE id = ?");
    $stmtDeleteBon->execute([$bonId]);

    $pdo->commit();

    header('Location: arhiva-bonuri.php?deleted=1');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Eroare la ștergerea bonului: ' . $e->getMessage());
}
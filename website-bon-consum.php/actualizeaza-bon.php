<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: arhiva-bonuri.php');
    exit;
}

function postv(string $key, string $default = ''): string {
    return trim($_POST[$key] ?? $default);
}

function postarr(string $key): array {
    $value = $_POST[$key] ?? [];
    return is_array($value) ? $value : [];
}

$bonId               = (int)($_POST['id'] ?? 0);
$unitatea            = postv('unitatea');
$produs_lucrare      = postv('produs_lucrare');
$numar_document      = postv('numar_document');
$zi                  = postv('zi');
$luna                = postv('luna');
$an                  = postv('an');
$predator            = postv('predator');
$primitor            = postv('primitor');
$cod_gestiune        = postv('cod_gestiune');
$sef_compartiment    = postv('sef_compartiment');
$gestionar           = postv('gestionar');
$primitor_semnatura  = postv('primitor_semnatura');

$produs_id_arr           = postarr('produs_id');
$nr_crt_arr              = postarr('nr_crt');
$denumire_arr            = postarr('denumire');
$cantitate_necesara_arr  = postarr('cantitate_necesara');
$cod_arr                 = postarr('cod');
$um_arr                  = postarr('um');
$cantitate_eliberata_arr = postarr('cantitate_eliberata');
$pret_unitar_arr         = postarr('pret_unitar');
$valoarea_arr            = postarr('valoarea');

if ($bonId <= 0) {
    die('ID bon invalid.');
}

if ($unitatea === '') {
    $unitatea = 'METROREX SA';
}

if ($numar_document === '') {
    die('Numărul documentului este obligatoriu.');
}

if ($zi === '' || $luna === '' || $an === '') {
    die('Data bonului este obligatorie.');
}

if (!checkdate((int)$luna, (int)$zi, (int)$an)) {
    die('Data bonului este invalidă.');
}

$dataBonSql = sprintf('%04d-%02d-%02d', (int)$an, (int)$luna, (int)$zi);

$pozitii = [];
$rowCount = max(
    count($denumire_arr),
    count($cantitate_eliberata_arr),
    count($pret_unitar_arr),
    count($cod_arr),
    count($um_arr),
    count($nr_crt_arr),
    count($produs_id_arr)
);

for ($i = 0; $i < $rowCount; $i++) {
    $denumire = trim((string)($denumire_arr[$i] ?? ''));
    $cantitateNecesara = trim((string)($cantitate_necesara_arr[$i] ?? ''));
    $cod = trim((string)($cod_arr[$i] ?? ''));
    $um = trim((string)($um_arr[$i] ?? ''));
    $cantitateEliberata = trim((string)($cantitate_eliberata_arr[$i] ?? ''));
    $pretUnitar = trim((string)($pret_unitar_arr[$i] ?? ''));
    $valoarea = trim((string)($valoarea_arr[$i] ?? ''));
    $nrCrt = trim((string)($nr_crt_arr[$i] ?? ($i + 1)));
    $produsId = (int)($produs_id_arr[$i] ?? 0);

    $hasContent =
        $denumire !== '' ||
        $cantitateNecesara !== '' ||
        $cod !== '' ||
        $um !== '' ||
        $cantitateEliberata !== '' ||
        $pretUnitar !== '' ||
        $valoarea !== '' ||
        $produsId > 0;

    if (!$hasContent) {
        continue;
    }

    if ($cantitateEliberata !== '' && !is_numeric($cantitateEliberata)) {
        die('Cantitatea eliberată trebuie să fie numerică.');
    }

    if ($pretUnitar !== '' && !is_numeric($pretUnitar)) {
        die('Prețul unitar trebuie să fie numeric.');
    }

    $cantitateEliberataFloat = ($cantitateEliberata === '') ? 0.0 : (float)$cantitateEliberata;
    $pretUnitarFloat = ($pretUnitar === '') ? 0.0 : (float)$pretUnitar;

    if ($valoarea === '') {
        $valoareFloat = round($cantitateEliberataFloat * $pretUnitarFloat, 2);
    } else {
        if (!is_numeric($valoarea)) {
            die('Valoarea trebuie să fie numerică.');
        }
        $valoareFloat = (float)$valoarea;
    }

    $pozitii[] = [
        'produs_id' => $produsId,
        'nr_crt' => $nrCrt === '' ? ($i + 1) : $nrCrt,
        'denumire' => $denumire,
        'cantitate_necesara' => $cantitateNecesara,
        'cod' => $cod,
        'um' => $um,
        'cantitate_eliberata' => $cantitateEliberataFloat,
        'pret_unitar' => $pretUnitarFloat,
        'valoarea' => $valoareFloat,
    ];
}

if (!$pozitii) {
    die('Completează cel puțin o poziție în bon.');
}

try {
    $pdo->beginTransaction();

    $stmtBon = $pdo->prepare("SELECT id FROM bonuri WHERE id = ? LIMIT 1");
    $stmtBon->execute([$bonId]);
    if (!$stmtBon->fetch()) {
        throw new Exception('Bonul nu există.');
    }

    // 1. Returnăm în stoc toate ieșirile vechi ale bonului
    $stmtMisOld = $pdo->prepare("
        SELECT produs_id, cantitate
        FROM miscari_stoc
        WHERE document_tip = 'BON_CONSUM' AND document_id = ?
    ");
    $stmtMisOld->execute([$bonId]);
    $miscariVechi = $stmtMisOld->fetchAll();

    $stmtProdus = $pdo->prepare("
        SELECT id, denumire, pret_unitar, stoc_curent
        FROM produse
        WHERE id = ?
        LIMIT 1
    ");

    $stmtUpdateStoc = $pdo->prepare("
        UPDATE produse
        SET stoc_curent = ?
        WHERE id = ?
    ");

    foreach ($miscariVechi as $mv) {
        $produsId = (int)$mv['produs_id'];
        $cantitate = (float)$mv['cantitate'];

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

    // 2. Ștergem vechile mișcări și poziții
    $stmtDeleteMis = $pdo->prepare("
        DELETE FROM miscari_stoc
        WHERE document_tip = 'BON_CONSUM' AND document_id = ?
    ");
    $stmtDeleteMis->execute([$bonId]);

    $stmtDeletePoz = $pdo->prepare("DELETE FROM bonuri_pozitii WHERE bon_id = ?");
    $stmtDeletePoz->execute([$bonId]);

    // 3. Actualizăm antetul bonului
    $stmtUpdateBon = $pdo->prepare("
        UPDATE bonuri
        SET
            unitatea = ?,
            produs_lucrare = ?,
            numar_document = ?,
            zi = ?,
            luna = ?,
            an = ?,
            predator = ?,
            primitor = ?,
            cod_gestiune = ?,
            sef_compartiment = ?,
            gestionar = ?,
            primitor_semnatura = ?
        WHERE id = ?
    ");

    $stmtUpdateBon->execute([
        $unitatea,
        $produs_lucrare,
        $numar_document,
        $zi,
        $luna,
        $an,
        $predator,
        $primitor,
        $cod_gestiune,
        $sef_compartiment,
        $gestionar,
        $primitor_semnatura,
        $bonId
    ]);

    // 4. Inserăm noile poziții
    $stmtPoz = $pdo->prepare("
        INSERT INTO bonuri_pozitii (
            bon_id,
            produs_id,
            nr_crt,
            denumire,
            cantitate_necesara,
            cod,
            um,
            cantitate_eliberata,
            pret_unitar,
            valoarea
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtMis = $pdo->prepare("
        INSERT INTO miscari_stoc (
            produs_id,
            tip_miscare,
            cantitate,
            pret_unitar,
            stoc_dupa_miscare,
            document_tip,
            document_id,
            document_nr,
            data_miscare,
            observatii
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($pozitii as $poz) {
        $stmtPoz->execute([
            $bonId,
            $poz['produs_id'] > 0 ? $poz['produs_id'] : null,
            $poz['nr_crt'],
            $poz['denumire'],
            $poz['cantitate_necesara'],
            $poz['cod'],
            $poz['um'],
            $poz['cantitate_eliberata'],
            $poz['pret_unitar'],
            $poz['valoarea']
        ]);

        if ($poz['produs_id'] > 0 && $poz['cantitate_eliberata'] > 0) {
            $stmtProdus->execute([$poz['produs_id']]);
            $produs = $stmtProdus->fetch();

            if (!$produs) {
                throw new Exception('Produsul selectat nu există în magazie.');
            }

            $stocCurent = (float)$produs['stoc_curent'];
            $cantitateIesita = (float)$poz['cantitate_eliberata'];
            $stocNou = $stocCurent - $cantitateIesita;

            if ($stocNou < 0) {
                throw new Exception('Stoc insuficient pentru produsul: ' . $produs['denumire']);
            }

            $stmtUpdateStoc->execute([$stocNou, $produs['id']]);

            $pretMiscare = $poz['pret_unitar'] > 0 ? $poz['pret_unitar'] : (float)$produs['pret_unitar'];
            $observatie = 'Consum prin bon nr. ' . $numar_document;
            if ($poz['denumire'] !== '') {
                $observatie .= ' - ' . $poz['denumire'];
            }

            $stmtMis->execute([
                $produs['id'],
                'iesire',
                $cantitateIesita,
                $pretMiscare,
                $stocNou,
                'BON_CONSUM',
                $bonId,
                $numar_document,
                $dataBonSql,
                $observatie
            ]);
        }
    }

    $pdo->commit();

    header('Location: preview-bon.php?id=' . $bonId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Eroare la actualizarea bonului: ' . $e->getMessage());
}
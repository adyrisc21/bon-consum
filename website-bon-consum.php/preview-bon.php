<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Preview bon';
$activePage = 'arhiva';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID invalid.');
}

$stmt = $pdo->prepare("SELECT * FROM bonuri WHERE id = ?");
$stmt->execute([$id]);
$bon = $stmt->fetch();

if (!$bon) {
    die('Bonul nu există.');
}

$stmt = $pdo->prepare("SELECT * FROM bonuri_pozitii WHERE bon_id = ? ORDER BY nr_crt ASC, id ASC");
$stmt->execute([$id]);
$pozitii = $stmt->fetchAll();

for ($i = count($pozitii); $i < 6; $i++) {
    $pozitii[] = [
        'nr_crt' => $i + 1,
        'denumire' => '',
        'cantitate_necesara' => '',
        'cod' => '',
        'um' => '',
        'cantitate_eliberata' => '',
        'pret_unitar' => '',
        'valoarea' => ''
    ];
}

include 'layout-start.php';
?>

<style>
.preview-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}

.preview-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:15px;
}

.preview-btn{
    background:#0f172a;
    color:#fff;
    padding:10px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:700;
    border:none;
    cursor:pointer;
    font-size:14px;
}

.preview-btn.secondary{
    background:#475569;
}

.preview-sheet{
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:18px;
    padding:14px;
    overflow:auto;
}

.preview-bon{
    width:1420px;
    margin:0 auto;
    background:#fff;
}

.separator{
    border-top:2px dashed #666;
    margin:20px 0;
}

table.bon{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.bon td{
    border:1px solid #000;
    padding:0;
    vertical-align:middle;
}

.center{text-align:center}
.left{text-align:left}
.bold{font-weight:700}

.title{
    font-size:18px;
    font-weight:700;
    text-align:center;
    letter-spacing:0;
}

.small{font-size:11px; line-height:1.1}
.normal{font-size:12px}

.value{
    padding:2px 4px;
    font-size:12.5px;
    font-weight:700;
    text-align:center;
    line-height:1.05;
}

.value.left{text-align:left}

.h24 td{height:22px}
.h26 td{height:24px}
.h28 td{height:26px}
.h30 td{height:28px}
.h34 td{height:32px}

.unitatea-cell{
    white-space:nowrap;
}

.unitatea-label{
    display:inline-block;
    margin-right:6px;
    vertical-align:middle;
}

.unitatea-display{
    display:inline-block;
    min-width:170px;
    vertical-align:middle;
}
</style>

<div class="preview-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Preview bon</h1>
            <p class="page-subtitle">Verifici bonul înainte de print.</p>
        </div>
    </div>

    <div class="preview-actions">
        <a href="arhiva-bonuri.php" class="preview-btn secondary">Înapoi</a>
        <a href="print-bon.php?id=<?php echo (int)$bon['id']; ?>&autoprint=1" class="preview-btn" target="_blank">Print</a>
        <a href="editeaza-bon.php?id=<?php echo (int)$bon['id']; ?>" class="preview-btn secondary">Editează</a>
    </div>

    <div class="preview-sheet">
        <div class="preview-bon">
            <?php renderBonPreview($bon, $pozitii); ?>

            <div class="separator"></div>

            <?php renderBonPreview($bon, $pozitii); ?>
        </div>
    </div>
</div>

<?php include 'layout-end.php'; ?>

<?php
function renderBonPreview($bon, $pozitii){ ?>
<table class="bon">
    <colgroup>
        <col style="width:24px">
        <col style="width:60px">
        <col style="width:64px">
        <col style="width:64px">
        <col style="width:104px">
        <col style="width:130px">
        <col style="width:88px">
        <col style="width:118px">
        <col style="width:74px">
        <col style="width:128px">
        <col style="width:72px">

        <col style="width:102px">
        <col style="width:126px">
    </colgroup>

    <tr class="h34">
        <td colspan="5" class="left unitatea-cell">
            <span class="bold unitatea-label">UNITATEA:</span>
            <span class="value left unitatea-display"><?php echo htmlspecialchars($bon['unitatea']); ?></span>
        </td>

        <td colspan="4" class="left">
            Produs, lucrare (comandă)
            <div class="value left"><?php echo htmlspecialchars($bon['produs_lucrare']); ?></div>
        </td>

        <td colspan="4" rowspan="3" class="title">BON DE CONSUM</td>
    </tr>

    <tr class="h26">
        <td colspan="2" rowspan="2" class="center small">Număr document</td>
        <td colspan="3" class="center small">Data</td>
        <td rowspan="2" class="center small">Predător</td>
        <td rowspan="2" class="center small">Primitor</td>
        <td colspan="2" rowspan="2" class="center small">Cod gestiune</td>
    </tr>

    <tr class="h26">
        <td class="center small">Ziua</td>
        <td class="center small">Luna</td>
        <td class="center small">Anul</td>
    </tr>

    <tr class="h30">
        <td colspan="2"><div class="value"><?php echo htmlspecialchars($bon['numar_document']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($bon['zi']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($bon['luna']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($bon['an']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($bon['predator']); ?></div></td>
        <td><div class="value"></div></td>
        <td colspan="2"><div class="value"><?php echo htmlspecialchars($bon['cod_gestiune']); ?></div></td>
        <td class="small center">Cantitatea<br>eliberată</td>
        <td class="small center">Prețul<br>unitar</td>
        <td colspan="2" class="small center">Valoarea</td>
    </tr>

    <tr class="h30">
        <td class="center small">Crt.</td>
        <td colspan="4" class="center small">Denumirea materialelor (inclusiv<br>sort, marca, profil, dimensiune)</td>
        <td class="center small">Cantitatea necesara</td>
        <td colspan="2" class="center small">Cod</td>
        <td class="center small">U/M</td>
        <td></td>
        <td></td>
        <td colspan="2"></td>
    </tr>

    <?php foreach($pozitii as $p){ ?>
    <tr class="h24">
        <td><div class="value"><?php echo htmlspecialchars($p['nr_crt']); ?></div></td>
        <td colspan="4"><div class="value left"><?php echo htmlspecialchars($p['denumire']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($p['cantitate_necesara']); ?></div></td>
        <td colspan="2"><div class="value"><?php echo htmlspecialchars($p['cod']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($p['um']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($p['cantitate_eliberata']); ?></div></td>
        <td><div class="value"><?php echo htmlspecialchars($p['pret_unitar']); ?></div></td>
        <td colspan="2"><div class="value"><?php echo htmlspecialchars($p['valoarea']); ?></div></td>
    </tr>
    <?php } ?>

    <tr class="h28">
        <td colspan="3" class="left small">Data si semnatura</td>
        <td colspan="3" class="center small">Șef compartiment</td>
        <td colspan="3" class="left small">Gestionar</td>
        <td colspan="4" class="left small">Primitor</td>
    </tr>

    <tr class="h28">
        <td colspan="3"></td>
        <td colspan="3"><div class="value"><?php echo htmlspecialchars($bon['sef_compartiment']); ?></div></td>
        <td colspan="3"><div class="value left"><?php echo htmlspecialchars($bon['gestionar']); ?></div></td>
        <td colspan="4"><div class="value left"><?php echo htmlspecialchars($bon['primitor_semnatura']); ?></div></td>
    </tr>
</table>
<?php } ?>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Fișă magazie';
$activePage = 'magazie';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID produs invalid.');
}

$stmt = $pdo->prepare("SELECT * FROM produse WHERE id = ?");
$stmt->execute([$id]);
$produs = $stmt->fetch();

if (!$produs) {
    die('Produsul nu există.');
}

$stmt = $pdo->prepare("
    SELECT *
    FROM miscari_stoc
    WHERE produs_id = ?
    ORDER BY 
        CASE 
            WHEN data_miscare IS NULL THEN 1
            ELSE 0
        END,
        data_miscare DESC,
        id DESC
");
$stmt->execute([$id]);
$miscari = $stmt->fetchAll();

include 'layout-start.php';
?>

<style>
.sheet-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}
.sheet-toolbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.sheet-btn{
    display:inline-block;
    background:#0f172a;
    color:#fff;
    text-decoration:none;
    border:none;
    padding:10px 14px;
    border-radius:12px;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
}
.sheet-btn.secondary{background:#475569}
.sheet-btn.success{background:#15803d}
.sheet-btn.warning{background:#b45309}

.info-grid{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-bottom:18px;
}
.info-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:14px 16px;
}
.info-label{
    font-size:12px;
    color:#64748b;
    margin-bottom:6px;
    font-weight:700;
    text-transform:uppercase;
}
.info-value{
    font-size:18px;
    font-weight:800;
    color:#0f172a;
    line-height:1.3;
}
.stock-pill{
    display:inline-block;
    padding:8px 12px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    background:#dcfce7;
    color:#166534;
}
.stock-pill.low{
    background:#fef3c7;
    color:#92400e;
}
.stock-pill.zero{
    background:#fee2e2;
    color:#991b1b;
}

.table-wrap{
    overflow:auto;
    border:1px solid #e2e8f0;
    border-radius:18px;
    background:#fff;
}
.sheet-table{
    width:100%;
    border-collapse:collapse;
    min-width:1200px;
}
.sheet-table th{
    background:#0f172a;
    color:#fff;
    text-align:left;
    padding:14px 12px;
    font-size:13px;
    white-space:nowrap;
}
.sheet-table td{
    padding:12px;
    border-bottom:1px solid #e2e8f0;
    font-size:14px;
    vertical-align:top;
}
.sheet-table tr:hover td{
    background:#f8fafc;
}
.type-badge{
    display:inline-block;
    padding:7px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
}
.type-in{
    background:#dcfce7;
    color:#166534;
}
.type-out{
    background:#fee2e2;
    color:#991b1b;
}
.qty-in{
    font-weight:800;
    color:#166534;
}
.qty-out{
    font-weight:800;
    color:#991b1b;
}
.muted{
    color:#64748b;
    font-size:13px;
}
.empty-box{
    background:#f8fafc;
    border:1px dashed #cbd5e1;
    border-radius:18px;
    padding:34px 20px;
    text-align:center;
    color:#64748b;
}
.empty-box h3{
    margin:0 0 8px;
    color:#0f172a;
    font-size:20px;
}

@media (max-width: 980px){
    .info-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}
@media (max-width: 760px){
    .sheet-card{
        padding:14px;
    }
    .info-grid{
        grid-template-columns:1fr;
    }
}
</style>

<?php
$stoc = (float)($produs['stoc_curent'] ?? 0);
$stockClass = '';
if ($stoc <= 0) {
    $stockClass = ' zero';
} elseif ($stoc <= 5) {
    $stockClass = ' low';
}

$dataIntrareAfisata = '-';
if (!empty($produs['data_intrare'])) {
    $tsIntrare = strtotime($produs['data_intrare']);
    $dataIntrareAfisata = $tsIntrare ? date('d.m.Y', $tsIntrare) : $produs['data_intrare'];
}
?>

<div class="sheet-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Fișă magazie</h1>
            <p class="page-subtitle">Istoricul mișcărilor pentru produsul selectat.</p>
        </div>
        <div class="sheet-toolbar">
            <a href="magazie.php" class="sheet-btn secondary">Înapoi la magazie</a>
            <a href="editeaza-produs.php?id=<?php echo (int)$produs['id']; ?>" class="sheet-btn warning">Editează produs</a>
            <a href="intrare-stoc.php?id=<?php echo (int)$produs['id']; ?>" class="sheet-btn success">Intrare stoc</a>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Denumire</div>
            <div class="info-value"><?php echo htmlspecialchars($produs['denumire']); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Cod produs</div>
            <div class="info-value"><?php echo htmlspecialchars($produs['cod_produs'] ?: '-'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Cod gestiune</div>
            <div class="info-value"><?php echo htmlspecialchars($produs['cod_gestiune'] ?: '-'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Stoc curent</div>
            <div class="info-value">
                <span class="stock-pill<?php echo $stockClass; ?>">
                    <?php echo number_format($stoc, 2, '.', ''); ?> <?php echo htmlspecialchars($produs['um'] ?: ''); ?>
                </span>
            </div>
        </div>

        <div class="info-box">
            <div class="info-label">U/M</div>
            <div class="info-value"><?php echo htmlspecialchars($produs['um'] ?: '-'); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Preț unitar</div>
            <div class="info-value"><?php echo number_format((float)($produs['pret_unitar'] ?? 0), 2, '.', ''); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Data intrare</div>
            <div class="info-value"><?php echo htmlspecialchars($dataIntrareAfisata); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Furnizor</div>
            <div class="info-value"><?php echo htmlspecialchars($produs['furnizor'] ?: '-'); ?></div>
        </div>
    </div>

    <?php if (!$miscari): ?>
        <div class="empty-box">
            <h3>Nu există mișcări</h3>
            <div>Pentru acest produs nu există încă intrări sau ieșiri înregistrate.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="sheet-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Tip</th>
                        <th>Document</th>
                        <th>Nr. document</th>
                        <th>Intrare</th>
                        <th>Ieșire</th>
                        <th>Preț unitar</th>
                        <th>Stoc după mișcare</th>
                        <th>Observații</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($miscari as $m): ?>
                        <?php
                            $tip = strtolower((string)($m['tip_miscare'] ?? ''));
                            $isIntrare = in_array($tip, ['intrare', 'in', 'intrari']);
                            $isIesire = in_array($tip, ['iesire', 'ieșire', 'out', 'consum']);

                            $dataMiscareRaw = $m['data_miscare'] ?? '';
                            $dataMiscare = '-';
                            if (!empty($dataMiscareRaw)) {
                                $tsMiscare = strtotime($dataMiscareRaw);
                                $dataMiscare = $tsMiscare ? date('d.m.Y', $tsMiscare) : $dataMiscareRaw;
                            }

                            $documentTip = $m['document_tip'] ?? '-';
                            $documentNr = $m['document_nr'] ?? '-';
                            $cantitate = (float)($m['cantitate'] ?? 0);
                            $pret = (float)($m['pret_unitar'] ?? 0);
                            $stocDupa = $m['stoc_dupa_miscare'] ?? '';
                            $obs = $m['observatii'] ?? '';

                            $createdAtAfisat = '';
                            if (!empty($m['created_at'])) {
                                $tsCreated = strtotime($m['created_at']);
                                $createdAtAfisat = $tsCreated ? date('d.m.Y H:i', $tsCreated) : $m['created_at'];
                            }
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dataMiscare); ?></td>
                            <td>
                                <?php if ($isIntrare): ?>
                                    <span class="type-badge type-in">Intrare</span>
                                <?php elseif ($isIesire): ?>
                                    <span class="type-badge type-out">Ieșire</span>
                                <?php else: ?>
                                    <span class="type-badge"><?php echo htmlspecialchars($m['tip_miscare'] ?: '-'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($documentTip); ?></td>
                            <td><?php echo htmlspecialchars($documentNr); ?></td>
                            <td>
                                <?php if ($isIntrare): ?>
                                    <span class="qty-in"><?php echo number_format($cantitate, 2, '.', ''); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isIesire): ?>
                                    <span class="qty-out"><?php echo number_format($cantitate, 2, '.', ''); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($pret, 2, '.', ''); ?></td>
                            <td><?php echo ($stocDupa === '' || $stocDupa === null) ? '-' : number_format((float)$stocDupa, 2, '.', ''); ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($obs ?: '-'); ?></div>
                                <?php if ($createdAtAfisat !== ''): ?>
                                    <div class="muted"><?php echo htmlspecialchars($createdAtAfisat); ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'layout-end.php'; ?>
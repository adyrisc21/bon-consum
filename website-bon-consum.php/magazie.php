<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Magazie';
$activePage = 'magazie';

$q = trim($_GET['q'] ?? '');

$sql = "
    SELECT *
    FROM produse
    WHERE activ = 1
";
$params = [];

if ($q !== '') {
    $sql .= " AND (
        denumire LIKE ?
        OR cod_produs LIKE ?
        OR cod_gestiune LIKE ?
        OR um LIKE ?
        OR furnizor LIKE ?
    )";
    $like = "%{$q}%";
    $params = [$like, $like, $like, $like, $like];
}

$sql .= " ORDER BY denumire ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produse = $stmt->fetchAll();

$totalProduse = count($produse);
$totalStoc = 0;
foreach ($produse as $p) {
    $totalStoc += (float)($p['stoc_curent'] ?? 0);
}

include 'layout-start.php';
?>

<style>
.mag-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}
.mag-toolbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.mag-btn{
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
.mag-btn.secondary{background:#475569}
.mag-btn.success{background:#15803d}
.mag-btn.warning{background:#b45309}

.search-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:14px;
    margin-bottom:16px;
}
.search-form{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.search-input{
    flex:1;
    min-width:260px;
    height:44px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    padding:0 14px;
    font-size:14px;
    outline:none;
}
.search-input:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}

.stats-row{
    display:flex;
    gap:14px;
    flex-wrap:wrap;
    margin-bottom:16px;
}
.stat-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:14px 16px;
    min-width:180px;
}
.stat-label{
    font-size:12px;
    color:#64748b;
    margin-bottom:6px;
    font-weight:700;
    text-transform:uppercase;
}
.stat-value{
    font-size:24px;
    font-weight:800;
    color:#0f172a;
}

.notice{
    border-radius:14px;
    padding:14px 16px;
    margin-bottom:16px;
    font-size:14px;
    font-weight:700;
}
.notice.success{
    background:#dcfce7;
    color:#166534;
    border:1px solid #bbf7d0;
}
.notice.info{
    background:#dbeafe;
    color:#1d4ed8;
    border:1px solid #bfdbfe;
}

.table-wrap{
    overflow:auto;
    border:1px solid #e2e8f0;
    border-radius:18px;
    background:#fff;
}
.mag-table{
    width:100%;
    border-collapse:collapse;
    min-width:1280px;
}
.mag-table th{
    background:#0f172a;
    color:#fff;
    text-align:left;
    padding:14px 12px;
    font-size:13px;
    white-space:nowrap;
}
.mag-table td{
    padding:12px;
    border-bottom:1px solid #e2e8f0;
    font-size:14px;
    vertical-align:top;
}
.mag-table tr:hover td{
    background:#f8fafc;
}
.prod-name{
    font-weight:800;
    color:#0f172a;
}
.muted{
    color:#64748b;
    font-size:13px;
}
.stock-badge{
    display:inline-block;
    padding:7px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:800;
    background:#dcfce7;
    color:#166534;
}
.stock-badge.low{
    background:#fef3c7;
    color:#92400e;
}
.stock-badge.zero{
    background:#fee2e2;
    color:#991b1b;
}
.actions{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.action-btn{
    display:inline-block;
    text-decoration:none;
    border:none;
    padding:8px 10px;
    border-radius:10px;
    font-size:12px;
    font-weight:700;
    color:#fff;
    cursor:pointer;
}
.action-btn.sheet{background:#2563eb}
.action-btn.edit{background:#b45309}
.action-btn.stock{background:#15803d}
.action-btn.delete{background:#b91c1c}

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

@media (max-width: 760px){
    .mag-card{
        padding:14px;
    }
    .search-input{
        min-width:100%;
    }
}
</style>

<div class="mag-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Magazie</h1>
            <p class="page-subtitle">Vezi produsele din stoc, cauți rapid și intri în fișa de magazie.</p>
        </div>
        <div class="mag-toolbar">
            <a href="produs-nou.php" class="mag-btn">Produs nou</a>
            <a href="intrare-stoc.php" class="mag-btn success">Intrare stoc</a>
        </div>
    </div>

    <?php if (isset($_GET['created'])): ?>
        <div class="notice success">Produsul a fost adăugat cu succes.</div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice success">Produsul a fost actualizat cu succes.</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice success">Produsul a fost șters.</div>
    <?php endif; ?>

    <?php if (isset($_GET['dezactivat'])): ?>
        <div class="notice info">Produsul era deja folosit în bonuri sau mișcări de stoc, a fost dezactivat și nu mai apare în listă.</div>
    <?php endif; ?>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Produse afișate</div>
            <div class="stat-value"><?php echo $totalProduse; ?></div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total stoc</div>
            <div class="stat-value"><?php echo number_format($totalStoc, 2, '.', ''); ?></div>
        </div>
    </div>

    <div class="search-box">
        <form method="get" class="search-form">
            <input
                type="text"
                name="q"
                class="search-input"
                placeholder="Caută după denumire, cod produs, cod gestiune, UM, furnizor..."
                value="<?php echo htmlspecialchars($q); ?>"
            >
            <button type="submit" class="mag-btn">Caută</button>
            <?php if ($q !== ''): ?>
                <a href="magazie.php" class="mag-btn secondary">Resetează</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$produse): ?>
        <div class="empty-box">
            <h3>Nu există produse</h3>
            <div>Nu am găsit produse pentru criteriul căutat.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="mag-table">
                <thead>
                    <tr>
                        <th>Denumire</th>
                        <th>Cod produs</th>
                        <th>Cod gestiune</th>
                        <th>U/M</th>
                        <th>Preț unitar</th>
                        <th>Stoc curent</th>
                        <th>Data intrare</th>
                        <th>Furnizor</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produse as $produs): ?>
                        <?php
                            $stoc = (float)($produs['stoc_curent'] ?? 0);
                            $stockClass = 'stock-badge';
                            if ($stoc <= 0) {
                                $stockClass .= ' zero';
                            } elseif ($stoc <= 5) {
                                $stockClass .= ' low';
                            }

                            $dataIntrareAfisata = '-';
                            if (!empty($produs['data_intrare'])) {
                                $ts = strtotime($produs['data_intrare']);
                                $dataIntrareAfisata = $ts ? date('d.m.Y', $ts) : $produs['data_intrare'];
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="prod-name"><?php echo htmlspecialchars($produs['denumire']); ?></div>
                                <?php if (!empty($produs['observatii'])): ?>
                                    <div class="muted"><?php echo htmlspecialchars($produs['observatii']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($produs['cod_produs'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($produs['cod_gestiune'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($produs['um'] ?? ''); ?></td>
                            <td><?php echo number_format((float)($produs['pret_unitar'] ?? 0), 2, '.', ''); ?></td>
                            <td>
                                <span class="<?php echo $stockClass; ?>">
                                    <?php echo number_format($stoc, 2, '.', ''); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($dataIntrareAfisata); ?></td>
                            <td><?php echo htmlspecialchars($produs['furnizor'] ?: '-'); ?></td>
                            <td>
                                <div class="actions">
                                    <a class="action-btn sheet" href="fisa-magazie.php?id=<?php echo (int)$produs['id']; ?>">Fișă</a>
                                    <a class="action-btn edit" href="editeaza-produs.php?id=<?php echo (int)$produs['id']; ?>">Editează</a>
                                    <a class="action-btn stock" href="intrare-stoc.php?id=<?php echo (int)$produs['id']; ?>">Intrare</a>
                                    <a class="action-btn delete"
                                       href="sterge-produs.php?id=<?php echo (int)$produs['id']; ?>"
                                       onclick="return confirm('Sigur vrei să ștergi produsul?');">
                                       Șterge
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'layout-end.php'; ?>
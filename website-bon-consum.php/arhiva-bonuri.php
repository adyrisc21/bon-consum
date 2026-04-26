<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Arhivă bonuri';
$activePage = 'arhiva';

$q = trim($_GET['q'] ?? '');

$sql = "
    SELECT 
        b.*,
        (
            SELECT GROUP_CONCAT(bp.denumire ORDER BY bp.nr_crt ASC SEPARATOR ', ')
            FROM bonuri_pozitii bp
            WHERE bp.bon_id = b.id
        ) AS materiale
    FROM bonuri b
    WHERE 1
";
$params = [];

if ($q !== '') {
    $sql .= " AND (
        b.numar_document LIKE ?
        OR b.gestionar LIKE ?
        OR b.primitor_semnatura LIKE ?
        OR CONCAT(b.zi, '.', b.luna, '.', b.an) LIKE ?
        OR EXISTS (
            SELECT 1
            FROM bonuri_pozitii bp2
            WHERE bp2.bon_id = b.id
              AND (
                  bp2.denumire LIKE ?
                  OR bp2.cod LIKE ?
              )
        )
    )";
    $like = "%{$q}%";
    $params = [$like, $like, $like, $like, $like, $like];
}

$sql .= " ORDER BY CAST(b.numar_document AS UNSIGNED) DESC, b.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bonuri = $stmt->fetchAll();

include 'layout-start.php';
?>

<style>
.archive-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}
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
.archive-btn{
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
.archive-btn.secondary{background:#475569}

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

.table-wrap{
    overflow:auto;
    border:1px solid #e2e8f0;
    border-radius:18px;
    background:#fff;
}
.archive-table{
    width:100%;
    border-collapse:collapse;
    min-width:1100px;
}
.archive-table th{
    background:#0f172a;
    color:#fff;
    text-align:left;
    padding:14px 12px;
    font-size:13px;
    white-space:nowrap;
}
.archive-table td{
    padding:12px;
    border-bottom:1px solid #e2e8f0;
    font-size:14px;
    vertical-align:top;
}
.archive-table tr:hover td{
    background:#f8fafc;
}
.nr-bon{
    font-weight:800;
    color:#0f172a;
}
.materiale-cell{
    max-width:420px;
    line-height:1.45;
}
.muted{
    color:#64748b;
    font-size:13px;
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
.action-btn.preview{background:#2563eb}
.action-btn.print{background:#0f172a}
.action-btn.edit{background:#b45309}
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
    .archive-card{
        padding:14px;
    }
    .search-input{
        min-width:100%;
    }
}
</style>

<div class="archive-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Arhivă bonuri</h1>
            <p class="page-subtitle">Cauți rapid bonurile salvate, vezi materialele și le poți previzualiza, edita sau printa.</p>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Total bonuri</div>
            <div class="stat-value"><?php echo count($bonuri); ?></div>
        </div>
    </div>

    <div class="search-box">
        <form method="get" class="search-form">
            <input
                type="text"
                name="q"
                class="search-input"
                placeholder="Caută după nr. bon, material, cod, gestionar, primitor, dată..."
                value="<?php echo htmlspecialchars($q); ?>"
            >
            <button type="submit" class="archive-btn">Caută</button>
            <?php if ($q !== ''): ?>
                <a href="arhiva-bonuri.php" class="archive-btn secondary">Resetează</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!$bonuri): ?>
        <div class="empty-box">
            <h3>Nu există bonuri</h3>
            <div>Nu am găsit niciun bon pentru criteriul căutat.</div>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="archive-table">
                <thead>
                    <tr>
                        <th>Nr. bon</th>
                        <th>Data</th>
                        <th>Denumire materiale</th>
                        <th>Gestionar</th>
                        <th>Primitor</th>
                        <th>Cod gestiune</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bonuri as $bon): ?>
                        <tr>
                            <td>
                                <div class="nr-bon"><?php echo htmlspecialchars($bon['numar_document']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($bon['zi'] . '.' . $bon['luna'] . '.' . $bon['an']); ?></div>
                            </td>
                            <td class="materiale-cell">
                                <div><?php echo htmlspecialchars($bon['materiale'] ?: '-'); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($bon['gestionar']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($bon['primitor_semnatura']); ?></div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($bon['cod_gestiune']); ?></div>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="action-btn preview" href="preview-bon.php?id=<?php echo (int)$bon['id']; ?>">Preview</a>
                                    <a class="action-btn print"
   href="print-bon.php?id=<?php echo (int)$bon['id']; ?>&autoprint=1"
   target="_blank">
   Print
</a>
                                    <a class="action-btn edit" href="editeaza-bon.php?id=<?php echo (int)$bon['id']; ?>">Editează</a>
                                    <a class="action-btn delete"
                                       href="sterge-bon.php?id=<?php echo (int)$bon['id']; ?>"
                                       onclick="return confirm('Sigur vrei să ștergi acest bon?');">
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
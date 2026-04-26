<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Intrare stoc';
$activePage = 'intrare-stoc';

$selectedId = (int)($_GET['id'] ?? $_POST['produs_id'] ?? 0);
$eroare = '';

$stmt = $pdo->query("
    SELECT id, denumire, cod_produs, cod_gestiune, um, pret_unitar, stoc_curent, activ
    FROM produse
    WHERE activ = 1
    ORDER BY denumire ASC
");
$produse = $stmt->fetchAll();

$values = [
    'produs_id' => $selectedId,
    'data_miscare' => date('d.m.Y'),
    'cantitate' => '',
    'pret_unitar' => '',
    'document_tip' => 'NIR',
    'document_nr' => '',
    'observatii' => '',
];

$produsSelectat = null;
if ($selectedId > 0) {
    $stmtProd = $pdo->prepare("
        SELECT id, denumire, cod_produs, cod_gestiune, um, pret_unitar, stoc_curent, activ
        FROM produse
        WHERE id = ?
        LIMIT 1
    ");
    $stmtProd->execute([$selectedId]);
    $produsSelectat = $stmtProd->fetch();

    if ($produsSelectat && $values['pret_unitar'] === '') {
        $values['pret_unitar'] = $produsSelectat['pret_unitar'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['produs_id'] = (int)($_POST['produs_id'] ?? 0);
    $values['data_miscare'] = trim($_POST['data_miscare'] ?? date('d.m.Y'));
    $values['cantitate'] = trim($_POST['cantitate'] ?? '');
    $values['pret_unitar'] = trim($_POST['pret_unitar'] ?? '');
    $values['document_tip'] = trim($_POST['document_tip'] ?? 'NIR');
    $values['document_nr'] = trim($_POST['document_nr'] ?? '');
    $values['observatii'] = trim($_POST['observatii'] ?? '');

    $dataMiscareSql = null;
    if ($values['data_miscare'] !== '') {
        $d = DateTime::createFromFormat('d.m.Y', $values['data_miscare']);
        if (!$d || $d->format('d.m.Y') !== $values['data_miscare']) {
            $eroare = 'Data mișcare trebuie să fie în format zz.ll.aaaa.';
        } else {
            $dataMiscareSql = $d->format('Y-m-d');
        }
    }

    if ($eroare === '') {
        if ($values['produs_id'] <= 0) {
            $eroare = 'Selectează un produs.';
        } elseif ($values['cantitate'] === '' || !is_numeric($values['cantitate']) || (float)$values['cantitate'] <= 0) {
            $eroare = 'Cantitatea trebuie să fie numerică și mai mare decât 0.';
        } elseif ($values['pret_unitar'] === '' || !is_numeric($values['pret_unitar'])) {
            $eroare = 'Prețul unitar trebuie să fie numeric.';
        } else {
            $stmtProd = $pdo->prepare("
                SELECT id, denumire, cod_produs, cod_gestiune, um, pret_unitar, stoc_curent, activ
                FROM produse
                WHERE id = ?
                LIMIT 1
            ");
            $stmtProd->execute([$values['produs_id']]);
            $produsSelectat = $stmtProd->fetch();

            if (!$produsSelectat) {
                $eroare = 'Produsul selectat nu există.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $cantitate = (float)$values['cantitate'];
                    $pretUnit = (float)$values['pret_unitar'];
                    $stocInitial = (float)$produsSelectat['stoc_curent'];
                    $stocFinal = $stocInitial + $cantitate;

                    $stmtUpdate = $pdo->prepare("
                        UPDATE produse
                        SET stoc_curent = ?, pret_unitar = ?
                        WHERE id = ?
                    ");
                    $stmtUpdate->execute([
                        $stocFinal,
                        $pretUnit,
                        $produsSelectat['id']
                    ]);

                    $stmtInsert = $pdo->prepare("
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
                    $stmtInsert->execute([
                        $produsSelectat['id'],
                        'intrare',
                        $cantitate,
                        $pretUnit,
                        $stocFinal,
                        $values['document_tip'] !== '' ? $values['document_tip'] : 'NIR',
                        null,
                        $values['document_nr'],
                        $dataMiscareSql ?: date('Y-m-d'),
                        $values['observatii']
                    ]);

                    $pdo->commit();

                    header('Location: fisa-magazie.php?id=' . (int)$produsSelectat['id']);
                    exit;
                } catch (Exception $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $eroare = 'Eroare la salvare: ' . $e->getMessage();
                }
            }
        }
    }
}

include 'layout-start.php';
?>

<style>
.stock-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}
.stock-toolbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.stock-btn{
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
.stock-btn.secondary{background:#475569}
.stock-btn.success{background:#15803d}

.alert{
    border-radius:14px;
    padding:14px 16px;
    margin-bottom:16px;
    font-size:14px;
    font-weight:700;
}
.alert.error{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
}

.info-strip{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:14px 16px;
    margin-bottom:18px;
    color:#475569;
    font-size:14px;
}

.quick-info{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:14px;
    margin-bottom:18px;
}
.quick-box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:14px 16px;
}
.quick-label{
    font-size:12px;
    color:#64748b;
    margin-bottom:6px;
    font-weight:700;
    text-transform:uppercase;
}
.quick-value{
    font-size:18px;
    font-weight:800;
    color:#0f172a;
    line-height:1.3;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
}
.form-group{
    display:flex;
    flex-direction:column;
}
.form-group.full{
    grid-column:1 / -1;
}
.form-group label{
    font-size:13px;
    font-weight:700;
    color:#0f172a;
    margin-bottom:7px;
}
.form-control{
    width:100%;
    height:46px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    padding:0 14px;
    font-size:14px;
    outline:none;
    background:#fff;
}
.form-control:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}
textarea.form-control{
    height:110px;
    padding:12px 14px;
    resize:vertical;
}
.bottom-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:18px;
    flex-wrap:wrap;
}
@media (max-width: 980px){
    .quick-info{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}
@media (max-width: 760px){
    .stock-card{
        padding:14px;
    }
    .form-grid,
    .quick-info{
        grid-template-columns:1fr;
    }
}
</style>

<div class="stock-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Intrare stoc</h1>
            <p class="page-subtitle">Adaugi stoc pentru un produs și înregistrezi mișcarea în fișa de magazie.</p>
        </div>
        <div class="stock-toolbar">
            <a href="magazie.php" class="stock-btn secondary">Înapoi la magazie</a>
            <?php if ($produsSelectat): ?>
                <a href="fisa-magazie.php?id=<?php echo (int)$produsSelectat['id']; ?>" class="stock-btn">Fișă magazie</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="info-strip">
        Selectezi produsul, completezi cantitatea și salvezi. Stocul curent se actualizează automat.
    </div>

    <?php if ($eroare): ?>
        <div class="alert error"><?php echo htmlspecialchars($eroare); ?></div>
    <?php endif; ?>

    <?php if ($produsSelectat): ?>
        <div class="quick-info">
            <div class="quick-box">
                <div class="quick-label">Produs</div>
                <div class="quick-value"><?php echo htmlspecialchars($produsSelectat['denumire']); ?></div>
            </div>
            <div class="quick-box">
                <div class="quick-label">Cod produs</div>
                <div class="quick-value"><?php echo htmlspecialchars($produsSelectat['cod_produs'] ?: '-'); ?></div>
            </div>
            <div class="quick-box">
                <div class="quick-label">U/M</div>
                <div class="quick-value"><?php echo htmlspecialchars($produsSelectat['um'] ?: '-'); ?></div>
            </div>
            <div class="quick-box">
                <div class="quick-label">Stoc curent</div>
                <div class="quick-value"><?php echo number_format((float)$produsSelectat['stoc_curent'], 2, '.', ''); ?></div>
            </div>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-grid">
            <div class="form-group full">
                <label>Produs</label>
                <select name="produs_id" class="form-control" required onchange="this.form.submit()">
                    <option value="">Selectează produsul</option>
                    <?php foreach ($produse as $produs): ?>
                        <option value="<?php echo (int)$produs['id']; ?>" <?php echo ((int)$values['produs_id'] === (int)$produs['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($produs['denumire'] . ' | Cod: ' . ($produs['cod_produs'] ?: '-') . ' | Stoc: ' . number_format((float)$produs['stoc_curent'], 2, '.', '') . ' ' . ($produs['um'] ?: '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Data mișcare</label>
                <input type="text" name="data_miscare" class="form-control" placeholder="zz.ll.aaaa" value="<?php echo htmlspecialchars($values['data_miscare']); ?>" required>
            </div>

            <div class="form-group">
                <label>Cantitate intrată</label>
                <input type="text" name="cantitate" class="form-control" value="<?php echo htmlspecialchars($values['cantitate']); ?>" required>
            </div>

            <div class="form-group">
                <label>Preț unitar</label>
                <input type="text" name="pret_unitar" class="form-control" value="<?php echo htmlspecialchars($values['pret_unitar']); ?>" required>
            </div>

            <div class="form-group">
                <label>Tip document</label>
                <input type="text" name="document_tip" class="form-control" value="<?php echo htmlspecialchars($values['document_tip']); ?>" placeholder="Ex: NIR, Factură">
            </div>

            <div class="form-group">
                <label>Nr. document</label>
                <input type="text" name="document_nr" class="form-control" value="<?php echo htmlspecialchars($values['document_nr']); ?>" placeholder="Ex: 125 / F123">
            </div>

            <div class="form-group full">
                <label>Observații</label>
                <textarea name="observatii" class="form-control" placeholder="Detalii suplimentare despre intrare"><?php echo htmlspecialchars($values['observatii']); ?></textarea>
            </div>
        </div>

        <div class="bottom-actions">
            <a href="magazie.php" class="stock-btn secondary">Anulează</a>
            <button type="submit" class="stock-btn success">Salvează intrarea</button>
        </div>
    </form>
</div>

<?php include 'layout-end.php'; ?>
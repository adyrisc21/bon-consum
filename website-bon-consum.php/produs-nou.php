<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Produs nou';
$activePage = 'produs-nou';

$eroare = '';

$values = [
    'denumire' => '',
    'cod_produs' => '',
    'cod_gestiune' => '',
    'um' => '',
    'pret_unitar' => '0',
    'stoc_curent' => '0',
    'data_intrare' => date('d.m.Y'),
    'furnizor' => '',
    'observatii' => '',
    'activ' => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['denumire']     = trim($_POST['denumire'] ?? '');
    $values['cod_produs']   = trim($_POST['cod_produs'] ?? '');
    $values['cod_gestiune'] = trim($_POST['cod_gestiune'] ?? '');
    $values['um']           = trim($_POST['um'] ?? '');
    $values['pret_unitar']  = trim($_POST['pret_unitar'] ?? '0');
    $values['stoc_curent']  = trim($_POST['stoc_curent'] ?? '0');
    $values['data_intrare'] = trim($_POST['data_intrare'] ?? '');
    $values['furnizor']     = trim($_POST['furnizor'] ?? '');
    $values['observatii']   = trim($_POST['observatii'] ?? '');
    $values['activ']        = isset($_POST['activ']) ? 1 : 0;

    $dataIntrareSql = null;
    if ($values['data_intrare'] !== '') {
        $d = DateTime::createFromFormat('d.m.Y', $values['data_intrare']);
        if (!$d || $d->format('d.m.Y') !== $values['data_intrare']) {
            $eroare = 'Data intrare trebuie să fie în format zz.ll.aaaa.';
        } else {
            $dataIntrareSql = $d->format('Y-m-d');
        }
    }

    if ($eroare === '') {
        if ($values['denumire'] === '') {
            $eroare = 'Denumirea produsului este obligatorie.';
        } elseif ($values['um'] === '') {
            $eroare = 'U/M este obligatoriu.';
        } elseif (!is_numeric($values['pret_unitar'])) {
            $eroare = 'Prețul unitar trebuie să fie numeric.';
        } elseif (!is_numeric($values['stoc_curent'])) {
            $eroare = 'Stocul curent trebuie să fie numeric.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO produse (
                    denumire,
                    cod_produs,
                    cod_gestiune,
                    um,
                    pret_unitar,
                    stoc_curent,
                    data_intrare,
                    furnizor,
                    observatii,
                    activ
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $values['denumire'],
                $values['cod_produs'],
                $values['cod_gestiune'],
                $values['um'],
                $values['pret_unitar'],
                $values['stoc_curent'],
                $dataIntrareSql,
                $values['furnizor'],
                $values['observatii'],
                $values['activ']
            ]);

            header('Location: magazie.php?created=1');
            exit;
        }
    }
}

include 'layout-start.php';
?>

<style>
.new-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}
.new-toolbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.new-btn{
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
.new-btn.secondary{background:#475569}
.new-btn.success{background:#15803d}

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
.checkbox-row{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:8px;
}
.checkbox-row input{
    width:18px;
    height:18px;
}
.bottom-actions{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    margin-top:18px;
    flex-wrap:wrap;
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
@media (max-width: 760px){
    .new-card{
        padding:14px;
    }
    .form-grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="new-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Produs nou</h1>
            <p class="page-subtitle">Adaugi un produs nou în magazie.</p>
        </div>
        <div class="new-toolbar">
            <a href="magazie.php" class="new-btn secondary">Înapoi la magazie</a>
        </div>
    </div>

    <div class="info-strip">
        Completează datele de bază pentru produs, apoi salvează.
    </div>

    <?php if ($eroare): ?>
        <div class="alert error"><?php echo htmlspecialchars($eroare); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-grid">
            <div class="form-group full">
                <label>Denumire produs</label>
                <input type="text" name="denumire" class="form-control" value="<?php echo htmlspecialchars($values['denumire']); ?>" required>
            </div>

            <div class="form-group">
                <label>Cod produs</label>
                <input type="text" name="cod_produs" class="form-control" value="<?php echo htmlspecialchars($values['cod_produs']); ?>">
            </div>

            <div class="form-group">
                <label>Cod gestiune</label>
                <input type="text" name="cod_gestiune" class="form-control" value="<?php echo htmlspecialchars($values['cod_gestiune']); ?>">
            </div>

            <div class="form-group">
                <label>U/M</label>
                <input type="text" name="um" class="form-control" value="<?php echo htmlspecialchars($values['um']); ?>" required>
            </div>

            <div class="form-group">
                <label>Preț unitar</label>
                <input type="text" name="pret_unitar" class="form-control" value="<?php echo htmlspecialchars($values['pret_unitar']); ?>">
            </div>

            <div class="form-group">
                <label>Stoc curent</label>
                <input type="text" name="stoc_curent" class="form-control" value="<?php echo htmlspecialchars($values['stoc_curent']); ?>">
            </div>

            <div class="form-group">
                <label>Data intrare</label>
                <input type="text" name="data_intrare" class="form-control" placeholder="zz.ll.aaaa" value="<?php echo htmlspecialchars($values['data_intrare']); ?>">
            </div>

            <div class="form-group">
                <label>Furnizor</label>
                <input type="text" name="furnizor" class="form-control" value="<?php echo htmlspecialchars($values['furnizor']); ?>">
            </div>

            <div class="form-group full">
                <label>Observații</label>
                <textarea name="observatii" class="form-control"><?php echo htmlspecialchars($values['observatii']); ?></textarea>
            </div>

            <div class="form-group full">
                <label>Status</label>
                <div class="checkbox-row">
                    <input type="checkbox" name="activ" id="activ" <?php echo !empty($values['activ']) ? 'checked' : ''; ?>>
                    <label for="activ" style="margin:0;">Produs activ</label>
                </div>
            </div>
        </div>

        <div class="bottom-actions">
            <a href="magazie.php" class="new-btn secondary">Anulează</a>
            <button type="submit" class="new-btn success">Salvează produsul</button>
        </div>
    </form>
</div>

<?php include 'layout-end.php'; ?>
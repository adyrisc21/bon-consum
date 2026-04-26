<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$pageTitle = 'Bon nou';
$activePage = 'bon-nou';

$stmt = $pdo->query("SELECT MAX(CAST(numar_document AS UNSIGNED)) AS max_nr FROM bonuri");
$row = $stmt->fetch();
$nextNumber = ($row && $row['max_nr']) ? ((int)$row['max_nr'] + 1) : 1;

$zi = date('d');
$luna = date('m');
$an = date('Y');

$stmtProduse = $pdo->query("
    SELECT id, denumire, cod_produs, cod_gestiune, um, pret_unitar, stoc_curent
    FROM produse
    WHERE activ = 1
    ORDER BY denumire ASC
");
$produse = $stmtProduse->fetchAll();

$primitori = [
    'EM1 Enescu Alina Ancuta',
    'EM1 Popovici Mihai',
    'EM1 Sava Dumitru Iulian',
    'EM2 Badea Marius',
    'EM2 Dumitrescu Daniela Nicoleta',
    'EM2 Trinca Bogdan',
    'M1 Enciulescu Sorina Petruta Andreea',
    'M1 Simion Valentin Mihai',
    'M1 Stefan Roxana Nicoleta'
];

function emptyRows($count = 6) {
    $rows = [];
    for ($i = 1; $i <= $count; $i++) {
        $rows[] = [
            'nr_crt' => $i,
            'denumire' => '',
            'cantitate_necesara' => '',
            'cod' => '',
            'um' => '',
            'cantitate_eliberata' => '',
            'pret_unitar' => '',
            'valoarea' => ''
        ];
    }
    return $rows;
}
$rows = emptyRows(6);

include 'layout-start.php';
?>

<style>
.bon-page-card{
    background:#fff;
    border-radius:20px;
    padding:20px;
    box-shadow:0 10px 30px rgba(15,23,42,.08);
}
.bon-toolbar{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.bon-btn{
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
.bon-btn.secondary{background:#475569}
.bon-btn.success{background:#15803d}

.bon-print-wrap{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:16px;
    padding:12px;
    overflow:auto;
}

.print-sheet{
    width:1420px;
    margin:0 auto;
    background:#fff;
}

.copy{
    margin:0;
}
.separator{
    border-top:2px dashed #666;
    margin:10px 0;
}

.bottom-actions{
    display:flex;
    justify-content:flex-end;
    margin-top:14px;
}

table.bon{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}
.bon td,.bon th{
    border:1px solid #000;
    padding:0;
    vertical-align:middle;
    overflow:visible;
}
.bon .p4{padding:4px}
.bon .center{text-align:center}
.bon .left{text-align:left}
.bon .bold{font-weight:700}
.bon .title{
    font-size:18px;
    font-weight:700;
    text-align:center;
    letter-spacing:0;
}
.bon .small{font-size:11px; line-height:1.1}
.bon .normal{font-size:12px}
.bon .h22{height:18px}
.bon .h26{height:20px}
.bon .h30{height:22px}
.bon .h34{height:24px}
.bon .h28{height:20px}

.bon .input,
.bon .select-input{
    width:100%;
    border:none;
    outline:none;
    background:transparent;
    font-family:Arial, sans-serif;
    font-size:12px;
    text-align:center;
    height:22px;
    padding:2px 4px;
    color:#c40000;
    font-weight:700;
}
.bon .input.lefttxt{
    text-align:left;
}
.bon .input.smallin{
    font-size:11px;
    height:18px;
}
.bon .display{
    padding:2px 4px;
    min-height:18px;
    line-height:16px;
    font-size:12px;
    color:#c40000;
    font-weight:700;
    text-align:center;
}
.bon .display.lefttxt{text-align:left}

.left-select{
    text-align:left !important;
    padding-left:8px !important;
}

.unitatea-cell{
    white-space:nowrap;
}
.unitatea-label{
    display:inline-block;
    vertical-align:middle;
    margin-right:6px;
}
.unitatea-input{
    display:inline-block;
    width:auto !important;
    min-width:170px;
    vertical-align:middle;
}
.unitatea-display{
    display:inline-block !important;
    min-width:170px;

    vertical-align:middle;
    padding:0 !important;
    line-height:normal !important;
}

.autocomplete-wrap{
    position:relative;
    width:100%;
}
.autocomplete-panel{
    position:absolute;
    top:100%;
    left:0;
    right:0;
    background:#fff;
    border:1px solid #bbb;
    border-radius:8px;
    box-shadow:0 10px 25px rgba(0,0,0,.18);
    z-index:99999;
    display:none;
    margin-top:4px;
    overflow:hidden;
}
.autocomplete-panel.show{
    display:block;
}
.autocomplete-header{
    padding:7px 10px;
    font-size:11px;
    color:#666;
    background:#f6f6f6;
    border-bottom:1px solid #e5e5e5;
}
.autocomplete-list{
    max-height:220px;
    overflow-y:auto;
    overflow-x:hidden;
}
.autocomplete-item{
    padding:8px 10px;
    cursor:pointer;
    border-bottom:1px solid #eee;
    background:#fff;
}
.autocomplete-item:last-child{
    border-bottom:none;
}
.autocomplete-item:hover,
.autocomplete-item.active{
    background:#eef5ff;
}
.autocomplete-title{
    font-size:12px;
    font-weight:700;
    color:#111;
    line-height:1.2;
    margin-bottom:2px;
}
.autocomplete-meta{
    font-size:10px;
    color:#555;
    line-height:1.2;
}
.autocomplete-empty{
    padding:10px;
    font-size:12px;
    color:#666;
    background:#fff;
}

@media print{
    @page{
        size:A4 landscape;
        margin:0;
    }

    body{
        background:#fff !important;
    }

    .topbar,
    .sidebar,
    .page-header,
    .bon-toolbar,
    .bottom-actions{
        display:none !important;
    }

    .main{
        margin:0 !important;
        padding:0 !important;
    }

    .bon-page-card{
        box-shadow:none !important;
        border-radius:0 !important;
        padding:0 !important;
        background:#fff !important;
    }

    .bon-print-wrap{
        border:none !important;
        background:#fff !important;
        padding:0 !important;
        overflow:visible !important;
    }

    .print-sheet{
        width:297mm;
        height:210mm;
        margin:0;
        padding:5mm 7mm 5mm 7mm;
        background:#fff;
        overflow:hidden;
    }

    .copy{
        margin:0;
    }

    .separator{
        margin:3mm 0 3mm 0;
        border-top:2px dashed #666;
    }

    table.bon{
        width:100%;
        table-layout:fixed;
    }

    .bon td,.bon th{
        font-size:10px;
    }

    .bon .title{
        font-size:13px;
        letter-spacing:0;
    }

    .bon .small{
        font-size:9px;
        line-height:1.05;
    }

    .bon .normal{
        font-size:10px;
    }

    .bon .input,
    .bon .select-input,
    .bon .display{
        font-size:10px;
        color:#000 !important;
    }

    .bon .h22 td{height:15px}
    .bon .h26 td{height:17px}
    .bon .h28 td{height:18px}
    .bon .h30 td{height:19px}
    .bon .h34 td{height:21px}

    .denumire-input::placeholder{
        color:transparent !important;
    }

    .autocomplete-panel{
        display:none !important;
    }

    .unitatea-input{
        min-width:150px;
    }
    .unitatea-display{
        min-width:150px;
    }
}
</style>

<div class="bon-page-card">
    <div class="page-header">
        <div>
            <h1 class="page-title">Bon nou</h1>
            <p class="page-subtitle">Completezi bonul, îl salvezi și apoi îl poți printa.</p>
        </div>
        <div class="bon-toolbar"></div>
    </div>

    <div class="bon-print-wrap">
        <div class="print-sheet">
            <form action="salveaza-bon.php" method="post" id="bonForm">
                <div class="copy">
                    <?php renderEditableBon($rows, $nextNumber, $zi, $luna, $an, $primitori); ?>
                </div>

                <div class="separator"></div>

                <div class="copy">
                    <?php renderMirrorBon($rows); ?>
                </div>

                <div class="bottom-actions">
                    <button type="submit" form="bonForm" class="bon-btn success">Salvează</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const PRODUSE = <?php echo json_encode($produse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

function syncField(source){
    const targetName = source.getAttribute('data-mirror');
    if(!targetName) return;
    const targets = document.querySelectorAll('[data-copy="'+targetName+'"]');
    targets.forEach(t=>{
        t.textContent = source.value;
    });
}

function attachSync(){
    document.querySelectorAll('[data-mirror]').forEach(el=>{
        el.addEventListener('input', function(){
            syncField(this);
            calculateRowFromElement(this);
        });
        el.addEventListener('change', function(){
            syncField(this);
            calculateRowFromElement(this);
        });
        syncField(el);
    });
}

function calculateRow(index){
    const cant = parseFloat(document.querySelector('[name="cantitate_eliberata['+index+']"]')?.value || 0);
    const pret = parseFloat(document.querySelector('[name="pret_unitar['+index+']"]')?.value || 0);
    const total = (!isNaN(cant) && !isNaN(pret)) ? (cant * pret).toFixed(2) : '';
    const valInput = document.querySelector('[name="valoarea['+index+']"]');
    if(valInput){
        valInput.value = total === '0.00' ? '' : total;
        syncField(valInput);
    }
}

function calculateRowFromElement(el){
    const idx = el.getAttribute('data-row');
    if(idx !== null){
        calculateRow(idx);
    }
}

function normalizeText(str) {
    return (str || '').toString().trim().toLowerCase();
}

function scoreProdus(prod, query) {
    const q = normalizeText(query);
    const den = normalizeText(prod.denumire);
    const cod = normalizeText(prod.cod_produs);
    const gest = normalizeText(prod.cod_gestiune);

    if (!q) return -1;
    if (den === q || cod === q || gest === q) return 100;
    if (den.startsWith(q) || cod.startsWith(q) || gest.startsWith(q)) return 80;
    if (den.includes(q)) return 60;
    if (cod.includes(q)) return 50;
    if (gest.includes(q)) return 40;
    return -1;
}

function filterProduse(query) {
    const q = normalizeText(query);
    if (q.length < 2) return [];

    return PRODUSE
        .map(p => ({...p, _score: scoreProdus(p, q)}))
        .filter(p => p._score >= 0)
        .sort((a, b) => {
            if (b._score !== a._score) return b._score - a._score;
            return normalizeText(a.denumire).localeCompare(normalizeText(b.denumire));
        })
        .slice(0, 8);
}

function applyProdusToRow(input, produs) {
    if (!produs) return;

    const tr = input.closest('tr');
    const produsIdInput = tr.querySelector('.produs-id');
    const codInput = tr.querySelector('input[name^="cod["]');
    const umInput = tr.querySelector('input[name^="um["]');
    const pretInput = tr.querySelector('input[name^="pret_unitar["]');
    const codGestiuneInput = document.querySelector('input[name="cod_gestiune"]');

    input.value = produs.denumire || '';
    if (produsIdInput) produsIdInput.value = produs.id || '';
    if (codInput) codInput.value = produs.cod_produs || '';
    if (umInput) umInput.value = produs.um || '';
    if (pretInput) pretInput.value = produs.pret_unitar || '';
    if (codGestiuneInput && produs.cod_gestiune) codGestiuneInput.value = produs.cod_gestiune || '';

    syncField(input);
    if (codInput) syncField(codInput);
    if (umInput) syncField(umInput);
    if (pretInput) syncField(pretInput);
    if (codGestiuneInput) syncField(codGestiuneInput);

    const rowIndex = input.getAttribute('data-row');
    if (rowIndex !== null) calculateRow(rowIndex);
}

function setupAutocomplete() {
    document.querySelectorAll('.denumire-input').forEach(input => {
        const wrap = input.closest('.autocomplete-wrap');
        const panel = wrap.querySelector('.autocomplete-panel');
        const header = wrap.querySelector('.autocomplete-header');
        const list = wrap.querySelector('.autocomplete-list');

        let currentItems = [];
        let activeIndex = -1;

        function closePanel() {
            panel.classList.remove('show');
            list.innerHTML = '';
            activeIndex = -1;
            currentItems = [];
        }

        function openPanel() {
            panel.classList.add('show');
        }

        function renderList(items, query) {
            currentItems = items;
            activeIndex = -1;
            list.innerHTML = '';

            if (normalizeText(query).length < 2) {
                closePanel();
                return;
            }

            header.textContent = `Rezultate: ${items.length}`;

            if (!items.length) {
                list.innerHTML = '<div class="autocomplete-empty">Nu există produse potrivite.</div>';
                openPanel();
                return;
            }

            items.forEach((item, index) => {
                const option = document.createElement('div');
                option.className = 'autocomplete-item';
                option.innerHTML = `
                    <div class="autocomplete-title">${escapeHtml(item.denumire || '')}</div>
                    <div class="autocomplete-meta">
                        Cod: ${escapeHtml(item.cod_produs || '')}
                        · Gestiune: ${escapeHtml(item.cod_gestiune || '')}
                        · U/M: ${escapeHtml(item.um || '')}
                        · Stoc: ${escapeHtml(String(item.stoc_curent ?? ''))}
                        · Preț: ${escapeHtml(String(item.pret_unitar ?? ''))}
                    </div>
                `;

                option.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    applyProdusToRow(input, item);
                    closePanel();
                });

                option.addEventListener('mouseenter', function() {
                    setActive(index);
                });

                list.appendChild(option);
            });

            openPanel();
        }

        function setActive(index) {
            const options = list.querySelectorAll('.autocomplete-item');
            options.forEach(opt => opt.classList.remove('active'));

            if (index >= 0 && options[index]) {
                options[index].classList.add('active');
                activeIndex = index;
                options[index].scrollIntoView({block: 'nearest'});
            }
        }

        function chooseActive() {
            if (activeIndex >= 0 && currentItems[activeIndex]) {
                applyProdusToRow(input, currentItems[activeIndex]);
                closePanel();
                return true;
            }
            return false;
        }

        input.addEventListener('input', function() {
            const items = filterProduse(this.value);
            renderList(items, this.value);
        });

        input.addEventListener('focus', function() {
            const items = filterProduse(this.value);
            if (items.length) renderList(items, this.value);
        });

        input.addEventListener('keydown', function(e) {
            if (!panel.classList.contains('show')) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentItems.length) {
                    activeIndex = Math.min(activeIndex + 1, currentItems.length - 1);
                    setActive(activeIndex);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentItems.length) {
                    activeIndex = Math.max(activeIndex - 1, 0);
                    setActive(activeIndex);
                }
            } else if (e.key === 'Enter') {
                if (chooseActive()) {
                    e.preventDefault();
                }
            } else if (e.key === 'Escape') {
                closePanel();
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(() => {
                const exact = PRODUSE.find(p =>
                    normalizeText(p.denumire) === normalizeText(input.value) ||
                    normalizeText(p.cod_produs) === normalizeText(input.value)
                );
                if (exact) {
                    applyProdusToRow(input, exact);
                }
                closePanel();
            }, 120);
        });

        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) {
                closePanel();
            }
        });
    });
}

function escapeHtml(str) {
    return String(str)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

attachSync();
document.querySelectorAll('[data-row]').forEach(el=>{
    el.addEventListener('input', ()=>calculateRow(el.getAttribute('data-row')));
});
for(let i=0;i<6;i++){ calculateRow(i); }

setupAutocomplete();
</script>

<?php
include 'layout-end.php';

function renderEditableBon($rows, $nextNumber, $zi, $luna, $an, $primitori){ ?>
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
        <td colspan="5" class="p4 normal left unitatea-cell">
            <span class="bold unitatea-label">UNITATEA:</span>
            <input class="input lefttxt unitatea-input" type="text" name="unitatea" value="METROREX SA" data-mirror="unitatea">
        </td>
        <td colspan="4" class="p4 normal left">
            Produs, lucrare (comandă)
            <input class="input lefttxt" type="text" name="produs_lucrare" value="" data-mirror="produs_lucrare">
        </td>
        <td colspan="4" rowspan="3" class="title">BON DE CONSUM</td>
    </tr>

    <tr class="h26">
        <td colspan="2" rowspan="2" class="small center p4">Număr<br>document</td>
        <td colspan="3" class="small center p4">Data</td>
        <td rowspan="2" class="small center p4">Predător</td>
        <td rowspan="2" class="small center p4">Primitor</td>
        <td rowspan="2" colspan="2" class="small center p4"><u>Cod gestiune</u></td>
    </tr>

    <tr class="h26">
        <td class="small center p4">Ziua</td>
        <td class="small center p4">Luna</td>
        <td class="small center p4">Anul</td>
    </tr>

    <tr class="h30">
        <td colspan="2"><input class="input" type="text" name="numar_document" value="<?php echo htmlspecialchars($nextNumber); ?>" data-mirror="numar_document"></td>
        <td><input class="input" type="text" name="zi" value="<?php echo htmlspecialchars($zi); ?>" data-mirror="zi"></td>
        <td><input class="input" type="text" name="luna" value="<?php echo htmlspecialchars($luna); ?>" data-mirror="luna"></td>
        <td><input class="input" type="text" name="an" value="<?php echo htmlspecialchars($an); ?>" data-mirror="an"></td>
        <td><input class="input" type="text" name="predator" value="5510066" data-mirror="predator"></td>
        <td>
            <input class="input lefttxt" type="text" name="primitor" value="" readonly>
        </td>
        <td colspan="2"><input class="input" type="text" name="cod_gestiune" data-mirror="cod_gestiune"></td>
        <td class="small center p4">Cantitatea<br>eliberată</td>
        <td class="small center p4">Prețul<br>unitar</td>
        <td colspan="2" class="small center p4">Valoarea</td>
    </tr>

    <tr class="h30">
        <td class="small center p4">Crt.</td>
        <td colspan="4" class="small center p4">Denumirea materialelor (inclusiv<br>sort, marca, profil, dimensiune)</td>
        <td class="small center p4">Cantitatea necesara</td>
        <td colspan="2" class="small center p4">Cod</td>
        <td class="small center p4">U/M</td>
        <td class="small center p4"></td>
        <td class="small center p4"></td>
        <td colspan="2" class="small center p4"></td>
    </tr>

    <?php foreach($rows as $i => $r): ?>
    <tr class="h24">
        <td class="center">
            <input class="input smallin" type="text" name="nr_crt[<?php echo $i; ?>]" value="<?php echo $i+1; ?>" data-mirror="nr_crt_<?php echo $i; ?>">
        </td>
        <td colspan="4">
            <input type="hidden" name="produs_id[<?php echo $i; ?>]" class="produs-id">
            <div class="autocomplete-wrap">
                <input class="input lefttxt smallin denumire-input" type="text" name="denumire[<?php echo $i; ?>]" data-mirror="denumire_<?php echo $i; ?>" data-row="<?php echo $i; ?>" autocomplete="off" placeholder="Scrie minim 2 litere">
                <div class="autocomplete-panel">
                    <div class="autocomplete-header">Scrie minim 2 litere pentru căutare</div>
                    <div class="autocomplete-list"></div>
                </div>
            </div>
        </td>
        <td>
            <input class="input smallin" type="text" name="cantitate_necesara[<?php echo $i; ?>]" data-mirror="cantitate_necesara_<?php echo $i; ?>" data-row="<?php echo $i; ?>">
        </td>
        <td colspan="2">
            <input class="input smallin" type="text" name="cod[<?php echo $i; ?>]" data-mirror="cod_<?php echo $i; ?>" data-row="<?php echo $i; ?>">
        </td>
        <td>
            <input class="input smallin" type="text" name="um[<?php echo $i; ?>]" data-mirror="um_<?php echo $i; ?>" data-row="<?php echo $i; ?>">
        </td>
        <td>
            <input class="input smallin" type="text" name="cantitate_eliberata[<?php echo $i; ?>]" data-mirror="cantitate_eliberata_<?php echo $i; ?>" data-row="<?php echo $i; ?>">
        </td>
        <td>
            <input class="input smallin" type="text" name="pret_unitar[<?php echo $i; ?>]" data-mirror="pret_unitar_<?php echo $i; ?>" data-row="<?php echo $i; ?>">
        </td>
        <td colspan="2">
            <input class="input smallin" type="text" name="valoarea[<?php echo $i; ?>]" data-mirror="valoarea_<?php echo $i; ?>" data-row="<?php echo $i; ?>" readonly>
        </td>
    </tr>
    <?php endforeach; ?>

    <tr class="h28">
        <td colspan="3" class="normal left p4">Data si semnatura</td>
        <td colspan="3" class="normal center p4"><u>Sef compartiment</u></td>
        <td colspan="3" class="normal left p4">Gestionar</td>
        <td colspan="4" class="normal left p4">Primitor</td>
    </tr>
    <tr class="h28">
        <td colspan="3"></td>
        <td colspan="3">
            <input class="input" type="text" name="sef_compartiment" data-mirror="sef_compartiment">
        </td>
        <td colspan="3">
            <input class="input lefttxt" type="text" name="gestionar" value="Ms Stoian Claudiu" data-mirror="gestionar">
        </td>
        <td colspan="4">
            <select class="select-input left-select" name="primitor_semnatura" data-mirror="primitor_semnatura">
                <option value="">Selectează</option>
                <?php foreach ($primitori as $p): ?>
                    <option value="<?php echo htmlspecialchars($p); ?>">
                        <?php echo htmlspecialchars($p); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>
<?php }

function renderMirrorBon($rows){ ?>
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
        <td colspan="5" class="p4 normal left unitatea-cell">
            <span class="bold unitatea-label">UNITATEA:</span>
            <span class="display lefttxt unitatea-display" data-copy="unitatea"></span>
        </td>
        <td colspan="4" class="p4 normal left">Produs, lucrare (comandă)<div class="display lefttxt" data-copy="produs_lucrare"></div></td>
        <td colspan="4" rowspan="3" class="title">BON DE CONSUM</td>
    </tr>

    <tr class="h26">
        <td colspan="2" rowspan="2" class="small center p4">Număr<br>document</td>
        <td colspan="3" class="small center p4">Data</td>
        <td rowspan="2" class="small center p4">Predător</td>
        <td rowspan="2" class="small center p4">Primitor</td>
        <td rowspan="2" colspan="2" class="small center p4"><u>Cod gestiune</u></td>
    </tr>

    <tr class="h26">
        <td class="small center p4">Ziua</td>
        <td class="small center p4">Luna</td>
        <td class="small center p4">Anul</td>
    </tr>

    <tr class="h30">
        <td colspan="2"><div class="display" data-copy="numar_document"></div></td>
        <td><div class="display" data-copy="zi"></div></td>
        <td><div class="display" data-copy="luna"></div></td>
        <td><div class="display" data-copy="an"></div></td>
        <td><div class="display" data-copy="predator"></div></td>
        <td><div class="display lefttxt"></div></td>
        <td colspan="2"><div class="display" data-copy="cod_gestiune"></div></td>
        <td class="small center p4">Cantitatea<br>eliberată</td>
        <td class="small center p4">Prețul<br>unitar</td>
        <td colspan="2" class="small center p4">Valoarea</td>
    </tr>

    <tr class="h30">
        <td class="small center p4">Crt.</td>
        <td colspan="4" class="small center p4">Denumirea materialelor (inclusiv<br>sort, marca, profil, dimensiune)</td>
        <td class="small center p4">Cantitatea necesara</td>
        <td colspan="2" class="small center p4">Cod</td>
        <td class="small center p4">U/M</td>
        <td class="small center p4"></td>
        <td class="small center p4"></td>
        <td colspan="2" class="small center p4"></td>
    </tr>

    <?php foreach($rows as $i => $r): ?>
    <tr class="h24">
        <td><div class="display" data-copy="nr_crt_<?php echo $i; ?>"></div></td>
        <td colspan="4"><div class="display lefttxt" data-copy="denumire_<?php echo $i; ?>"></div></td>
        <td><div class="display" data-copy="cantitate_necesara_<?php echo $i; ?>"></div></td>
        <td colspan="2"><div class="display" data-copy="cod_<?php echo $i; ?>"></div></td>
        <td><div class="display" data-copy="um_<?php echo $i; ?>"></div></td>
        <td><div class="display" data-copy="cantitate_eliberata_<?php echo $i; ?>"></div></td>
        <td><div class="display" data-copy="pret_unitar_<?php echo $i; ?>"></div></td>
        <td colspan="2"><div class="display" data-copy="valoarea_<?php echo $i; ?>"></div></td>
    </tr>
    <?php endforeach; ?>

    <tr class="h28">
        <td colspan="3" class="normal left p4">Data si semnatura</td>
        <td colspan="3" class="normal center p4"><u>Sef compartiment</u></td>
        <td colspan="3" class="normal left p4">Gestionar</td>
        <td colspan="4" class="normal left p4">Primitor</td>
    </tr>
    <tr class="h28">
        <td colspan="3"></td>
        <td colspan="3"><div class="display" data-copy="sef_compartiment"></div></td>
        <td colspan="3"><div class="display lefttxt" data-copy="gestionar"></div></td>
        <td colspan="4"><div class="display lefttxt" data-copy="primitor_semnatura"></div></td>
    </tr>
</table>
<?php } ?>
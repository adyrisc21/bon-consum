<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'auth.php';
require_once 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$autoprint = isset($_GET['autoprint']) && $_GET['autoprint'] == '1';

if ($id <= 0) {
    die('ID invalid.');
}

$stmt = $pdo->prepare("SELECT * FROM bonuri WHERE id = ?");
$stmt->execute([$id]);
$bon = $stmt->fetch();

if (!$bon) {
    die('Bonul nu există.');
}

$stmtPoz = $pdo->prepare("
    SELECT *
    FROM bonuri_pozitii
    WHERE bon_id = ?
    ORDER BY nr_crt ASC, id ASC
");
$stmtPoz->execute([$id]);
$pozitii = $stmtPoz->fetchAll();

$maxRows = 6;
for ($i = count($pozitii); $i < $maxRows; $i++) {
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

function renderBonPrint($bon, $pozitii){ ?>
<table class="bon">
    <colgroup>
        <col style="width:3%">
        <col style="width:6%">
        <col style="width:6%">
        <col style="width:6%">
        <col style="width:9%">
        <col style="width:12%">
        <col style="width:8.5%">
        <col style="width:11%">
        <col style="width:7%">
        <col style="width:8.5%">
        <col style="width:6.5%">
        <col style="width:8.5%">
        <col style="width:8%">
    </colgroup>

    <tr class="r1">
        <td colspan="5" class="p4 left">
            <span class="label-top">UNITATEA:</span>
            <span class="unitatea-val"><?php echo htmlspecialchars($bon['unitatea']); ?></span>
        </td>
        <td colspan="4" class="p4 left">
            <span class="label-top">Produs, lucrare (comandă)</span>
            <div class="value left value-plain"><?php echo htmlspecialchars($bon['produs_lucrare']); ?></div>
        </td>
        <td colspan="4" rowspan="3" class="title">BON DE CONSUM</td>
    </tr>

    <tr class="r2">
        <td colspan="2" rowspan="2" class="small center p4">Număr<br>document</td>
        <td colspan="3" class="small center p4">Data</td>
        <td rowspan="2" class="small center p4">Predător</td>
        <td rowspan="2" class="small center p4">Primitor</td>
        <td rowspan="2" colspan="2" class="small center p4"><u>Cod gestiune</u></td>
    </tr>

    <tr class="r3">
        <td class="small center p4">Ziua</td>
        <td class="small center p4">Luna</td>
        <td class="small center p4">Anul</td>
    </tr>

    <tr class="r4">
        <td colspan="2" class="value center"><?php echo htmlspecialchars($bon['numar_document']); ?></td>
        <td class="value center"><?php echo htmlspecialchars($bon['zi']); ?></td>
        <td class="value center"><?php echo htmlspecialchars($bon['luna']); ?></td>
        <td class="value center"><?php echo htmlspecialchars($bon['an']); ?></td>
        <td class="value center"><?php echo htmlspecialchars($bon['predator']); ?></td>
        <td class="value left"></td>
        <td colspan="2" class="value center"><?php echo htmlspecialchars($bon['cod_gestiune']); ?></td>
        <td class="small center p4">Cantitatea<br>eliberată</td>
        <td class="small center p4">Prețul<br>unitar</td>
        <td colspan="2" class="small center p4">Valoarea</td>
    </tr>

    <tr class="r5">
        <td class="small center p4">Crt.</td>
        <td colspan="4" class="small center p4">Denumirea materialelor (inclusiv<br>sort, marca, profil, dimensiune)</td>
        <td class="small center p4">Cantitatea necesara</td>
        <td colspan="2" class="small center p4">Cod</td>
        <td class="small center p4">U/M</td>
        <td class="small center p4"></td>
        <td class="small center p4"></td>
        <td colspan="2" class="small center p4"></td>
    </tr>

    <?php foreach($pozitii as $p): ?>
    <tr class="rline">
        <td class="value center"><?php echo htmlspecialchars($p['nr_crt']); ?></td>
        <td colspan="4" class="value left value-denumire"><?php echo htmlspecialchars($p['denumire']); ?></td>
        <td class="value center"><?php echo htmlspecialchars($p['cantitate_necesara']); ?></td>
        <td colspan="2" class="value center"><?php echo htmlspecialchars($p['cod']); ?></td>
        <td class="value center"><?php echo htmlspecialchars($p['um']); ?></td>
        <td class="value center value-strong"><?php echo htmlspecialchars($p['cantitate_eliberata']); ?></td>
        <td class="value center value-strong"><?php echo htmlspecialchars($p['pret_unitar']); ?></td>
        <td colspan="2" class="value center value-strong"><?php echo htmlspecialchars($p['valoarea']); ?></td>
    </tr>
    <?php endforeach; ?>

    <tr class="rsign1">
        <td colspan="3" class="normal left p4">Data si semnatura</td>
        <td colspan="3" class="normal center p4"><u>Sef compartiment</u></td>
        <td colspan="3" class="normal left p4">Gestionar</td>
        <td colspan="4" class="normal left p4">Primitor</td>
    </tr>

    <tr class="rsign2">
        <td colspan="3"></td>
        <td colspan="3" class="value center value-plain"><?php echo htmlspecialchars($bon['sef_compartiment']); ?></td>
        <td colspan="3" class="value left"><?php echo htmlspecialchars($bon['gestionar']); ?></td>
        <td colspan="4" class="value left"><?php echo htmlspecialchars($bon['primitor_semnatura']); ?></td>
    </tr>
</table>
<?php } ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print bon</title>
    <style>
        *{box-sizing:border-box}
        html, body{
            margin:0;
            padding:0;
            background:#e9e9e9;
            font-family:Arial, sans-serif;
            color:#000;
        }

        .screen-bar{
            max-width:1400px;
            margin:14px auto 0;
            display:flex;
            gap:10px;
            flex-wrap:wrap;
            padding:0 10px;
        }

        .btn{
            background:#111;
            color:#fff;
            text-decoration:none;
            border:none;
            padding:10px 16px;
            border-radius:6px;
            cursor:pointer;
            font-size:14px;
        }

        .btn.secondary{background:#666}

        .page{
            width:297mm;
            height:210mm;
            margin:10px auto;
            background:#fff;
            box-shadow:0 4px 18px rgba(0,0,0,.12);
            position:relative;
            overflow:hidden;
            padding:5mm 6mm 6mm 6mm;
        }

        .bon-wrap{
            width:100%;
            height:97mm; /* FIX exact jumătate */
            position:relative;
        }

        .bon-wrap.top{
    margin-bottom:10mm; /* mărește spațiul între bonuri */
    transform: translateY(-4mm); /* ridică bonul de sus */
}
        .bon-wrap.bottom{
             margin-top:0;
         }
        .cut-line{
    position:absolute;
    left:6mm;
    right:6mm;
    top:105mm; /* EXACT jumătatea paginii A4 landscape */
    border-top:2px dashed #666;
    z-index:5;
}

        .cut-line::before{
            content:"✂";
            position:absolute;
            left:-3mm;
            top:-10px;
            font-size:14px;
            background:#fff;
            padding-right:3px;
        }

        table.bon{
            width:100%;
            height:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }

        .bon td,.bon th{
            border:1px solid #000;
            padding:0;
            vertical-align:middle;
        }

        .p4{padding:3px}
        .center{text-align:center}
        .left{text-align:left}
        .bold{font-weight:700}

        .title{
            font-size:6mm;
            font-weight:700;
            text-align:center;
            white-space:nowrap;
            letter-spacing:0;
        }

        .label-top{
            font-size:2.3mm;
            font-weight:700;
        }

        .unitatea-val{
            font-size:3.1mm;
            font-weight:700;
        }

        .small{
            font-size:2.2mm;
            line-height:1.05;
        }

        .normal{
            font-size:3.2mm;
            line-height:1.05;
        }

        .value{
            padding:1px 3px;
            font-size:3.6mm;
            font-weight:700;
            text-align:center;
            line-height:1.05;
        }

        .value.left{
            text-align:left;
        }

        .value-denumire{
            font-size:3.8mm;
        }

        .value-strong{
            font-size:3.7mm;
            font-weight:700;
        }

        .value-plain{
            font-size:3.2mm;
        }

        .r1 td{height:10mm}
        .r2 td{height:7mm}
        .r3 td{height:7mm}
        .r4 td{height:8mm}
        .r5 td{height:7mm}
        .rline td{height:7mm}
        .rsign1 td{height:7mm}
        .rsign2 td{height:7mm}

        @media print{
            @page{
                size:A4 landscape;
                margin:0;
            }

            html, body{
                width:297mm;
                height:210mm;
                background:#fff;
                margin:0;
                padding:0;
                overflow:hidden;
            }

            .screen-bar{
                display:none !important;
            }

            .page{
                width:297mm;
                height:210mm;
                margin:0;
                padding:5mm 6mm 6mm 6mm;
                box-shadow:none;
            }
        }
    </style>
</head>
<body>

<?php if (!$autoprint): ?>
<div class="screen-bar">
    <a href="bon-nou.php" class="btn secondary">Bon nou</a>
    <a href="arhiva-bonuri.php" class="btn secondary">Arhivă</a>
    <button onclick="window.print()" class="btn">Printează</button>
</div>
<?php endif; ?>

<div class="page">
    <div class="bon-wrap top">
        <?php renderBonPrint($bon, $pozitii); ?>
    </div>

    <div class="cut-line"></div>

    <div class="bon-wrap bottom">
        <?php renderBonPrint($bon, $pozitii); ?>
    </div>
</div>

<?php if ($autoprint): ?>
<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        window.print();
    }, 250);

    window.addEventListener('afterprint', function () {
        try { window.close(); } catch (e) {}
    });
});
</script>
<?php endif; ?>

</body>
</html>
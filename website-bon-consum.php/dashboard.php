<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family:Arial,sans-serif;
            background:#f1f5f9;
            color:#0f172a;
        }

        .topbar{
            height:64px;
            background:#0f172a;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 18px;
            position:fixed;
            top:0;
            left:0;
            right:0;
            z-index:1000;
            box-shadow:0 4px 18px rgba(0,0,0,.16);
        }
        .topbar-left{
            display:flex;
            align-items:center;
            gap:14px;
        }
        .menu-toggle{
            width:40px;
            height:40px;
            border:none;
            border-radius:10px;
            background:#1e293b;
            color:#fff;
            cursor:pointer;
            font-size:18px;
        }
        .brand{
            font-size:18px;
            font-weight:700;
        }
        .topbar-right{
            display:flex;
            align-items:center;
            gap:12px;
        }
        .user-pill{
            background:#1e293b;
            padding:8px 12px;
            border-radius:999px;
            font-size:13px;
        }
        .logout-btn{
            color:#fff;
            text-decoration:none;
            background:#dc2626;
            padding:10px 14px;
            border-radius:10px;
            font-size:13px;
            font-weight:700;
        }

        .sidebar{
            position:fixed;
            top:64px;
            left:0;
            width:250px;
            bottom:0;
            background:#111827;
            padding:20px 14px;
            overflow:auto;
        }
        .sidebar-title{
            color:#94a3b8;
            font-size:12px;
            text-transform:uppercase;
            margin-bottom:14px;
            font-weight:700;
            letter-spacing:.5px;
        }
        .nav-item{
            display:block;
            color:#e5e7eb;
            text-decoration:none;
            padding:12px 14px;
            border-radius:12px;
            margin-bottom:8px;
            background:transparent;
            transition:.2s;
            font-size:14px;
            font-weight:600;
        }
        .nav-item:hover{
            background:#1f2937;
        }
        .nav-item.danger{
            background:#7f1d1d;
        }
        .nav-item.danger:hover{
            background:#991b1b;
        }

        .main{
            margin-left:250px;
            padding:88px 24px 24px;
        }

        .hero{
            background:linear-gradient(135deg,#ffffff,#e2e8f0);
            border-radius:20px;
            padding:26px;
            box-shadow:0 10px 30px rgba(15,23,42,.08);
            margin-bottom:22px;
        }
        .hero h1{
            margin:0 0 8px;
            font-size:28px;
        }
        .hero p{
            margin:0;
            color:#475569;
            font-size:15px;
        }

        .grid{
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:18px;
        }

        .card{
            background:#fff;
            border-radius:18px;
            padding:22px;
            box-shadow:0 10px 30px rgba(15,23,42,.08);
            min-height:150px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
        }

        .card h3{
            margin:0 0 10px;
            font-size:19px;
        }

        .card p{
            margin:0 0 18px;
            color:#64748b;
            font-size:14px;
            line-height:1.45;
        }

        .card a{
            display:inline-block;
            text-decoration:none;
            background:#0f172a;
            color:#fff;
            padding:11px 14px;
            border-radius:12px;
            font-size:14px;
            font-weight:700;
        }

        @media (max-width: 980px){
            .grid{
                grid-template-columns:repeat(2,minmax(0,1fr));
            }
        }

        @media (max-width: 760px){
            .sidebar{
                transform:translateX(-100%);
                transition:.25s;
                z-index:999;
            }
            body.sidebar-open .sidebar{
                transform:translateX(0);
            }
            .main{
                margin-left:0;
                padding:88px 14px 18px;
            }
            .grid{
                grid-template-columns:1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="hero">
            <h1>Dashboard</h1>
            <p>Alege rapid ce vrei să faci: bon nou, arhivă, produse, magazie sau intrări de stoc.</p>
        </div>

        <div class="grid">
            <div class="card">
                <div>
                    <h3>Bon nou</h3>
                    <p>Creezi un bon nou de consum și îl trimiți direct la print.</p>
                </div>
                <a href="bon-nou.php">Deschide</a>
            </div>

            <div class="card">
                <div>
                    <h3>Arhivă bonuri</h3>
                    <p>Vezi bonurile salvate, le cauți, le editezi și le printezi din nou.</p>
                </div>
                <a href="arhiva-bonuri.php">Deschide</a>
            </div>

            <div class="card">
                <div>
                    <h3>Magazie</h3>
                    <p>Vezi produsele din stoc, pozițiile și accesezi fișa de magazie.</p>
                </div>
                <a href="magazie.php">Deschide</a>
            </div>

            <div class="card">
                <div>
                    <h3>Produs nou</h3>
                    <p>Adaugi produse noi în nomenclatorul de magazie.</p>
                </div>
                <a href="produs-nou.php">Deschide</a>
            </div>

            <div class="card">
                <div>
                    <h3>Intrare stoc</h3>
                    <p>Înregistrezi intrări de marfă și actualizezi stocul curent.</p>
                </div>
                <a href="intrare-stoc.php">Deschide</a>
            </div>

            <div class="card">
                <div>
                    <h3>Logout</h3>
                    <p>Ieși din sistemul de gestiune în siguranță.</p>
                </div>
                <a href="logout.php">Ieșire</a>
            </div>
        </div>
    </div>
</body>
</html>
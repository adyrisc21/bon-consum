<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($pageTitle)) {
    $pageTitle = 'Panou';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
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
            z-index:999;
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
            font-size:14px;
            font-weight:600;
        }
        .nav-item:hover{
            background:#1f2937;
        }
        .nav-item.active{
            background:#2563eb;
            color:#fff;
        }
        .nav-item.danger{
            background:#7f1d1d;
        }
        .nav-item.danger:hover{
            background:#991b1b;
        }
        .main{
            margin-left:250px;
            padding:88px 20px 20px;
        }
        @media (max-width: 760px){
            .sidebar{
                transform:translateX(-100%);
                transition:.25s;
            }
            body.sidebar-open .sidebar{
                transform:translateX(0);
            }
            .main{
                margin-left:0;
                padding:88px 12px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
            <div class="brand">Gestiune Bon Consum</div>
        </div>
        <div class="topbar-right">
            <span class="user-pill"><?php echo htmlspecialchars($_SESSION['username'] ?? 'admin'); ?></span>
            <a class="logout-btn" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-title">Meniu</div>
        <a href="dashboard.php" class="nav-item <?php echo (($activePage ?? '') === 'dashboard' ? 'active' : ''); ?>">Dashboard</a>
        <a href="bon-nou.php" class="nav-item <?php echo (($activePage ?? '') === 'bon-nou' ? 'active' : ''); ?>">Bon nou</a>
        <a href="arhiva-bonuri.php" class="nav-item <?php echo (($activePage ?? '') === 'arhiva' ? 'active' : ''); ?>">Arhivă bonuri</a>
        <a href="magazie.php" class="nav-item <?php echo (($activePage ?? '') === 'magazie' ? 'active' : ''); ?>">Magazie</a>
        <a href="produs-nou.php" class="nav-item <?php echo (($activePage ?? '') === 'produs-nou' ? 'active' : ''); ?>">Produs nou</a>
        <a href="intrare-stoc.php" class="nav-item <?php echo (($activePage ?? '') === 'intrare-stoc' ? 'active' : ''); ?>">Intrare stoc</a>
        <a href="logout.php" class="nav-item danger">Logout</a>
    </div>

    <div class="main">
<?php
session_start();

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$eroare = '';

$utilizator_corect = 'admin';
$parola_corecta = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $utilizator_corect && $password === $parola_corecta) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['username'] = $username;

        header('Location: dashboard.php');
        exit;
    } else {
        $eroare = 'Utilizator sau parolă incorectă.';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare</title>
    <style>
        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            font-family:Arial,sans-serif;
            background:linear-gradient(135deg,#0f172a,#1e293b,#334155);
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .login-box{
            width:100%;
            max-width:430px;
            background:rgba(255,255,255,0.96);
            border-radius:18px;
            padding:32px 28px;
            box-shadow:0 20px 60px rgba(0,0,0,.28);
        }
        .title{
            margin:0 0 8px;
            font-size:28px;
            color:#0f172a;
            text-align:center;
        }
        .sub{
            margin:0 0 24px;
            text-align:center;
            color:#64748b;
            font-size:14px;
        }
        .group{
            margin-bottom:16px;
        }
        label{
            display:block;
            margin-bottom:7px;
            font-size:13px;
            font-weight:700;
            color:#0f172a;
        }
        input{
            width:100%;
            height:46px;
            border:1px solid #cbd5e1;
            border-radius:10px;
            padding:0 14px;
            font-size:14px;
            outline:none;
        }
        input:focus{
            border-color:#2563eb;
            box-shadow:0 0 0 3px rgba(37,99,235,.12);
        }
        .btn{
            width:100%;
            height:48px;
            border:none;
            border-radius:10px;
            background:#0f172a;
            color:#fff;
            font-size:15px;
            font-weight:700;
            cursor:pointer;
            margin-top:8px;
        }
        .btn:hover{
            background:#1e293b;
        }
        .error{
            margin-bottom:16px;
            padding:12px 14px;
            border-radius:10px;
            background:#fee2e2;
            color:#991b1b;
            font-size:14px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1 class="title">Autentificare</h1>
        <p class="sub">Sistem bon consum și magazie</p>

        <?php if ($eroare): ?>
            <div class="error"><?php echo htmlspecialchars($eroare); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="group">
                <label>Utilizator</label>
                <input type="text" name="username" required>
            </div>

            <div class="group">
                <label>Parolă</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn" type="submit">Intră în sistem</button>
        </form>
    </div>
</body>
</html>
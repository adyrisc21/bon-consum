<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
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
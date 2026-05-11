<?php
$current_page = basename($_SERVER['PHP_SELF']);
$crud_pages   = ['coches.php', 'coche_nuevo.php', 'coche_editar.php'];
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">AB</div>
        <div>
            <div class="logo-title">Automóviles</div>
            <div class="logo-sub">de Barcelona</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#9632;</span> Dashboard
        </a>
        <a href="coches.php" class="nav-item <?= in_array($current_page, $crud_pages) ? 'active' : '' ?>">
            <span class="nav-icon">&#9670;</span> Vehículos
        </a>
        <div class="nav-separator"></div>
        <a href="admin_backup.php" class="nav-item <?= $current_page === 'admin_backup.php' ? 'active' : '' ?>">
            <span class="nav-icon">&#128190;</span> Backups
        </a>
        <a href="index.php" class="nav-item">
            <span class="nav-icon">&#8592;</span> Ver web
        </a>
    </nav>
    <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(mb_substr($_SESSION['usuario_nombre'], 0, 1)) ?></div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
            <div class="user-email"><?= htmlspecialchars($_SESSION['usuario_email']) ?></div>
        </div>
        <a href="logout.php" class="btn-logout" title="Cerrar sesión">&#9211;</a>
    </div>
</aside>

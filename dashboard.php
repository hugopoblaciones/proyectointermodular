<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$stats = ['total' => 0, 'disponibles' => 0, 'no_disponibles' => 0, 'precio_medio' => '—'];
$row = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(disponible),0) as disponibles, AVG(precio) as precio_medio FROM coches")->fetch_assoc();
if ($row) {
    $stats['total']         = (int)$row['total'];
    $stats['disponibles']   = (int)$row['disponibles'];
    $stats['no_disponibles']= $stats['total'] - $stats['disponibles'];
    $stats['precio_medio']  = $row['precio_medio'] ? '€' . number_format((float)$row['precio_medio'], 0, ',', '.') : '—';
}

$recientes = [];
$res = $conn->query("SELECT marca, modelo, precio, disponible FROM coches ORDER BY created_at DESC LIMIT 5");
while ($r = $res->fetch_assoc()) {
    $recientes[] = $r;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Automóviles de Barcelona</title>
    <link rel="stylesheet" href="panel.css?v=<?= filemtime('panel.css') ?>">
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <span class="topbar-title">Dashboard</span>
        </div>
        <div class="content">
            <div class="page-header">
                <div>
                    <div class="page-title">Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></div>
                    <div class="page-subtitle">Panel de gestión — Automóviles de Barcelona</div>
                </div>
                <a href="coches.php" class="btn btn-primary">+ Gestionar vehículos</a>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Vehículos totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#27ae60"><?= $stats['disponibles'] ?></div>
                    <div class="stat-label">Disponibles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color:#f39c12"><?= $stats['no_disponibles'] ?></div>
                    <div class="stat-label">No disponibles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="font-size:1.3rem;line-height:2.2"><?= $stats['precio_medio'] ?></div>
                    <div class="stat-label">Precio medio</div>
                </div>
            </div>

            <?php if (!empty($recientes)): ?>
            <div class="card">
                <div class="card-title">Últimos vehículos añadidos</div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Precio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recientes as $c): ?>
                            <tr>
                                <td style="color:#fff;font-weight:500"><?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?></td>
                                <td style="color:#e94560;font-weight:600">€<?= number_format((float)$c['precio'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($c['disponible']): ?>
                                        <span class="badge badge-success">Disponible</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">No disponible</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="empty-state">
                    <p>Todavía no hay vehículos en el sistema.</p>
                    <a href="coche_nuevo.php" class="btn btn-primary">Añadir el primero</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>

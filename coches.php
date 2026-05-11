<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM coches WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vehículo eliminado correctamente.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'No se pudo eliminar el vehículo.'];
        }
        $stmt->close();
    }
    header("Location: coches.php");
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$coches = [];
$res = $conn->query("SELECT * FROM coches ORDER BY created_at DESC");
while ($row = $res->fetch_assoc()) {
    $coches[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehículos — Automóviles de Barcelona</title>
    <link rel="stylesheet" href="panel.css?v=<?= filemtime('panel.css') ?>">
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <span class="topbar-title">Gestión de Vehículos</span>
        </div>
        <div class="content">
            <div class="page-header">
                <div>
                    <div class="page-title">Vehículos</div>
                    <div class="page-subtitle">
                        <?= count($coches) ?> vehículo<?= count($coches) !== 1 ? 's' : '' ?> en el sistema
                    </div>
                </div>
                <a href="coche_nuevo.php" class="btn btn-primary">+ Añadir vehículo</a>
            </div>

            <?php if ($flash): ?>
            <div class="alert <?= htmlspecialchars($flash['type']) ?>">
                <?= htmlspecialchars($flash['message']) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <?php if (empty($coches)): ?>
                <div class="empty-state">
                    <p>No hay vehículos registrados todavía.</p>
                    <a href="coche_nuevo.php" class="btn btn-primary">Añadir el primero</a>
                </div>
                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Marca / Modelo</th>
                                <th>Año</th>
                                <th>Precio</th>
                                <th>Kilómetros</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($coches as $c): ?>
                        <tr>
                            <td>
                                <?php if ($c['imagen_url']): ?>
                                <img src="<?= htmlspecialchars($c['imagen_url']) ?>"
                                     alt="<?= htmlspecialchars($c['marca']) ?>"
                                     class="car-img"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="car-img-placeholder" style="display:none">Sin foto</div>
                                <?php else: ?>
                                <div class="car-img-placeholder">Sin foto</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color:#fff"><?= htmlspecialchars($c['marca']) ?></strong><br>
                                <span style="font-size:0.8rem;color:#8899aa"><?= htmlspecialchars($c['modelo']) ?></span>
                            </td>
                            <td><?= htmlspecialchars((string)$c['anio']) ?></td>
                            <td><strong style="color:#e94560">€<?= number_format((float)$c['precio'], 0, ',', '.') ?></strong></td>
                            <td><?= $c['km'] > 0 ? number_format((int)$c['km'], 0, ',', '.') . ' km' : '0 km' ?></td>
                            <td>
                                <?php if ($c['disponible']): ?>
                                    <span class="badge badge-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">No disponible</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap">
                                    <a href="coche_editar.php?id=<?= $c['id'] ?>" class="btn btn-edit btn-sm">Editar</a>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="confirmarEliminar(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['marca'] . ' ' . $c['modelo'])) ?>')">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Modal confirmación eliminar -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>&#9888; Eliminar vehículo</h3>
        <p>¿Estás seguro de que quieres eliminar <strong id="deleteCarName"></strong>?<br>Esta acción no se puede deshacer.</p>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteCarId">
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Sí, eliminar</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmarEliminar(id, nombre) {
    document.getElementById('deleteCarId').value = id;
    document.getElementById('deleteCarName').textContent = nombre;
    document.getElementById('deleteModal').classList.add('open');
}
function cerrarModal() {
    document.getElementById('deleteModal').classList.remove('open');
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>
</body>
</html>

<?php
session_start();
require_once 'db.php';

// ── Acceso admin por contraseña propia ───────────────────────────────────────
define('ADMIN_PASS_HASH', password_hash('admin', PASSWORD_DEFAULT));

$adminError = '';

if (isset($_POST['admin_logout'])) {
    unset($_SESSION['admin_auth']);
    header('Location: admin_backup.php');
    exit;
}

if (isset($_POST['admin_password'])) {
    if (password_verify($_POST['admin_password'], ADMIN_PASS_HASH)) {
        $_SESSION['admin_auth'] = true;
        header('Location: admin_backup.php');
        exit;
    } else {
        $adminError = 'Contraseña incorrecta.';
    }
}

if (empty($_SESSION['admin_auth'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Acceso restringido</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 20px;
        padding: 48px 40px;
        width: 100%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    }
    .icon { font-size: 2.8rem; margin-bottom: 16px; }
    h1 { color: #fff; font-size: 1.35rem; font-weight: 700; margin-bottom: 6px; }
    .subtitle { color: #8899aa; font-size: 0.85rem; margin-bottom: 32px; }
    .error {
        background: rgba(233,69,96,0.15);
        border: 1px solid rgba(233,69,96,0.4);
        color: #ff8fa0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.85rem;
        margin-bottom: 18px;
    }
    input[type="password"] {
        width: 100%;
        padding: 13px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.15);
        background: rgba(255,255,255,0.07);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        margin-bottom: 16px;
        outline: none;
        transition: border-color 0.2s;
    }
    input[type="password"]:focus { border-color: #e94560; }
    button {
        width: 100%;
        padding: 13px;
        border-radius: 10px;
        border: none;
        background: linear-gradient(135deg, #e94560, #ff6b6b);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(233,69,96,0.4);
        transition: all 0.2s;
    }
    button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(233,69,96,0.55); }
    .back { display: block; margin-top: 20px; color: #8899aa; font-size: 0.82rem; text-decoration: none; }
    .back:hover { color: #e94560; }
    </style>
    </head>
    <body>
    <div class="card">
        <div class="icon">&#128274;</div>
        <h1>Panel de Administración</h1>
        <p class="subtitle">Acceso restringido — introduce la contraseña de administrador</p>
        <?php if ($adminError): ?>
        <div class="error"><?= htmlspecialchars($adminError) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="admin_password" placeholder="Contraseña" autofocus>
            <button type="submit">Entrar</button>
        </form>
        <a href="index.php" class="back">&#8592; Volver al inicio</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ── Configuración ────────────────────────────────────────────────────────────
define('BACKUP_DIR',  __DIR__ . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR);
define('MYSQLDUMP',   'C:/xampp/mysql/bin/mysqldump.exe');
define('MYSQL_CLI',   'C:/xampp/mysql/bin/mysql.exe');
define('DB_NAME',     'auto');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_HOST',     'localhost');
define('RETENTION',   7); // días que se conservan los backups

// ── Descarga de archivo (antes de cualquier HTML) ────────────────────────────
if (($_GET['action'] ?? '') === 'download') {
    $bname = preg_replace('/[^0-9_]/', '', $_GET['backup'] ?? '');
    $type  = in_array($_GET['type'] ?? '', ['sql', 'zip']) ? $_GET['type'] : '';
    if ($bname && $type) {
        $prefix = $type === 'sql' ? 'db_' : 'web_';
        $file   = BACKUP_DIR . $bname . DIRECTORY_SEPARATOR . $prefix . $bname . '.' . $type;
        if (file_exists($file)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }
    header('Location: admin_backup.php?msg=nodl');
    exit;
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function fmtBytes(int $b): string {
    if ($b <= 0)       return '—';
    if ($b < 1024)     return $b . ' B';
    if ($b < 1048576)  return round($b / 1024, 1) . ' KB';
    return round($b / 1048576, 2) . ' MB';
}

function addDirToZip(ZipArchive $zip, string $root): void {
    $backupReal = str_replace('/', DIRECTORY_SEPARATOR, realpath(BACKUP_DIR) ?: BACKUP_DIR);
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $real = $file->getRealPath();
        if (strpos($real, $backupReal) === 0) continue; // excluir carpeta backups
        $relative = ltrim(substr($real, strlen($root)), DIRECTORY_SEPARATOR . '/');
        $zip->addFile($real, $relative);
    }
}

function getBackups(): array {
    $list = [];
    if (!is_dir(BACKUP_DIR)) return $list;
    foreach (glob(BACKUP_DIR . '*', GLOB_ONLYDIR) as $dir) {
        $name = basename($dir);
        if (!preg_match('/^\d{8}_\d{4}$/', $name)) continue;
        $sql = $dir . DIRECTORY_SEPARATOR . 'db_'  . $name . '.sql';
        $zip = $dir . DIRECTORY_SEPARATOR . 'web_' . $name . '.zip';
        $dt  = DateTime::createFromFormat('Ymd_Hi', $name);
        $list[] = [
            'name'     => $name,
            'date'     => $dt ? $dt->format('d/m/Y H:i') : $name,
            'sql'      => file_exists($sql),
            'zip'      => file_exists($zip),
            'sql_size' => file_exists($sql) ? filesize($sql) : 0,
            'zip_size' => file_exists($zip) ? filesize($zip) : 0,
        ];
    }
    usort($list, fn($a, $b) => strcmp($b['name'], $a['name']));
    return $list;
}

function purgeOldBackups(): void {
    if (!is_dir(BACKUP_DIR)) return;
    $limit = time() - (RETENTION * 86400);
    foreach (glob(BACKUP_DIR . '*', GLOB_ONLYDIR) as $dir) {
        if (filemtime($dir) < $limit) {
            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $f) unlink($f);
            rmdir($dir);
        }
    }
}

// ── Acciones POST ────────────────────────────────────────────────────────────
$msg  = '';
$type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREAR BACKUP
    if ($action === 'create') {
        if (!is_dir(BACKUP_DIR)) mkdir(BACKUP_DIR, 0755, true);
        $ts     = date('Ymd_Hi');
        $folder = BACKUP_DIR . $ts . DIRECTORY_SEPARATOR;
        if (!is_dir($folder)) mkdir($folder, 0755, true);

        // Dump BD — captura stdout de mysqldump
        $sqlFile = $folder . 'db_' . $ts . '.sql';
        $passArg = DB_PASS !== '' ? ('-p' . DB_PASS) : '';
        $cmd     = '"' . MYSQLDUMP . '" -h ' . DB_HOST . ' -u ' . DB_USER . ' ' . $passArg . ' ' . DB_NAME;
        exec($cmd, $sqlLines, $exitCode);

        if ($exitCode !== 0 || empty($sqlLines)) {
            foreach (glob($folder . '*') as $f) unlink($f);
            rmdir($folder);
            $msg  = 'Error al generar el dump de la base de datos. Comprueba que mysqldump está en la ruta configurada.';
            $type = 'error';
        } else {
            file_put_contents($sqlFile, implode("\n", $sqlLines));

            // ZIP del proyecto
            $zipFile = $folder . 'web_' . $ts . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                addDirToZip($zip, __DIR__);
                $zip->close();
                purgeOldBackups();
                $msg  = "Backup <strong>$ts</strong> creado correctamente.";
                $type = 'success';
            } else {
                $msg  = 'SQL generado, pero falló la creación del ZIP del proyecto.';
                $type = 'warning';
            }
        }
    }

    // ELIMINAR BACKUP
    elseif ($action === 'delete') {
        $name = preg_replace('/[^0-9_]/', '', $_POST['backup_name'] ?? '');
        if (preg_match('/^\d{8}_\d{4}$/', $name)) {
            $dir = BACKUP_DIR . $name . DIRECTORY_SEPARATOR;
            if (is_dir($dir)) {
                foreach (glob($dir . '*') as $f) unlink($f);
                rmdir($dir);
                $msg  = "Backup <strong>$name</strong> eliminado.";
                $type = 'success';
            } else {
                $msg  = 'Backup no encontrado.';
                $type = 'error';
            }
        }
    }

    // RESTAURAR BD + ARCHIVOS
    elseif ($action === 'restore') {
        $name    = preg_replace('/[^0-9_]/', '', $_POST['backup_name'] ?? '');
        $confirm = $_POST['confirm'] ?? '';
        if ($confirm !== 'RESTAURAR') {
            $msg  = 'Escribe exactamente <strong>RESTAURAR</strong> para confirmar.';
            $type = 'error';
        } elseif (preg_match('/^\d{8}_\d{4}$/', $name)) {
            $backupFolder = BACKUP_DIR . $name . DIRECTORY_SEPARATOR;
            $sqlFile      = $backupFolder . 'db_' . $name . '.sql';
            $zipFile      = $backupFolder . 'web_' . $name . '.zip';
            $errors       = [];

            // Restaurar BD
            if (file_exists($sqlFile)) {
                $sql     = file_get_contents($sqlFile);
                $passArg = DB_PASS !== '' ? ('-p' . DB_PASS) : '';
                $cmd     = '"' . MYSQL_CLI . '" -h ' . DB_HOST . ' -u ' . DB_USER . ' ' . $passArg . ' ' . DB_NAME;
                $desc    = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                $proc    = proc_open($cmd, $desc, $pipes);
                if (is_resource($proc)) {
                    fwrite($pipes[0], $sql);
                    fclose($pipes[0]);
                    $stderr = stream_get_contents($pipes[2]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    $code = proc_close($proc);
                    if ($code !== 0) {
                        $errors[] = 'Error BD: ' . htmlspecialchars($stderr);
                    }
                } else {
                    $errors[] = 'No se pudo ejecutar mysql. Revisa la ruta de MYSQL_CLI.';
                }
            } else {
                $errors[] = 'Archivo SQL del backup no encontrado.';
            }

            // Restaurar archivos del proyecto
            if (file_exists($zipFile)) {
                $zip = new ZipArchive();
                if ($zip->open($zipFile) === true) {
                    $zip->extractTo(__DIR__);
                    $zip->close();
                } else {
                    $errors[] = 'No se pudo abrir el ZIP del proyecto.';
                }
            }

            if (empty($errors)) {
                $msg  = "Backup <strong>$name</strong> restaurado correctamente (BD + archivos).";
                $type = 'success';
            } else {
                $msg  = implode('<br>', $errors);
                $type = 'error';
            }
        }
    }
}

$backups = getBackups();
$canExec = function_exists('exec');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Backups — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    min-height: 100vh;
    color: #e0e0e0;
}

/* ── Barra superior ── */
.topbar {
    background: rgba(15, 52, 96, 0.8);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(233, 69, 96, 0.3);
    padding: 0 32px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
}

.topbar-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
}

.topbar-brand span { color: #e94560; }

.topbar-nav { display: flex; align-items: center; gap: 16px; }

.btn-nav {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-nav.secondary {
    background: transparent;
    border: 1px solid rgba(255,255,255,0.3);
    color: #ccc;
}

.btn-nav.secondary:hover { border-color: #e94560; color: #e94560; }

.btn-nav.danger {
    background: linear-gradient(135deg, #e94560, #ff6b6b);
    color: #fff;
    border: none;
    box-shadow: 0 3px 10px rgba(233,69,96,0.35);
}

.btn-nav.danger:hover { transform: translateY(-1px); box-shadow: 0 5px 14px rgba(233,69,96,0.5); }

/* ── Contenido ── */
.content { max-width: 1100px; margin: 0 auto; padding: 36px 20px 60px; }

.page-title {
    font-size: 1.7rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
}

.page-subtitle { font-size: 0.9rem; color: #8899aa; margin-bottom: 32px; }

/* ── Alertas ── */
.alert {
    padding: 14px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 0.9rem;
    border-left: 4px solid;
}

.alert.success { background: rgba(39,174,96,0.15); border-color: #27ae60; color: #6ddb93; }
.alert.error   { background: rgba(233,69,96,0.15);  border-color: #e94560; color: #ff8fa0; }
.alert.warning { background: rgba(243,156,18,0.15);  border-color: #f39c12; color: #f7c45e; }
.alert.info    { background: rgba(52,152,219,0.15);  border-color: #3498db; color: #7ec8f5; }

/* ── Tarjetas de estadísticas ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.stat-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 14px;
    padding: 20px;
    text-align: center;
}

.stat-card .number { font-size: 2rem; font-weight: 700; color: #e94560; }
.stat-card .label  { font-size: 0.8rem; color: #8899aa; margin-top: 4px; }

/* ── Panel de acción ── */
.action-panel {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 32px;
}

.action-panel h2 {
    font-size: 1.05rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.action-panel h2::before { content: ''; display: block; width: 4px; height: 18px; background: #e94560; border-radius: 2px; }

.btn-create {
    background: linear-gradient(135deg, #e94560, #ff6b6b);
    color: #fff;
    border: none;
    padding: 14px 32px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(233,69,96,0.4);
}

.btn-create:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(233,69,96,0.55); }
.btn-create:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.create-info { font-size: 0.82rem; color: #8899aa; margin-top: 10px; }

/* ── Tabla de backups ── */
.table-wrap { overflow-x: auto; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}

thead th {
    background: rgba(15,52,96,0.7);
    padding: 14px 16px;
    text-align: left;
    color: #8899aa;
    font-weight: 600;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

tbody tr {
    border-top: 1px solid rgba(255,255,255,0.06);
    transition: background 0.15s;
}

tbody tr:hover { background: rgba(255,255,255,0.04); }

td { padding: 14px 16px; vertical-align: middle; color: #cdd; }

td.date-col { color: #fff; font-weight: 500; }
td.name-col { font-size: 0.78rem; color: #8899aa; font-family: monospace; }

.size-badge {
    display: inline-block;
    background: rgba(255,255,255,0.08);
    border-radius: 6px;
    padding: 2px 8px;
    font-size: 0.78rem;
    color: #aab;
}

.missing { color: #e94560; font-size: 0.78rem; }

/* ── Botones de acción en tabla ── */
.actions { display: flex; gap: 6px; flex-wrap: wrap; }

.btn-sm {
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 0.77rem;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-block;
    transition: all 0.15s;
    white-space: nowrap;
}

.btn-dl-sql  { background: rgba(52,152,219,0.2); color: #7ec8f5; border: 1px solid rgba(52,152,219,0.4); }
.btn-dl-zip  { background: rgba(39,174,96,0.2);  color: #6ddb93; border: 1px solid rgba(39,174,96,0.4); }
.btn-restore { background: rgba(243,156,18,0.2); color: #f7c45e; border: 1px solid rgba(243,156,18,0.4); }
.btn-del     { background: rgba(233,69,96,0.2);  color: #ff8fa0; border: 1px solid rgba(233,69,96,0.4); }

.btn-sm:hover { filter: brightness(1.25); transform: translateY(-1px); }

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #8899aa;
}

.empty-state .icon { font-size: 3rem; margin-bottom: 12px; }

/* ── Modal de restaurar ── */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 999;
    align-items: center;
    justify-content: center;
}

.modal-overlay.open { display: flex; }

.modal {
    background: #16213e;
    border: 1px solid rgba(233,69,96,0.4);
    border-radius: 16px;
    padding: 32px;
    max-width: 440px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.modal h3 { color: #e94560; font-size: 1.1rem; margin-bottom: 12px; }
.modal p  { color: #bbb; font-size: 0.88rem; margin-bottom: 20px; line-height: 1.5; }

.modal input[type="text"] {
    width: 100%;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid rgba(233,69,96,0.5);
    background: rgba(255,255,255,0.05);
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    margin-bottom: 16px;
    outline: none;
}

.modal input[type="text"]:focus { border-color: #e94560; }

.modal-backup-name {
    background: rgba(243,156,18,0.1);
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 0.82rem;
    color: #f7c45e;
    font-family: monospace;
    margin-bottom: 18px;
    word-break: break-all;
}

.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }

.btn-modal-cancel {
    padding: 9px 20px;
    border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.2);
    background: transparent;
    color: #aaa;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
}

.btn-modal-confirm {
    padding: 9px 20px;
    border-radius: 8px;
    border: none;
    background: linear-gradient(135deg, #e94560, #ff6b6b);
    color: #fff;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
}

/* ── Spinner ── */
.spinner { display: none; width: 18px; height: 18px; border: 3px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.8s linear infinite; vertical-align: middle; margin-left: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</head>
<body>

<!-- Barra superior -->
<div class="topbar">
    <div class="topbar-brand">
        &#9632; Admin <span>Backups</span>
    </div>
    <div class="topbar-nav">
        <span style="font-size:0.85rem; color:#8899aa;">&#128272; Admin</span>
        <a href="index.php" class="btn-nav secondary">&#8592; Inicio</a>
        <a href="dashboard.php" class="btn-nav secondary">Dashboard</a>
        <form method="POST" style="display:inline">
            <input type="hidden" name="admin_logout" value="1">
            <button type="submit" class="btn-nav danger" style="font-family:'Poppins',sans-serif;font-size:0.85rem;cursor:pointer;">Cerrar sesión admin</button>
        </form>
    </div>
</div>

<!-- Contenido principal -->
<div class="content">
    <div class="page-title">Gestión de Copias de Seguridad</div>
    <div class="page-subtitle">Crea, descarga, restaura y elimina backups de la base de datos y del proyecto.</div>

    <!-- Alerta exec desactivado -->
    <?php if (!$canExec): ?>
    <div class="alert warning">
        <strong>Atención:</strong> La función <code>exec()</code> está desactivada en este PHP. Los backups no podrán generarse hasta que la habilites en <code>php.ini</code> (elimina <code>exec</code> de <code>disable_functions</code>).
    </div>
    <?php endif; ?>

    <!-- Mensaje de resultado -->
    <?php if ($msg): ?>
    <div class="alert <?= htmlspecialchars($type) ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <?php
        $total     = count($backups);
        $totalSize = array_sum(array_column($backups, 'sql_size')) + array_sum(array_column($backups, 'zip_size'));
        $newest    = $total ? $backups[0]['date'] : '—';
    ?>
    <div class="stats-row">
        <div class="stat-card">
            <div class="number"><?= $total ?></div>
            <div class="label">Backups disponibles</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= fmtBytes($totalSize) ?></div>
            <div class="label">Espacio total</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= RETENTION ?>d</div>
            <div class="label">Retención automática</div>
        </div>
        <div class="stat-card">
            <div class="number" style="font-size:1.1rem; line-height:2"><?= $newest ?></div>
            <div class="label">Último backup</div>
        </div>
    </div>

    <!-- Panel crear backup -->
    <div class="action-panel">
        <h2>Crear nuevo backup</h2>
        <form method="POST" id="createForm">
            <input type="hidden" name="action" value="create">
            <button type="submit" class="btn-create" id="btnCreate" <?= !$canExec ? 'disabled' : '' ?>>
                &#43; Crear Backup Ahora
                <span class="spinner" id="spinner"></span>
            </button>
        </form>
        <p class="create-info">
            Genera un dump SQL de la base de datos <strong>auto</strong> y un ZIP completo del proyecto.<br>
            Los backups con más de <?= RETENTION ?> días se eliminan automáticamente.
        </p>
    </div>

    <!-- Tabla de backups -->
    <div class="action-panel">
        <h2>Backups existentes (<?= $total ?>)</h2>

        <?php if (empty($backups)): ?>
        <div class="empty-state">
            <div class="icon">&#128190;</div>
            <p>No hay backups todavía. Crea el primero con el botón de arriba.</p>
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Identificador</th>
                        <th>Base de datos</th>
                        <th>Proyecto (ZIP)</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($backups as $b): ?>
                <tr>
                    <td class="date-col"><?= htmlspecialchars($b['date']) ?></td>
                    <td class="name-col"><?= htmlspecialchars($b['name']) ?></td>
                    <td>
                        <?php if ($b['sql']): ?>
                            <span class="size-badge"><?= fmtBytes($b['sql_size']) ?></span>
                        <?php else: ?>
                            <span class="missing">No disponible</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($b['zip']): ?>
                            <span class="size-badge"><?= fmtBytes($b['zip_size']) ?></span>
                        <?php else: ?>
                            <span class="missing">No disponible</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="size-badge"><?= fmtBytes($b['sql_size'] + $b['zip_size']) ?></span></td>
                    <td>
                        <div class="actions">
                            <?php if ($b['sql']): ?>
                            <a href="?action=download&backup=<?= urlencode($b['name']) ?>&type=sql" class="btn-sm btn-dl-sql">&#8659; SQL</a>
                            <?php endif; ?>
                            <?php if ($b['zip']): ?>
                            <a href="?action=download&backup=<?= urlencode($b['name']) ?>&type=zip" class="btn-sm btn-dl-zip">&#8659; ZIP</a>
                            <?php endif; ?>
                            <?php if ($b['sql']): ?>
                            <button type="button" class="btn-sm btn-restore" onclick="openRestore('<?= htmlspecialchars($b['name']) ?>', '<?= htmlspecialchars($b['date']) ?>')">&#9850; Restaurar BD</button>
                            <?php endif; ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('¿Eliminar el backup <?= htmlspecialchars($b['name']) ?>? Esta acción no se puede deshacer.')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="backup_name" value="<?= htmlspecialchars($b['name']) ?>">
                                <button type="submit" class="btn-sm btn-del">&#10005; Eliminar</button>
                            </form>
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

<!-- Modal restaurar -->
<div class="modal-overlay" id="restoreModal">
    <div class="modal">
        <h3>&#9888; Restaurar base de datos</h3>
        <p>Esta acción reemplazará todos los datos actuales de la BD con los del backup seleccionado. Esta operación <strong>no se puede deshacer</strong>.</p>
        <div class="modal-backup-name" id="modalBackupLabel"></div>
        <form method="POST" id="restoreForm">
            <input type="hidden" name="action" value="restore">
            <input type="hidden" name="backup_name" id="restoreBackupName">
            <input type="text" name="confirm" placeholder='Escribe "RESTAURAR" para confirmar' autocomplete="off">
            <div class="modal-actions">
                <button type="button" class="btn-modal-cancel" onclick="closeRestore()">Cancelar</button>
                <button type="submit" class="btn-modal-confirm">Restaurar ahora</button>
            </div>
        </form>
    </div>
</div>

<script>
// Spinner al crear backup
document.getElementById('createForm').addEventListener('submit', function () {
    const btn = document.getElementById('btnCreate');
    btn.disabled = true;
    btn.textContent = 'Generando backup...';
    document.getElementById('spinner').style.display = 'inline-block';
});

// Modal de restaurar
function openRestore(name, date) {
    document.getElementById('restoreBackupName').value = name;
    document.getElementById('modalBackupLabel').textContent = name + '  (' + date + ')';
    document.getElementById('restoreModal').classList.add('open');
}

function closeRestore() {
    document.getElementById('restoreModal').classList.remove('open');
    document.getElementById('restoreForm').reset();
}

document.getElementById('restoreModal').addEventListener('click', function(e) {
    if (e.target === this) closeRestore();
});
</script>
</body>
</html>

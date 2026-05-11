<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$errors = [];
$values = [
    'marca'       => '',
    'modelo'      => '',
    'anio'        => date('Y'),
    'precio'      => '',
    'km'          => '',
    'descripcion' => '',
    'imagen_url'  => '',
    'disponible'  => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca       = trim($_POST['marca']       ?? '');
    $modelo      = trim($_POST['modelo']      ?? '');
    $anio        = (int)($_POST['anio']       ?? 0);
    $precio      = trim($_POST['precio']      ?? '');
    $km          = trim($_POST['km']          ?? '0');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen_url  = trim($_POST['imagen_url']  ?? '');
    $disponible  = isset($_POST['disponible']) ? 1 : 0;

    if (empty($marca))
        $errors['marca'] = 'La marca es obligatoria.';
    elseif (strlen($marca) > 100)
        $errors['marca'] = 'Máximo 100 caracteres.';

    if (empty($modelo))
        $errors['modelo'] = 'El modelo es obligatorio.';
    elseif (strlen($modelo) > 100)
        $errors['modelo'] = 'Máximo 100 caracteres.';

    if ($anio < 1900 || $anio > (int)date('Y') + 1)
        $errors['anio'] = 'Introduce un año válido (1900–' . ((int)date('Y') + 1) . ').';

    if ($precio === '')
        $errors['precio'] = 'El precio es obligatorio.';
    elseif (!is_numeric($precio) || (float)$precio < 0)
        $errors['precio'] = 'El precio debe ser un número positivo.';

    if ($km !== '' && $km !== '0' && (!is_numeric($km) || (int)$km < 0))
        $errors['km'] = 'Los kilómetros deben ser un número positivo.';

    if ($imagen_url !== '' && !filter_var($imagen_url, FILTER_VALIDATE_URL))
        $errors['imagen_url'] = 'La URL de imagen no es válida.';

    $values = compact('marca', 'modelo', 'anio', 'precio', 'km', 'descripcion', 'imagen_url', 'disponible');

    if (empty($errors)) {
        $precio_f = (float)$precio;
        $km_i     = (int)$km;
        $stmt = $conn->prepare(
            "INSERT INTO coches (marca, modelo, anio, precio, km, descripcion, imagen_url, disponible)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssidissi", $marca, $modelo, $anio, $precio_f, $km_i, $descripcion, $imagen_url, $disponible);

        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Vehículo añadido correctamente.'];
            $stmt->close();
            $conn->close();
            header("Location: coches.php");
            exit;
        } else {
            $errors['general'] = 'Error al guardar. Inténtalo de nuevo.';
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo vehículo — Automóviles de Barcelona</title>
    <link rel="stylesheet" href="panel.css?v=<?= filemtime('panel.css') ?>">
</head>
<body>
<div class="app-layout">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <div class="topbar">
            <span class="topbar-title">Añadir vehículo</span>
        </div>
        <div class="content">
            <div class="page-header">
                <div>
                    <div class="page-title">Nuevo vehículo</div>
                    <div class="page-subtitle">Rellena los datos del vehículo</div>
                </div>
                <a href="coches.php" class="btn btn-secondary">&#8592; Volver</a>
            </div>

            <?php if (isset($errors['general'])): ?>
            <div class="alert error"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-title">Datos del vehículo</div>
                <form method="POST" action="coche_nuevo.php" novalidate>
                    <div class="form-grid">

                        <div class="form-group">
                            <label for="marca">Marca *</label>
                            <input type="text" id="marca" name="marca"
                                   value="<?= htmlspecialchars($values['marca']) ?>"
                                   placeholder="Ej: BMW, Audi, Toyota..." maxlength="100">
                            <?php if (isset($errors['marca'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['marca']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="modelo">Modelo *</label>
                            <input type="text" id="modelo" name="modelo"
                                   value="<?= htmlspecialchars($values['modelo']) ?>"
                                   placeholder="Ej: Serie 3, Q5, Corolla..." maxlength="100">
                            <?php if (isset($errors['modelo'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['modelo']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="anio">Año *</label>
                            <input type="number" id="anio" name="anio"
                                   value="<?= htmlspecialchars((string)$values['anio']) ?>"
                                   min="1900" max="<?= (int)date('Y') + 1 ?>">
                            <?php if (isset($errors['anio'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['anio']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio (€) *</label>
                            <input type="number" id="precio" name="precio"
                                   value="<?= htmlspecialchars($values['precio']) ?>"
                                   placeholder="Ej: 25000" min="0" step="100">
                            <?php if (isset($errors['precio'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['precio']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="km">Kilómetros</label>
                            <input type="number" id="km" name="km"
                                   value="<?= htmlspecialchars($values['km']) ?>"
                                   placeholder="Ej: 50000" min="0">
                            <?php if (isset($errors['km'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['km']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="imagen_url">URL de imagen</label>
                            <input type="url" id="imagen_url" name="imagen_url"
                                   value="<?= htmlspecialchars($values['imagen_url']) ?>"
                                   placeholder="https://...">
                            <?php if (isset($errors['imagen_url'])): ?>
                            <span class="form-error"><?= htmlspecialchars($errors['imagen_url']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group full">
                            <label for="descripcion">Descripción</label>
                            <textarea id="descripcion" name="descripcion"
                                      placeholder="Descripción del vehículo..."><?= htmlspecialchars($values['descripcion']) ?></textarea>
                        </div>

                        <div class="form-group full">
                            <div class="form-check">
                                <input type="checkbox" id="disponible" name="disponible"
                                       <?= $values['disponible'] ? 'checked' : '' ?>>
                                <label for="disponible">Disponible para la venta</label>
                            </div>
                        </div>

                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar vehículo</button>
                        <a href="coches.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

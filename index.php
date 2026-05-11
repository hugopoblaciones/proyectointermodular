<?php
session_start();
$usuario_logueado = isset($_SESSION['usuario_id']);

$contact_errors  = [];
$contact_success = false;
$contact_values  = ['nombre' => '', 'email' => '', 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'contacto') {
    $nombre  = trim($_POST['contacto_nombre']  ?? '');
    $email   = trim($_POST['contacto_email']   ?? '');
    $mensaje = trim($_POST['contacto_mensaje'] ?? '');

    if (empty($nombre))
        $contact_errors['nombre'] = 'El nombre es obligatorio.';

    if (empty($email))
        $contact_errors['email'] = 'El email es obligatorio.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $contact_errors['email'] = 'Introduce un email válido.';

    if (empty($mensaje))
        $contact_errors['mensaje'] = 'El mensaje no puede estar vacío.';
    elseif (strlen($mensaje) < 10)
        $contact_errors['mensaje'] = 'El mensaje debe tener al menos 10 caracteres.';

    $contact_values = compact('nombre', 'email', 'mensaje');

    if (empty($contact_errors)) {
        $contact_success = true;
        $contact_values  = ['nombre' => '', 'email' => '', 'mensaje' => ''];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automóviles de Barcelona</title>
    <link rel="stylesheet" href="styles.css?v=<?= filemtime('styles.css') ?>">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-overlay">
            <div class="header-buttons">
                <?php if ($usuario_logueado): ?>
                    <span class="usuario-info"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
                    <a href="dashboard.php" class="btn-header">Panel de gestión</a>
                    <a href="logout.php" class="btn-header">Cerrar sesión</a>
                <?php else: ?>
                    <a href="registro.php" class="btn-header">Registrar</a>
                    <a href="login.php" class="btn-header">Iniciar sesión</a>
                <?php endif; ?>
            </div>
            <div class="logo-container">
                <h1 class="logo-text">Automóviles de Barcelona</h1>
            </div>
        </div>
    </header>

    <!-- Presentación -->
    <section class="presentacion">
        <div class="container">
            <h2>Bienvenido a Automóviles de Barcelona</h2>
            <p>Tu concesionario de confianza en Barcelona. Ofrecemos los mejores vehículos con la mejor atención al cliente.</p>
        </div>
    </section>

    <!-- Galería -->
    <section class="galeria">
        <div class="container">
            <h2>Nuestros Vehículos Disponibles</h2>
            <div class="coches-grid">
                <div class="coche-card">
                    <img src="https://images.unsplash.com/photo-1682845485707-f5029d736001?w=500" alt="BMW">
                    <div class="coche-info"><h3>BMW Serie 3</h3><p class="precio">€35,900</p></div>
                </div>
                <div class="coche-card">
                    <img src="https://images.unsplash.com/photo-1647708790417-ab5d00697c68?w=500" alt="Audi">
                    <div class="coche-info"><h3>Audi Q5</h3><p class="precio">€42,500</p></div>
                </div>
                <div class="coche-card">
                    <img src="https://images.unsplash.com/photo-1637005218692-a7e234ffcbf4?w=500" alt="Mercedes">
                    <div class="coche-info"><h3>Mercedes Clase C</h3><p class="precio">€38,700</p></div>
                </div>
                <div class="coche-card">
                    <img src="https://images.unsplash.com/photo-1605152277138-359efd4a6862?w=500" alt="Golf">
                    <div class="coche-info"><h3>Volkswagen Golf</h3><p class="precio">€24,900</p></div>
                </div>
                <div class="coche-card">
                    <img src="https://images.unsplash.com/photo-1728315640904-b38019d170a1?w=500" alt="Toyota">
                    <div class="coche-info"><h3>Toyota Corolla</h3><p class="precio">€28,500</p></div>
                </div>
                <div class="coche-card">
                    <img src="https://images.unsplash.com/photo-1705747401901-28363172fe7e?w=500" alt="Premium">
                    <div class="coche-info"><h3>Vehículo Premium</h3><p class="precio">€52,000</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Horario -->
    <section class="horario">
        <div class="container">
            <h2>Horario de Atención</h2>
            <div class="horario-info">
                <p><strong>Lunes a Viernes:</strong> 9:00 - 20:00</p>
                <p><strong>Sábados:</strong> 10:00 - 14:00</p>
                <p><strong>Domingos:</strong> Cerrado</p>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section class="contacto" id="contacto">
        <div class="container">
            <h2>Contacto</h2>
            <div class="contacto-grid">
                <div class="contacto-item"><div><h3>Teléfono</h3><p>+34 638 35 98 67</p></div></div>
                <div class="contacto-item"><div><h3>Dirección</h3><p>Carrer de Camil Fabra</p></div></div>
                <div class="contacto-item"><div><h3>Email</h3><p>AutomovilBarcelona@gmail.com</p></div></div>
            </div>

            <div class="contacto-form-wrap">
                <h3>Envíanos un mensaje</h3>

                <?php if ($contact_success): ?>
                <div class="contacto-alert success">
                    ¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto.
                </div>
                <?php else: ?>

                <form method="POST" action="#contacto" novalidate>
                    <input type="hidden" name="form" value="contacto">
                    <div class="contacto-form-grid">

                        <div class="contacto-form-group">
                            <label>Nombre *</label>
                            <input type="text" name="contacto_nombre"
                                   value="<?= htmlspecialchars($contact_values['nombre']) ?>"
                                   placeholder="Tu nombre completo">
                            <?php if (isset($contact_errors['nombre'])): ?>
                            <span class="contacto-error"><?= htmlspecialchars($contact_errors['nombre']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="contacto-form-group">
                            <label>Email *</label>
                            <input type="email" name="contacto_email"
                                   value="<?= htmlspecialchars($contact_values['email']) ?>"
                                   placeholder="tu@email.com">
                            <?php if (isset($contact_errors['email'])): ?>
                            <span class="contacto-error"><?= htmlspecialchars($contact_errors['email']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="contacto-form-group full">
                            <label>Mensaje *</label>
                            <textarea name="contacto_mensaje"
                                      placeholder="Escribe tu consulta aquí..."><?= htmlspecialchars($contact_values['mensaje']) ?></textarea>
                            <?php if (isset($contact_errors['mensaje'])): ?>
                            <span class="contacto-error"><?= htmlspecialchars($contact_errors['mensaje']) ?></span>
                            <?php endif; ?>
                        </div>

                    </div>
                    <button type="submit" class="btn-contacto">Enviar mensaje</button>
                </form>

                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Automóviles de Barcelona.</p>
    </footer>
</body>
</html>
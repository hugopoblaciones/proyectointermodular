<?php
session_start();
$usuario_logueado = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automóviles de Barcelona</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-overlay">
            <div class="header-buttons">
                <?php if ($usuario_logueado): ?>
                    <span class="usuario-info"><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
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
    <section class="contacto">
        <div class="container">
            <h2>Contacto</h2>
            <div class="contacto-grid">
                <div class="contacto-item"><div><h3>Teléfono</h3><p>+34 638 35 98 67</p></div></div>
                <div class="contacto-item"><div><h3>Dirección</h3><p>Carrer de Camil Fabra</p></div></div>
                <div class="contacto-item"><div><h3>Email</h3><p>AutomovilBarcelona@gmail.com</p></div></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 Automóviles de Barcelona.</p>
    </footer>
</body>
</html>
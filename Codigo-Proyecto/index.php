<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Bienvenido - Sistema de Seguridad</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: url('imagenes_camaras/medellin.jpg') no-repeat center center fixed;
      background-size: cover;
      color: white;
      min-height: 100vh;
    }

    header {
      background-color: rgba(0, 0, 0, 0.7);
      padding: 20px;
      text-align: center;
    }

    nav {
      margin-top: 10px;
    }

    nav a {
      color: white;
      text-decoration: none;
      background-color: #007BFF;
      padding: 10px 20px;
      margin: 5px;
      border-radius: 5px;
      transition: background 0.3s;
    }

    nav a:hover {
      background-color: #0056b3;
    }

    .container {
      background-color: rgba(0, 0, 0, 0.6);
      max-width: 900px;
      margin: 50px auto;
      padding: 40px;
      border-radius: 15px;
    }

    .info-section {
      margin-top: 40px;
      background-color: rgba(255, 255, 255, 0.1);
      padding: 20px;
      border-radius: 10px;
    }

    .info-section h2 {
      margin-bottom: 15px;
    }

    .info-section p, .info-section li {
      line-height: 1.6;
      font-size: 16px;
    }

    iframe {
      width: 100%;
      height: 400px;
      border: none;
      border-radius: 10px;
      margin-top: 15px;
    }

    @media (max-width: 600px) {
      nav {
        flex-direction: column;
        align-items: center;
      }
    }
  </style>
</head>
<body>

  <header>
    <h1>Sistema de Seguridad Residencial</h1>
    <nav>
      <a href="login.php">Iniciar Sesión</a>
    </nav>
  </header>

  <div class="container">
    <p style="font-size: 18px;">Bienvenido al sistema de seguridad de su unidad residencial. Desde aquí puede acceder a sus servicios o estar al tanto de las últimas novedades.</p>

    <div class="info-section">
      <h2>Avisos de la unidad residencial</h2>
      <p>- Reunión de copropietarios: sábado 25 a las 6:00 p.m. en el salón comunal.</p>
      <p>- Mantenimiento de zonas comunes: lunes 27 de mayo de 8:00 a.m. a 5:00 p.m.</p>
      <p>- Corte de agua programado: martes 28 de mayo, entre las 9:00 a.m. y 2:00 p.m.</p>
    </div>

    <div class="info-section">
      <h2>Noticias de Medellín</h2>
      <!-- Widget embed de noticias locales -->
      <iframe src="https://telemedellin.tv/medellin/" loading="lazy"></iframe>
      <p style="font-size: 12px; text-align: right;">Fuente: Telemedellin</p>
    </div>
  </div>

    <footer>
        <p>&copy; 2025 Sistema de Seguridad Residencial - Medellín</p>
    </footer>

</body>
</html>

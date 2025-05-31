<?php
include("db.php");
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

if (isset($_GET['toggle_theme'])) {
    $new_theme = ($theme === 'light') ? 'dark' : 'light';
    setcookie('theme', $new_theme, time() + (86400 * 30), "/"); 
    header("Location: ver_camaras.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ver Cámaras</title>
   <style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', sans-serif;
        background-color: <?php echo ($theme === 'dark') ? '#121212' : '#e0e0e0'; ?>;
        color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
        min-height: 100vh;
        position: relative;
        overflow-x: auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    body::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 300px;
        height: 300px;
        transform: translate(-50%, -50%);
        background-image: url('imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        opacity: <?php echo ($theme === 'dark') ? '0.5' : '0.3'; ?>;
        filter: <?php echo ($theme === 'dark') ? 'brightness(100%)' : 'brightness(100%)'; ?>;
        z-index: 0;
        pointer-events: none;
        transition: opacity 0.3s ease, filter 0.3s ease;
    }

    .container {
        position: relative;
        z-index: 1;
        background-color: <?php echo ($theme === 'dark') ? 'rgba(30, 30, 30, 0.8)' : 'rgba(255, 255, 255, 0.8)'; ?>;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
        width: 90%;
        max-width: 900px;
        margin-top: 30px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    h2 {
        color: #8e44ad;
        text-align: center;
        margin-top: 20px;
        margin-bottom: 20px;
        width: 100%;
    }

    .camera-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: calc(33% - 20px);
        margin: 10px;
        border: 1px solid <?php echo ($theme === 'dark') ? '#424242' : '#ccc'; ?>;
        padding: 15px;
        text-align: center;
        border-radius: 8px;
        background-color: <?php echo ($theme === 'dark') ? '#212121' : '#f9f9f9'; ?>;
        box-sizing: border-box;
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }

    .camera-image {
        width: 100%;
        max-width: 150px;
        height: auto;
        object-fit: cover;
        margin-bottom: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    p {
        margin: 5px 0;
        font-size: 0.9em;
        color: <?php echo ($theme === 'dark') ? '#e0e0e0' : '#555'; ?>;
    }

    strong {
        font-weight: bold;
        color: #8e44ad;
    }

    br {
        display: none;
    }

    a {
        display: inline-block;
        padding: 10px 20px;
        text-decoration: none;
        color: #7957d6;
        border: 2px solid #7957d6;
        border-radius: 8px;
        transition: color 0.3s ease, border-color 0.3s ease, background-color 0.3s ease;
        margin-top: 20px;
    }

    a:hover {
        color: #6741c9;
        border-color: #6741c9;
        background-color: rgba(136, 78, 160, 0.1);
    }

    .theme-toggle-button {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 10px 15px;
        background-color: #8e44ad;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 0.9em;
        z-index: 2;
        transition: background-color 0.3s ease;
    }

    .theme-toggle-button:hover {
        background-color: #7d3c98;
    }

    @media (max-width: 768px) {
        .camera-container {
            width: calc(50% - 20px);
        }
    }

    @media (max-width: 480px) {
        .camera-container {
            width: calc(100% - 20px);
        }
    }
</style>
</head>
<body>
    <a href="?toggle_theme=true" class="theme-toggle-button">
        <?php echo ($theme === 'dark') ? 'Modo Claro' : 'Modo Oscuro'; ?>
    </a>
    <div class="container">
        <h2> Cámaras </h2>
        <?php

    $sql = "SELECT id_camara, ubicacion, tipo_camara, id_torre FROM camara";
    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        while ($camara = $resultado->fetch_assoc()) {
            echo '<div class="camera-container">';
            echo '<img src="imagenes_camaras/camara1.jpg" alt="Cámara ' . $camara['id_camara'] . '" class="camera-image">';
            echo '<p><strong>ID:</strong> ' . $camara['id_camara'] . '</p>';
            echo '<p><strong>Ubicación:</strong> ' . $camara['ubicacion'] . '</p>';
            echo '<p><strong>Tipo:</strong> ' . $camara['tipo_camara'] . '</p>';
            echo '<p><strong>Torre ID:</strong> ' . $camara['id_torre'] . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p style="color: ' . (($theme === 'dark') ? '#ccc' : '#777') . ';">No hay cámaras registradas en la base de datos.</p>';
    }

        ?>
        <br>
        <a href="dashboard.php">← Volver al Panel</a>
    </div>
</body>
</html>

<?php
session_start();
include("db.php");

if (!isset($_SESSION['id_persona'])) {
    echo "Error: Usuario no identificado.";
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $id_persona = $_SESSION['id_persona'];
    $fecha_hora_inicio = date("Y-m-d H:i:s");

     if (strlen($descripcion) > 800) {
        $mensaje = "<p class='error-message'>La descripción no puede exceder los 800 caracteres.</p>";
    } else {

    $insert = $conn->query("INSERT INTO comportamiento (descripcion, id_persona, fecha_hora)
                                        VALUES ('$descripcion', $id_persona, '$fecha_hora_inicio')");

    if ($insert) {
        $id_comportamiento = $conn->insert_id;
        $conn->query("INSERT INTO incidente (id_comportamiento, fecha_hora_inicio, estado)
                                            VALUES ($id_comportamiento, '$fecha_hora_inicio', 'reportado')");
        $mensaje = "<p class='success-message'>Incidente reportado correctamente.</p>";
    } else {
        $mensaje = "<p class='error-message'>Error al reportar el incidente: " . $conn->error . "</p>";
    }
}
}

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

if (isset($_GET['toggle_theme'])) {
    $new_theme = ($theme === 'light') ? 'dark' : 'light';
    setcookie('theme', $new_theme, time() + (86400 * 30), "/"); 
    header("Location: reportar_incidente.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reportar Incidente</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: <?php echo ($theme === 'dark') ? '#121212' : '#e0e0e0'; ?>;
            color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
            min-height: 100vh;
            position: relative;
            overflow: auto; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            transition: background-color 0.3s ease, color 0.3s ease;
            background-image: url('imagenes_camaras/<?php echo ($theme === 'dark') ? 'all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg' : 'vecteezy_esoteric-eye-in-triangle_16927071.jpg'; ?>'); /* Ruta de tu logo */
            background-repeat: repeat; 
            background-size: 100px auto; 
            background-position: center center;
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: <?php echo ($theme === 'dark') ? 'rgba(30, 30, 30, 0.8)' : 'rgba(255, 255, 255, 0.8)'; ?>;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            margin-top: 50px;
            text-align: center;
        }

        h2 {
            color: #8e44ad;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            text-align: left;
            color: <?php echo ($theme === 'dark') ? '#e0e0e0' : '#555'; ?>;
        }

        textarea {
            width: calc(100% - 12px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid <?php echo ($theme === 'dark') ? '#424242' : '#ccc'; ?>;
            border-radius: 5px;
            background-color: <?php echo ($theme === 'dark') ? '#212121' : '#f9f9f9'; ?>;
            color: <?php echo ($theme === 'dark') ? '#f0f0f0' : '#333'; ?>;
            box-sizing: border-box;
            font-size: 1em;
        }

        input[type="submit"], button {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        button {
            background-color: #6c757d;
            color: white;
            margin-top: 10px;
        }

        button:hover {
            background-color: #545b62;
        }

        p a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        p a:hover {
            color: #0056b3;
        }

        .success-message {
            color: #28a745;
            margin-top: 10px;
        }

        .error-message {
            color: #dc3545;
            margin-top: 10px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Reportar Incidente</h2>
        <form method="POST">
            <label for="descripcion">Descripción del incidente:</label><br>
            <textarea name="descripcion" id="descripcion" required rows="20" cols="50" maxlength="800"></textarea><br><br>
            <input type="submit" value="Reportar">
        </form>
        <?= $mensaje ?>
        <p><a href="ver_reportes.php">Ver listado de incidentes</a></p>
        <button onclick="window.location.href='dashboard.php'">Menú Principal</button>
    </div>
</body>
</html>

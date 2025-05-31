<?php
include('db.php');
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $verificar = $conn->query("SELECT * FROM persona WHERE correo='$correo'");

    if ($verificar->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expira = date("Y-m-d H:i:s", strtotime("+24 hours"));
        $conn->query("INSERT INTO reset_password_tokens (correo, token, expiracion) VALUES ('$correo', '$token', '$expira')");

        $enlace = "http://localhost/Logi/reset.php?token=$token";
        echo "<div class='container'>";
        echo "<h2>Recuperar Contraseña</h2>";
        echo "<p style='text-align:center;'>Hemos enviado un enlace de recuperación a tu correo (simulado):</p>";
        echo "<p style='text-align:center;'><a href='$enlace'>$enlace</a></p>";
        echo "<div class='button-container'>";
        echo "<button onclick=\"window.location.href='index.php'\">Regresar al Inicio</button>";
        echo "</div>";
        echo "</div>";
        exit();
    } else {
        $mensaje = "Correo no registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #2e2e2e; /* gris oscuro */
            color: #e0d8f9; /* lila claro */
            height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 400px;
            height: 400px;
            transform: translate(-10%, -10%);
            background-image: url('imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.05;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: rgba(50, 50, 50, 0.9); 
            max-width: 400px;
            margin: 20px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(186, 104, 200, 0.2); 
            text-align: center;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #d1c4e9; 
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }

        input[type="email"] {
            padding: 10px;
            margin-bottom: 15px;
            border: none;
            border-radius: 5px;
            background-color: rgba(255, 255, 255, 0.08);
            color: #f3e5f5;
        }

        input[type="submit"] {
            background-color: #8e24aa; 
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: #6a1b9a; 
        }

        .error {
            color: #ef9a9a; 
            text-align: center;
            margin-top: 10px;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .button-container button {
            padding: 10px 20px;
            cursor: pointer;
            background: #7b1fa2; 
            color: white;
            border: none;
            border-radius: 5px;
        }

        .button-container button:hover {
            background: #4a148c; 
        }

        a {
            color: #ba68c8; 
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Recuperar Contraseña</h2>
        <form method="POST">
            <input type="email" name="correo" placeholder="Ingresa tu correo" required><br><br>
            <input type="submit" value="Enviar enlace">
        </form>
        <?php if ($mensaje) echo "<p class='error'>$mensaje</p>"; ?>

        <div class="button-container">
            <button onclick="window.location.href='index.php'">Regresar al Inicio</button>
        </div>
    </div>
</body>
</html>

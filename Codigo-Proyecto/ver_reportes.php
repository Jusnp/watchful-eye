<?php
session_start();
include("db.php");

$resultado = $conn->query("
    SELECT i.id_incidente, c.descripcion, i.fecha_hora_inicio, i.estado
    FROM incidente i
    INNER JOIN comportamiento c ON i.id_comportamiento = c.id_comportamiento
    ORDER BY i.fecha_hora_inicio DESC
");

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Listado de Incidentes</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #000;
            color: white;
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
            position: fixed;
            top: 20px;
            left: 20px;
            width: 100px; 
            height: 100px; 
            background-image: url('imagenes_camaras/vecteezy_esoteric-eye-in-triangle_16927071.jpg'); /* Ruta de tu logo */
            background-size: contain;
            background-repeat: no-repeat;
            background-position: top left;
            opacity: 0.9; 
            z-index: 0;
            pointer-events: none;
            transition: opacity 0.3s ease, filter 0.3s ease;
        }

        body.light-mode {
            background-color: white;
            color: #333;
        }

        body.light-mode::before {
            opacity: 0.50; 
            filter: brightness(150%);
        }

        .container {
            position: relative;
            z-index: 1;
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.1);
            width: 90%;
            max-width: 800px;
            margin-top: 80px;
            text-align: center;
        }

        .light-mode .container {
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #f0f0f0;
            margin-bottom: 20px;
        }

        .light-mode h2 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #555;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: #f0f0f0;
        }

        .light-mode th {
            background-color: #f0f0f0;
            color: #333;
            border-color: #ccc;
        }

        td {
            background-color: #222;
        }

        .light-mode td {
            background-color: #f9f9f9;
            color: #333;
            border-color: #ccc;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .actions a, .actions button {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .actions a, .actions button { 
            color: <?php echo ($theme === 'dark') ? '#5cb85c' : '#007bff'; ?>;
            border: 2px solid <?php echo ($theme === 'dark') ? '#5cb85c' : '#007bff'; ?>;
            background-color: transparent; /* Fondo transparente */
        }

        .light-mode .actions a, .light-mode .actions button {
            color: #007bff;
            border-color: #007bff;
        }

        .actions a:hover, .actions button:hover {
            color: <?php echo ($theme === 'dark') ? '#77dd77' : '#0056b3'; ?>;
            border-color: <?php echo ($theme === 'dark') ? '#77dd77' : '#0056b3'; ?>;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .light-mode .actions a:hover, .light-mode .actions button:hover {
            color: #0056b3;
            border-color: #0056b3;
            background-color: rgba(0, 123, 255, 0.1);
        }
    </style>
</head>
<body class="<?php echo ($theme === 'dark') ? 'dark-mode' : 'light-mode'; ?>">
    <div class="container">
        <h2>Listado de Reportes e Incidentes</h2>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Fecha/Hora</th>
                <th>Estado</th>
            </tr>
            <?php while ($row = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id_incidente'] ?></td>
                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                <td><?= $row['fecha_hora_inicio'] ?></td>
                <td><?= ucfirst($row['estado']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <div class="actions">
            <a href="reportes_incidentes.php">Reportar nuevo incidente</a>
            <button onclick="window.location.href='dashboard.php'">Menu principal</button>
        </div>
    </div>

    <script>
        const body = document.body;
        const theme = '<?php echo $theme; ?>';

        if (theme === 'dark') {
            body.classList.add('dark-mode');
        } else {
            body.classList.add('light-mode');
        }
    </script>
</body>
</html>

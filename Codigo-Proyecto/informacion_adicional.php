<?php
session_start();
if (!isset($_SESSION['id_persona'])) {
    header("Location: login.php"); 
    exit();
}

date_default_timezone_set('America/Bogota');

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "watchful_eye";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

$unidad_info = [
    'nombre' => 'watchful_eye',
    'direccion' => 'Calle 10A #40-27',
    'administrador' => 'juan Diego Paz',
    'telefono_administracion' => '555555555'
];


$avisos = [
    ['titulo' => 'Recordatorio: Pago de Administración', 'fecha' => date('Y-m-d'), 'descripcion' => 'Se recuerda a los residentes realizar el pago de la administración antes del [30/05/2025].'],
['titulo' => 'Mantenimiento de Zonas Verdes', 'fecha' => date('Y-m-d', strtotime('+2 days')), 'descripcion' => 'Se informa que el [30/05/2025S] se realizará mantenimiento en las zonas verdes de la unidad.'],

];

function obtenerPicoYPlacaSabaneta() {
    $dia_semana = date('N'); 
    $ultimo_digito_placa = date('j') % 10; 

    switch ($dia_semana) {
        case 1: // Lunes
            return in_array($ultimo_digito_placa, [0, 1]) ? '0 y 1' : 'No aplica';
        case 2: // Martes
            return in_array($ultimo_digito_placa, [2, 3]) ? '2 y 3' : 'No aplica';
        case 3: // Miércoles
            return in_array($ultimo_digito_placa, [4, 5]) ? '4 y 5' : 'No aplica';
        case 4: // Jueves
            return in_array($ultimo_digito_placa, [6, 7]) ? '6 y 7' : 'No aplica';
        case 5: // Viernes
            return in_array($ultimo_digito_placa, [8, 9]) ? '8 y 9' : 'No aplica';
        default: // Sábado y Domingo
            return 'No aplica';
    }
}

$pico_y_placa_hoy = obtenerPicoYPlacaSabaneta();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Información Adicional</title>
<style>
    :root {
        --fondo-claro: #f4f4f4;
        --texto-claro: #333;
        --box-claro: #ffffff;
        --aviso-fondo-claro: #f9f9f9;
        --aviso-borde-claro: #28a745;
        --titulo-claro: #007bff;
        --boton-claro: #007bff;
        --boton-hover-claro: #0056b3;

        --fondo-oscuro: #2c2c2c;
        --texto-oscuro: #f0f0f0;
        --box-oscuro: #3a3a3a;
        --aviso-fondo-oscuro: #4b4b4b;
        --aviso-borde-oscuro: #a259ff;
        --titulo-oscuro: #a259ff;
        --boton-oscuro: #a259ff;
        --boton-hover-oscuro: #8b5cf6;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 20px;
        transition: background-color 0.3s, color 0.3s;
    }

    body[data-theme="claro"] {
        background-color: var(--fondo-claro);
        color: var(--texto-claro);
    }

    body[data-theme="oscuro"] {
        background-color: var(--fondo-oscuro);
        color: var(--texto-oscuro);
    }

    h1, h2 {
        color: var(--titulo-claro);
    }

    body[data-theme="oscuro"] h1,
    body[data-theme="oscuro"] h2 {
        color: var(--titulo-oscuro);
    }

    .info-box {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        transition: background-color 0.3s;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    body[data-theme="claro"] .info-box {
        background-color: var(--box-claro);
    }

    body[data-theme="oscuro"] .info-box {
        background-color: var(--box-oscuro);
        box-shadow: 0 2px 6px rgba(162, 89, 255, 0.3);
    }

    .aviso-item {
        margin-bottom: 10px;
        padding: 10px;
        border-left: 5px solid var(--aviso-borde-claro);
        border-radius: 4px;
    }

    body[data-theme="claro"] .aviso-item {
        background-color: var(--aviso-fondo-claro);
        border-left-color: var(--aviso-borde-claro);
    }

    body[data-theme="oscuro"] .aviso-item {
        background-color: var(--aviso-fondo-oscuro);
        border-left-color: var(--aviso-borde-oscuro);
    }

    .aviso-item h3 {
        margin-top: 0;
    }

    body[data-theme="claro"] .aviso-item h3 {
        color: var(--aviso-borde-claro);
    }

    body[data-theme="oscuro"] .aviso-item h3 {
        color: #c084fc;
    }

    .aviso-item .fecha {
        font-size: 0.9em;
        color: #bbb;
        margin-bottom: 5px;
    }

    .pico-placa {
        color: #ff6ec7;
        font-weight: bold;
    }

    .volver-dashboard {
        margin-top: 20px;
    }

    .volver-dashboard a {
        display: inline-block;
        padding: 10px 15px;
        text-decoration: none;
        color: white;
        border-radius: 5px;
        transition: background-color 0.3s;
    }

    body[data-theme="claro"] .volver-dashboard a {
        background-color: var(--boton-claro);
    }

    body[data-theme="claro"] .volver-dashboard a:hover {
        background-color: var(--boton-hover-claro);
    }

    body[data-theme="oscuro"] .volver-dashboard a {
        background-color: var(--boton-oscuro);
    }

    body[data-theme="oscuro"] .volver-dashboard a:hover {
        background-color: var(--boton-hover-oscuro);
    }

    .toggle-tema {
        margin-bottom: 20px;
    }

    .toggle-tema button {
        padding: 8px 14px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s;
    }

    body[data-theme="claro"] .toggle-tema button {
        background-color: var(--boton-claro);
        color: white;
    }

    body[data-theme="claro"] .toggle-tema button:hover {
        background-color: var(--boton-hover-claro);
    }

    body[data-theme="oscuro"] .toggle-tema button {
        background-color: var(--boton-oscuro);
        color: white;
    }

    body[data-theme="oscuro"] .toggle-tema button:hover {
        background-color: var(--boton-hover-oscuro);
    }
</style>
</head>
<body>



    <h1>Información Adicional</h1>
        <div class="toggle-tema">
    <button id="btn-tema">Cambiar Tema</button>
</div>

    <div class="info-box">
        <h2>Información de la Unidad Residencial</h2>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($unidad_info['nombre']); ?></p>
        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($unidad_info['direccion']); ?></p>
        <p><strong>Administrador:</strong> <?php echo htmlspecialchars($unidad_info['administrador']); ?></p>
        <p><strong>Teléfono Administración:</strong> <?php echo htmlspecialchars($unidad_info['telefono_administracion']); ?></p>
    </div>

    <div class="info-box">
        <h2>Avisos Básicos</h2>
        <?php if (!empty($avisos)): ?>
            <?php foreach ($avisos as $aviso): ?>
                <div class="aviso-item">
                    <h3><?php echo htmlspecialchars($aviso['titulo']); ?></h3>
                    <p class="fecha">Fecha: <?php echo htmlspecialchars($aviso['fecha']); ?></p>
                    <p><?php echo htmlspecialchars($aviso['descripcion']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay avisos disponibles en este momento.</p>
        <?php endif; ?>
    </div>

    <div class="info-box">
        <h2>Pico y Placa del Día (Sabaneta)</h2>
        <p>Hoy, <?php echo date('l, j de F de Y'); ?>, el pico y placa para vehículos particulares en Sabaneta aplica para placas terminadas en: <span class="pico-placa"><?php echo htmlspecialchars($pico_y_placa_hoy); ?></span>.</p>
        <p><small><strong>Nota:</strong> Esta información es un ejemplo y puede variar. Consulte las fuentes oficiales para conocer las regulaciones exactas del pico y placa en Sabaneta.</small></p>
    </div>

    <div class="volver-dashboard">
        <a href="dashboard.php"> Volver al Panel</a>
    </div>
    

</body>

<script>
    function cambiarTema() {
        const temaActual = document.body.getAttribute('data-theme') || 'oscuro';
        const nuevoTema = temaActual === 'oscuro' ? 'claro' : 'oscuro';
        document.body.setAttribute('data-theme', nuevoTema);
        document.cookie = "tema=" + nuevoTema + "; path=/; max-age=31536000";
    }

    function aplicarTemaGuardado() {
        const match = document.cookie.match(/(?:^|; )tema=([^;]*)/);
        const tema = match ? match[1] : 'oscuro';
        document.body.setAttribute('data-theme', tema);
    }

    document.getElementById('btn-tema').addEventListener('click', cambiarTema);
    aplicarTemaGuardado();
</script>


</html>

<?php session_start();
if (!isset($_SESSION['usuario'])) header("Location: login.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) {
    session_unset();
    session_destroy();
    header("Location: login.php?msg=inactividad");
    exit();
}
$_SESSION['last_activity'] = time();

$all_data = [
    ["titulo" => "Administración de Usuarios", "url" => "admin_panel.php"],
    ["titulo" => "Visualización de Cámaras", "url" => "ver_camaras.php"],
    ["titulo" => "Reportes de Incidentes", "url" => "reportes_incidentes.php"],
    ["titulo" => "Cerrar Sesión", "url" => "logout.php"],
    ["titulo" => "Información Adicional 1", "url" => "#info1"],
    ["titulo" => "Actualizar Datos", "url" => "#Actualizar_Datos.php"],
    ["titulo" => "Reporte Importante", "url" => "reportes_incidentes.php"],
];

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Panel Principal</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #2c2c2c;
        color: white;
        height: 100vh;
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    body::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 200px;
        height: 200px;
        transform: translate(-50%, -50%);
        background-image: url('imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jpg'); /* Ruta de tu logo */
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        opacity: 0.5;
        z-index: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    body::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: white; 
        background-image: url('imagenes_camaras/El-Poblado-Medellin-1080x810.jpg'); /* Reemplaza con la ruta de tu imagen de fondo claro */
        background-size: cover;
        background-repeat: no-repeat;
        opacity: 0;
        z-index: -1; 
        transition: opacity 0.3s ease;
    }

    body.dark-mode::after {
        opacity: 0; 
    }

    body.light-mode {
        background-color: white;
        color: #333;
    }

    body.light-mode::before {
        opacity: 0;
    }

    body.light-mode::after {
        opacity: 1; 
    }

    .light-mode .container {
        background-color: rgba(255, 255, 255, 0.8);
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
    }

    .light-mode h1 {
        color: #333;
    }

    .light-mode a {
        color: #007bff;
        border-color: #007bff;
    }

    .light-mode a:hover {
        color: #0056b3;
        border-color: #0056b3;
        background-color: rgba(0, 123, 255, 0.1);
    }

    .container {
        position: relative;
        z-index: 1;
        background-color: rgba(44, 44, 44, 0.95));
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 25px rgba(162, 89, 255, 0.2);
        width: 90%;
        max-width: 800px;
        margin: 50px auto;
        text-align: center;
        display: flex; 
        flex-direction: column; 
        align-items: center; 
    }

    .logo-container {
        margin-bottom: 20px;
    }

    .logo-container img {
        width: 150px;
        height: auto;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
        color: #e0e0e0;
    }

    .container > ul { 
        list-style: none;
        padding: 0;
        width: 100%;
    }

    .container > ul > li {
        margin-bottom: 15px; 
    }

    .container > ul > li > a {
        display: block;
        padding: 12px;
        background-color: transparent;
        text-decoration: none;
        color: #a259ff; 
        border: 2px solid #a259ff;
        border-radius: 6px;
        transition: all 0.3s ease;
        min-width: 200px;
        box-sizing: border-box;
        text-align: center;
    }

    .container > ul > li > a:hover {
        color: #c084fc;
        border-color: #c084fc;
        background-color: rgba(255, 255, 255, 0.05);
    }

    #shortcut-list,
    #global-search-container,
    #theme-toggle-container {
        margin-bottom: 20px;
        position: fixed;
        z-index: 1000;
        background-color: rgba(44, 44, 44, 0.95);
        color: white;
        padding: 15px;
        border-radius: 10px;
    }

    #shortcut-list {
        top: 20px;
        left: 20px;
        display: none;
    }

    #shortcut-list h3 {
        margin-top: 0;
        text-align: center;
    }

    #shortcut-list ul {
        list-style: none;
        padding: 0;
    }

    #shortcut-list li {
        margin: 5px 0;
    }

    #shortcut-list a {
        color: white;
        text-decoration: none;
    }

    #global-search-container {
        top: 20px;
        right: 20px;
    }

    #global-search {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        border: 1px solid #a259ff;
        border-radius: 5px;
        background-color: #3a3a3a;
        color: white;
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    #search-results {
        position: absolute;
        top: 35px;
        right: 0;
        background-color: #3a3a3a;
        border: 1px solid #8b5cf6;
        border-radius: 5px;
        width: 200px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: none;
        list-style: none;
        padding: 0;
        margin-top: 10px;
        margin: 0;
        color: <?php echo ($theme === 'dark') ? 'white' : '#333'; ?>;
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        z-index: 1;
    }

    #search-results li {
        padding: 10px;
        border-bottom: 1px solid #555;
        cursor: pointer;
    }

    #search-results li:hover {
        background-color: #4b4b4b;
    }

    #search-results li a {
        color: <?php echo ($theme === 'dark') ? 'white' : '#333'; ?>;
        text-decoration: none;
        display: block;
    }

    #theme-toggle-container {
        bottom: 20px;
        left: 20px;
    }

    #theme-toggle-button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        transition: background-color 0.3s ease;
    }

    #theme-toggle-button:hover {
        background-color: #0056b3;
    }

    .dark-mode {
        background-color: #2c2c2c;
        color: white;
    }

    .dark-mode .container {
        background-color: rgba(0, 0, 0, 0.8);
        box-shadow: 0 0 25px rgba(255, 255, 255, 0.1);
    }

    .dark-mode h1 {
        color: #f4f4f4;
    }

    .dark-mode a {
        color: #a259ff;
        border-color: #a259ff;
    }

    .dark-mode a:hover {
        color: #c084fc;
        border-color: #c084fc;
        background-color: rgba(255, 255, 255, 0.05);
    }
        #theme-toggle-button {
        margin-top: 10px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background-color: #a259ff;
        color: white;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #theme-toggle-button:hover {
        background-color: #8b5cf6;
    }
</style>
</head>
<body class="<?php echo ($theme === 'dark') ? 'dark-mode' : 'light-mode'; ?>">
    <div class="container">
        <div class="logo-container">
            <img src="imagenes_camaras/all-seeing-eye-symbol-of-religion-spirituality-occultism-illustration-isolated-on-a-dark-background-free-vector.jp" alt="">
        </div>
        <h1>BIENVENIDO: Aqui podra acceder a funcionalidades de sistema.<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$tipo = $_SESSION['tipo_usuario'];
?></h1>
        <ul>
            <?php if ($tipo === 'P' || $tipo === 'A' || $tipo === 'X'): ?>
    <li><a href="informacion_adicional.php">Información Adicional</a></li>
    <li><a href="Actualizar_Datos.php">Actualizar Datos</a></li>
<?php endif; ?>

<?php if ($tipo === 'V' || $tipo === 'X'): ?>
    <li><a href="ver_camaras.php">Ver Cámaras</a></li>
    <li><a href="reportes_incidentes.php">Reportes e Incidentes</a></li>
<?php endif; ?>

<?php if ($tipo === 'X'): ?>
    <!-- Puedes agregar funcionalidades exclusivas del administrador -->
    <li><a href="admin_panel.php">Gestión de Usuarios</a></li>
<?php endif; ?>

    <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </div>

    <div id="shortcut-list">
        <h3>Atajos de Teclado</h3>
        <ul>
            <li><kbd>Ctrl</kbd> + <kbd>/</kbd>: Mostrar/Ocultar esta lista</li>
            <li><kbd>Ctrl</kbd> + <kbd>1</kbd>: <a href="admin_panel.php">Administrar Usuarios</a></li>
            <li><kbd>Ctrl</kbd> + <kbd>2</kbd>: <a href="ver_camaras.php">Ver Cámaras</a></li>
            <li><kbd>Ctrl</kbd> + <kbd>3</kbd>: <a href="reportes_incidentes.php">Reportes e Incidentes</a></li>
            <li><kbd>Ctrl</kbd> + <kbd>L</kbd>: <a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </div>

    <div id="global-search-container">
        <input type="text" id="global-search" placeholder="Buscar..." onkeyup="buscarGlobal()">
        <ul id="search-results"></ul>
    </div>

    <div id="theme-toggle-container">
        <button id="theme-toggle-button" onclick="toggleTheme()">
            <?php echo ($theme === 'dark') ? 'Modo Claro' : 'Modo Oscuro'; ?>
        </button>
    </div>

    <script>
        const searchInput = document.getElementById('global-search');
        const searchResults = document.getElementById('search-results');
        const body = document.body;
        const themeToggleButton = document.getElementById('theme-toggle-button');
        const allData = <?php echo json_encode($all_data); ?>; // Pasar datos de PHP a JavaScript

        function buscarGlobal() {
            const searchTerm = searchInput.value.toLowerCase();
            const results = allData.filter(item =>
                item.titulo.toLowerCase().includes(searchTerm)
            );

            mostrarResultados(results);
        }

        function mostrarResultados(results) {
            searchResults.innerHTML = '';
            if (results.length > 0 && searchInput.value.trim() !== '') {
                results.forEach(result => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.href = result.url;
                    a.textContent = result.titulo;
                    li.appendChild(a);
                    searchResults.appendChild(li);
                });
                searchResults.style.display = 'block';
            } else {
                searchResults.style.display = 'none';
            }
        }

        document.addEventListener('click', function(event) {
            if (!event.target.closest('#global-search-container')) {
                searchResults.style.display = 'none';
            }
        });

        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === '/') {
                const shortcutList = document.getElementById('shortcut-list');
                shortcutList.style.display = shortcutList.style.display === 'none' ? 'block' : 'none';
            }

            if (event.ctrlKey || event.metaKey) {
                const shortcut = event.key.toLowerCase();
                const links = document.querySelectorAll('ul > li > a[data-shortcut]');

                links.forEach(link => {
                    if (link.getAttribute('data-shortcut') === shortcut) {
                        window.location.href = link.href;
                    }
                });
            }
        });

        function toggleTheme() {
            body.classList.toggle('dark-mode');
            body.classList.toggle('light-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            document.cookie = `theme=${isDarkMode ? 'dark' : 'light'}; path=/; SameSite=Lax`;
            themeToggleButton.textContent = isDarkMode ? 'Modo Claro' : 'Modo Oscuro';

            const searchInput = document.getElementById('global-search');
            const searchResults = document.getElementById('search-results');
            const themeColorInput = isDarkMode ? '#444' : 'white';
            const textColorInput = isDarkMode ? 'white' : '#333';

            searchInput.style.backgroundColor = themeColorInput;
            searchInput.style.color = textColorInput;
            searchResults.style.backgroundColor = themeColorInput;
            searchResults.style.color = textColorInput;
        }
    </script>
</body>
</html>

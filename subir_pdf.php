<?php
include 'db_config.php'; // Conexión a la base de datos

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Rutas de destino
    $target_dir_pdf = "uploads/";
    $target_dir_img = "uploads/images/";

    // Crear carpeta para imágenes si no existe
    if (!is_dir($target_dir_img)) {
        mkdir($target_dir_img, 0777, true);
    }

    // Obtener nombres de archivos
    $pdfFileName = basename($_FILES["pdfToUpload"]["name"]);
    $imgFileName = basename($_FILES["imageToUpload"]["name"]);
    
    // Rutas completas para los archivos
    $pdfFilePath = $target_dir_pdf . $pdfFileName;
    $imgFilePath = $target_dir_img . $imgFileName;

    // Mover archivos subidos
    if (move_uploaded_file($_FILES["pdfToUpload"]["tmp_name"], $pdfFilePath) &&
        move_uploaded_file($_FILES["imageToUpload"]["tmp_name"], $imgFilePath)) {

        // Recoger datos del formulario
        $nombre = $_POST['nombre'];
        $descripcion = $_POST['descripcion'];
        $genero = $_POST['genero'];

        // Consulta para insertar datos
        $sql = "INSERT INTO novelas (nombre, descripcion, ruta, imagen, genero) VALUES ('$nombre', '$descripcion', '$pdfFilePath', '$imgFilePath', '$genero')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('El archivo se ha subido correctamente.');</script>";
        } else {
            echo "<script>alert('Error al subir el archivo: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error al subir el archivo.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario para subir novelas</title>
    <link rel="icon" href="uploads/aetheris.png" type="image/x-icon">
    <style>
        /* Estilo del encabezado */
        header {
    display: flex;
    justify-content: space-between;
    padding: 10px 20px;
    background-color: #000000;
    color: white;
    font-family: Arial, sans-serif;
}

.header-left .logo {
    font-size: 24px;
    color: white;
    text-decoration: none;
}

.header-right {
    display: flex;
    align-items: center;
}

.menu {
    position: relative;
    margin-right: 20px;
}

.menu-button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

.menu-content {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: #000000;
    min-width: 150px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1;
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
    background-color: #000000;
}

/* Submenú de Géneros */
.submenu {
    position: relative;
}

.submenu-button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

.submenu-content {
    display: none;
    position: absolute;
    left: 100%;
    top: 0;
    background-color: #000000;
    min-width: 150px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1;
}

.submenu:hover .submenu-content {
    display: block;
}

.submenu-content a {
    color: white;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.submenu-content a:hover {
    background-color: #000000;
}

/* Barra de búsqueda */
.search-bar {
    display: flex;
    align-items: center;
}

.search-bar input[type="text"] {
    padding: 5px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
}

.search-bar button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    margin-left: 5px;
}

        /* Estilo del formulario */
        form {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        input[type="file"], input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f1f1f1;
        }

        input[type="submit"] {
            background-color: #4B0082; /* Morado oscuro */
            color: white;
            cursor: pointer;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #6A0DAD; /* Morado más claro al pasar el cursor */
        }

        /* Ajuste en los márgenes de los mensajes */
        .message {
            text-align: center;
            margin: 20px 0;
        }
       
body {
    background-image: url('uploads/images/fondo.jpg'); /* Ruta de la imagen */
    background-size: cover; /* Esto hace que la imagen cubra toda la pantalla */
    background-position: center center; /* Esto centra la imagen */
    background-attachment: fixed; /* Esto mantiene la imagen fija cuando haces scroll */
    background-repeat: no-repeat; /* Evita que la imagen se repita */
}
    </style>
</head>
<body>

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
                <a href="subir_pdf.php">Subir Novela</a>
                <div class="submenu">
                    <button class="submenu-button">Géneros</button>
                    <div class="submenu-content">
                        <a href="romance.php">Romance</a>
                        <a href="ciencia_ficcion.php">Ciencia Ficción</a>
                        <a href="comedia.php">Comedia</a>
                        <a href="isekai.php">Isekai</a>
                        <a href="fantasia.php">Fantasía</a>
                        <a href="misterio.php">Misterio</a>
                    </div>
                </div>
                <a href="login.php" class="login-icon">Iniciar Sesión</a>
            </div>
        </div>
        <div class="search-bar">
            <input type="text" placeholder="Buscar novela..." name="search">
            <button type="submit">🔍</button>
        </div>
    </div>
</header>

<!-- Formulario para subir PDFs -->
<form action="subir_pdf.php" method="post" enctype="multipart/form-data">
    <label for="pdfToUpload">Selecciona el PDF:</label>
    <input type="file" name="pdfToUpload" id="pdfToUpload" required>
    
    <label for="imageToUpload">Selecciona la imagen:</label>
    <input type="file" name="imageToUpload" id="imageToUpload" required>
    
    <label for="nombre">Nombre del documento:</label>
    <input type="text" name="nombre" id="nombre" required>
    
    <label for="descripcion">Descripción del documento:</label>
    <textarea name="descripcion" id="descripcion" required></textarea>
    
    <label for="genero">Género:</label>
    <select name="genero" id="genero" required>
        <option value="romance">Romance</option>
        <option value="ciencia_ficcion">Ciencia Ficción</option>
        <option value="comedia">Comedia</option>
        <option value="isekai">Isekai</option>
        <option value="fantasia">Fantasía</option>
        <option value="misterio">Misterio</option>
    </select>
    
    <input type="submit" value="Subir">
    <a href="subir.php"><input type="button" value="Subir volumenes" /></a>
</form>

</body>
</html>

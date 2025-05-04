<?php
include 'db_config.php';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $generos_seleccionados = $_POST['generos'] ?? [];

    // Procesar imágenes
    $imagen = '';
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = 'uploads/images/' . uniqid() . '_' . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen);
    }
    
    // Validar datos
    if (empty($nombre) || empty($descripcion) || empty($generos_seleccionados)) {
        $error = "Los campos marcados con * son obligatorios";
    } else {
        // Iniciar transacción
        $conn->begin_transaction();
        
        try {
            // 1. Insertar la novela
            $stmt = $conn->prepare("INSERT INTO documentos (nombre, descripcion, imagen ) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nombre, $descripcion, $imagen);
            $stmt->execute();
            $anime_id = $conn->insert_id;
            
            // 2. Insertar géneros seleccionados
            $stmt_generos = $conn->prepare("INSERT INTO novela_generos (novela_id, genero_id) VALUES (?, ?)");
            
            foreach ($generos_seleccionados as $genero_id) {
                $stmt_generos->bind_param("ii", $novela_id, $genero_id);
                $stmt_generos->execute();
            }
            
            // Confirmar transacción
            $conn->commit();
            $success = "¡novela subida correctamente!";
            $_POST = array(); // Limpiar el formulario
            
        } catch (Exception $e) {
            // Revertir en caso de error
            $conn->rollback();
            $error = "Error al subir la novela: " . $e->getMessage();
            
            // Eliminar imágenes subidas si hubo error
            if (!empty($imagen) && file_exists($imagen)) unlink($imagen);
        }
    }
}

// Obtener lista de géneros disponibles
$generos = $conn->query("SELECT * FROM generos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir novela - Aetheris</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(106, 198, 221);
            --dark-bg: #121212;
            --card-bg: #1a1a2a;
            --text-color: #e0e0e0;
            --text-muted: #a0a0a0;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color:rgb(16, 55, 65);
            color: var(--text-color);
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color:rgb(41, 101, 116);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(11, 115, 146, 0.3);
        }
        
        h1 {
            color: var(--primary-color);
            margin-top: 0;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .required:after {
            content: " *";
            color: #f44336;
        }
        
        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            background-color: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            color: var(--text-color);
            font-family: 'Open Sans', sans-serif;
            font-size: 15px;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .generos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        
        .genero-option {
            position: relative;
        }
        
        .genero-option input {
            position: absolute;
            opacity: 0;
        }
        
        .genero-option label {
            display: block;
            padding: 10px 15px;
            background-color: rgba(74, 198, 255, 0.1);
            border: 1px solid rgba(74, 213, 255, 0.3);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: normal;
            margin-bottom: 0;
        }
        
        .genero-option input:checked + label {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .file-upload {
            margin-top: 10px;
        }
        
        .file-upload-label {
            display: inline-block;
            padding: 12px 20px;
            background-color: rgba(74, 213, 255, 0.1);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px dashed rgba(74, 234, 255, 0.5);
            text-align: center;
            width: 100%;
        }
        
        .file-upload-label:hover {
            background-color: rgba(74, 213, 255, 0.2);
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn:hover {
            background-color: #3a7bef;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: rgba(76, 175, 80, 0.15);
            color: #4CAF50;
            border-color: #4CAF50;
        }
        
        .alert-error {
            background-color: rgba(244, 67, 54, 0.15);
            color: #F44336;
            border-color: #F44336;
        }
        
        .preview-container {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .preview-box {
            flex: 1;
            text-align: center;
        }
        
        .preview-img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 6px;
            border: 1px dashed rgba(255,255,255,0.3);
            display: none;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            .preview-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Subir Nueva novela</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nombre" class="required">Nombre de la novela</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion" class="required">Descripción</label>
                <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="required">Géneros</label>
                <div class="generos-container">
                    <?php while ($genero = $generos->fetch_assoc()): ?>
                        <div class="genero-option">
                            <input type="checkbox" id="genero_<?php echo $genero['id']; ?>" name="generos[]" value="<?php echo $genero['id']; ?>"
                                <?php echo (in_array($genero['id'], $_POST['generos'] ?? [])) ? 'checked' : ''; ?>>
                            <label for="genero_<?php echo $genero['id']; ?>"><?php echo htmlspecialchars($genero['nombre']); ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen portada</label>
                <div class="file-upload">
                    <label for="imagen" class="file-upload-label">Seleccionar imagen (JPEG/PNG, max 2MB)</label>
                    <input type="file" id="imagen" name="imagen" accept="image/jpeg, image/png" style="display: none;">
                    <div class="file-name" id="imagen-name">No se ha seleccionado archivo</div>
                </div>
                <div class="preview-container">
                    <div class="preview-box">
                        <img id="imagen-preview" class="preview-img" alt="Vista previa imagen">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn">Subir novela</button>
        </form>
    </div>

    <script>
        // Mostrar nombre de archivo y vista previa
        document.getElementById('imagen').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No se ha seleccionado archivo';
            document.getElementById('imagen-name').textContent = fileName;
            
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewImg = document.getElementById('imagen-preview');
                    previewImg.src = event.target.result;
                    previewImg.style.display = 'block';
                };
                reader.readAsDataURL(e.target.files[0]);
            } else {
                document.getElementById('imagen-preview').style.display = 'none';
            }
        });
        
    </script>
</body>
</html>
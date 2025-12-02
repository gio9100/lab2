<?php
// ============================================================================
// 🗑️ ELIMINAR CATEGORÍA - eliminar_categoria.php
// ============================================================================
// Este archivo maneja la eliminación de categorías.
// Muestra una confirmación antes de eliminar para evitar borrados accidentales.
// ============================================================================

// Traemos los archivos necesarios
include_once 'config-categorias.php';  // Conexión a la BD
include_once 'categoria.php';          // Clase Categoria

// Creamos la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Creamos un objeto de la clase Categoria
$categoria = new Categoria($db);

// Obtenemos el ID de la categoría a eliminar desde la URL
// Ejemplo: eliminar_categoria.php?id=5
$categoria->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no especificado.');

// ============================================================================
// 📌 EXPLICACIÓN DE $_SERVER['REQUEST_METHOD']
// ============================================================================
// $_SERVER['REQUEST_METHOD'] nos dice cómo se accedió a la página:
// - 'GET' = El usuario solo visitó la página (mostrar confirmación)
// - 'POST' = El usuario envió el formulario (ejecutar eliminación)
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Si el usuario confirmó la eliminación (envió el formulario)
    
    if ($categoria->eliminar()) {
        // Si se eliminó correctamente
        
        // ====================================================================
        // 📌 EXPLICACIÓN DE header() y exit()
        // ====================================================================
        // header('Location: ...') redirige al usuario a otra página
        // El parámetro ?mensaje=eliminado se usa para mostrar un mensaje
        // exit() es CRUCIAL después de header() para detener la ejecución
        // ====================================================================
        header("Location: listar_categorias.php?mensaje=eliminado");
        exit();
    } else {
        // Si hubo un error al eliminar
        die('ERROR: No se pudo eliminar la categoría.');
    }
} else {
    // Si es GET (solo visitó la página), cargamos los datos para mostrar confirmación
    if (!$categoria->leerUna()) {
        // Si no encontró la categoría
        die('ERROR: Categoría no encontrada.');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Categoría - Laboratorio Clínico</title>
    <!-- Bootstrap para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <!-- Card de confirmación -->
                <div class="card">
                    <!-- Header rojo para indicar peligro -->
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Confirmar Eliminación</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensaje de confirmación -->
                        <p>¿Estás seguro de que deseas eliminar la categoría:</p>
                        <!-- Mostramos el nombre de la categoría en rojo -->
                        <h5 class="text-danger">"<?php echo htmlspecialchars($categoria->nombre); ?>"?</h5>
                        <!-- Advertencia importante -->
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                        
                        <!-- Formulario de confirmación -->
                        <!-- Al enviar, se ejecutará el código POST de arriba -->
                        <form method="POST">
                            <div class="d-grid gap-2">
                                <!-- Botón rojo de eliminar -->
                                <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
                                <!-- Botón gris para cancelar -->
                                <a href="listar_categorias.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
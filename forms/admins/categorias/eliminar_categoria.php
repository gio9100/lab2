<?php
// Eliminar Categoría (Admin)
// Maneja la eliminación de categorías con confirmación

// Incluir configuración y clases
include_once 'config-categorias.php';
include_once 'categoria.php';

// Inicializar conexión y objeto
$database = new Database();
$db = $database->getConnection();
$categoria = new Categoria($db);

// Obtener ID de la URL
$categoria->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID no especificado.');

// Procesar eliminación si es POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($categoria->eliminar()) {
        // Redirigir con mensaje de éxito
        header("Location: listar_categorias.php?mensaje=eliminado");
        exit();
    } else {
        die('ERROR: No se pudo eliminar la categoría.');
    }
} else {
    // Si es GET, cargar datos para confirmación
    if (!$categoria->leerUna()) {
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
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <!-- Card de confirmación -->
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Confirmar Eliminación</h4>
                    </div>
                    <div class="card-body">
                        <p>¿Estás seguro de que deseas eliminar la categoría:</p>
                        <h5 class="text-danger">"<?php echo htmlspecialchars($categoria->nombre); ?>"?</h5>
                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                        
                        <!-- Formulario de confirmación -->
                        <form method="POST">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
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

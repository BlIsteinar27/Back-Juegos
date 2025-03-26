<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Obtener el ID del juego a actualizar
$idjuego = isset($_POST['idjuego']) ? (int)$_POST['idjuego'] : null;

// Validar que el ID del juego esté presente
if (!$idjuego) {
    http_response_code(400);
    echo json_encode(['error' => 'ID del juego no proporcionado']);
    exit;
}

// Asignar valores (permitir NULL si no se proporcionan)
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
$idestatus = isset($_POST['idestatus']) ? (int)$_POST['idestatus'] : null;
$idgenero = isset($_POST['idgenero']) ? (int)$_POST['idgenero'] : null;
$fechapublicacion = isset($_POST['fechapublicacion']) ? $_POST['fechapublicacion'] : null;
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : null;
$valoracion = isset($_POST['valoracion']) ? (int)$_POST['valoracion'] : null;

// Consultar la imagen actual del juego
$stmt = $pdo->prepare("SELECT imagen FROM juegos WHERE idjuego = :idjuego");
$stmt->execute(['idjuego' => $idjuego]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$imagenActual = $result['imagen'] ?? null; // Imagen actual del juego
$nuevaImagen = null; // Nueva imagen (si se proporciona)

// Manejo de la imagen (opcional)
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
    $imagen = $_FILES['imagen'];
    $nombreImagen = $imagen['name'];
    $tipoImagen = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
    $directorio = '../../img/';

    if (in_array($tipoImagen, ["jpg", "jpeg", "png"])) {
        $idRegistro = uniqid();
        $nombreArchivo = $idRegistro . "." . $tipoImagen;
        $ruta = $directorio . $nombreArchivo;

        // Eliminar la imagen anterior si existe
        if ($imagenActual && file_exists($directorio . $imagenActual)) {
            unlink($directorio . $imagenActual);
        }

        // Mover la nueva imagen al directorio
        if (move_uploaded_file($imagen['tmp_name'], $ruta)) {
            $nuevaImagen = $nombreArchivo;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir la imagen']);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de imagen no permitido']);
        exit;
    }
}

// Determinar qué imagen usar
$imagenFinal = $nuevaImagen ?? $imagenActual; // Usa la nueva imagen si existe, de lo contrario, mantiene la actual

try {
    // Preparar la consulta SQL
    $stmt = $pdo->prepare("UPDATE juegos SET 
        idestatus = :idestatus, 
        idgenero = :idgenero, 
        nombre = :nombre, 
        descripcion = :descripcion, 
        fechapublicacion = :fechapublicacion, 
        precio = :precio, 
        valoracion = :valoracion, 
        imagen = :imagen 
        WHERE idjuego = :idjuego");

    // Ejecutar la consulta con los valores proporcionados
    $stmt->execute([
        'idestatus' => $idestatus,
        'idgenero' => $idgenero,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'fechapublicacion' => $fechapublicacion,
        'precio' => $precio,
        'valoracion' => $valoracion,
        'imagen' => $imagenFinal, // Usa la imagen final (nueva o existente)
        'idjuego' => $idjuego
    ]);

    // Respuesta exitosa
    echo json_encode(['message' => 'Juego actualizado correctamente']);
} catch (Exception $e) {
    // Manejo de errores
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Ha ocurrido un error interno: ' . $e->getMessage()]);
}
?>
<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Log de datos recibidos
error_log(print_r($data, true));

// Obtener el ID del juego a actualizar
$idjuego = isset($data['idjuego']) ? (int)$data['idjuego'] : null;

// Validar que el ID del juego esté presente
if (!$idjuego) {
    http_response_code(400);
    echo json_encode(['error' => 'ID del juego no proporcionado']);
    exit;
}

// Asignar valores (permitir NULL si no se proporcionan)
$nombre = isset($data['nombre']) ? trim($data['nombre']) : null;
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
$idestatus = isset($data['idestatus']) ? (int)$data['idestatus'] : null;
$idgenero = isset($data['idgenero']) ? (int)$data['idgenero'] : null;
$fechapublicacion = isset($data['fechapublicacion']) ? $data['fechapublicacion'] : null;
$precio = isset($data['precio']) ? (float)$data['precio'] : null;
$valoracion = isset($data['valoracion']) ? (int)$data['valoracion'] : null;

// Manejo de la imagen (opcional)
$imagen = null;
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
        $stmt = $pdo->prepare("SELECT imagen FROM juegos WHERE idjuego = :idjuego");
        $stmt->execute(['idjuego' => $idjuego]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && file_exists($directorio . $result['imagen'])) {
            unlink($directorio . $result['imagen']);
        }

        // Mover la nueva imagen al directorio
        if (move_uploaded_file($imagen['tmp_name'], $ruta)) {
            $imagen = $nombreArchivo;
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

try {
    // Preparar la consulta SQL
    $stmt = $pdo->prepare("UPDATE juegos SET idestatus = :idestatus, idgenero = :idgenero, nombre = :nombre, descripcion = :descripcion, fechapublicacion = :fechapublicacion, precio = :precio, valoracion = :valoracion, imagen = :imagen WHERE idjuego = :idjuego");

    // Log de la consulta SQL
    error_log("Consulta SQL: " . $stmt->queryString);

    // Ejecutar la consulta con los valores proporcionados
    $stmt->execute([
        'idestatus' => $idestatus,
        'idgenero' => $idgenero,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'fechapublicacion' => $fechapublicacion,
        'precio' => $precio,
        'valoracion' => $valoracion,
        'imagen' => $imagen,
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

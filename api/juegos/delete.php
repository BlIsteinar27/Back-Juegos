<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Obtener el ID del juego a eliminar
$idjuego = isset($_POST['idjuego']) ? (int)$_POST['idjuego'] : null;

if (!$idjuego) {
    http_response_code(400);
    echo json_encode(['error' => 'ID del juego no proporcionado']);
    exit;
}

try {
    // Obtener el nombre de la imagen del juego
    $stmt = $pdo->prepare("SELECT imagen FROM juegos WHERE idjuego = :idjuego");
    $stmt->execute(['idjuego' => $idjuego]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404);
        echo json_encode(['error' => 'Juego no encontrado']);
        exit;
    }

    $imagen = $result['imagen'];
    $rutaImagen = '../../img/' . $imagen;

    // Eliminar la imagen del sistema de archivos
    if (file_exists($rutaImagen)) {
        if (!unlink($rutaImagen)) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar la imagen']);
            exit;
        }
    }

    // Eliminar el registro de la base de datos
    $stmt = $pdo->prepare("DELETE FROM juegos WHERE idjuego = :idjuego");
    if ($stmt->execute(['idjuego' => $idjuego])) {
        echo json_encode(['message' => 'Juego eliminado correctamente']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el juego de la base de datos']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Ha ocurrido un error interno: ' . $e->getMessage()]);
}
?>

<?php
require_once __DIR__ . '/../../../config/db.php';

header('Content-Type: application/json');

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar que se proporcione el idgenero
if (empty($data['idgenero'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El campo idgenero es obligatorio']);
    exit;
}

// Asignar valores
$idgenero = (int)$data['idgenero'];
$nombre = isset($data['nombre']) ? trim($data['nombre']) : null;
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
$idestatus = isset($data['idestatus']) ? (int)$data['idestatus'] : null;

try {
    // Verificar si el género existe
    $stmt = $pdo->prepare("SELECT idgenero FROM generos WHERE idgenero = :idgenero");
    $stmt->execute(['idgenero' => $idgenero]);

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        http_response_code(404);
        echo json_encode(['error' => 'El género no existe']);
        exit;
    }

    // Validar que al menos un campo esté presente para actualizar
    if ($nombre === null && $descripcion === null && $idestatus === null) {
        http_response_code(400);
        echo json_encode(['error' => 'No se proporcionaron campos para actualizar']);
        exit;
    }

    // Actualizar los campos proporcionados usando COALESCE
    $stmt = $pdo->prepare("
        UPDATE generos 
        SET nombre = COALESCE(:nombre, nombre), 
            descripcion = COALESCE(:descripcion, descripcion), 
            idestatus = COALESCE(:idestatus, idestatus) 
        WHERE idgenero = :idgenero
    ");

    $stmt->execute([
        'idgenero' => $idgenero,
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'idestatus' => $idestatus
    ]);

    // Respuesta exitosa
    echo json_encode(['message' => 'Género actualizado correctamente']);
} catch (Exception $e) {
    // Manejo de errores
    error_log($e->getMessage()); // Registrar el error en un archivo de logs
    http_response_code(500);
    echo json_encode(['error' => 'Ha ocurrido un error interno']);
}
?>
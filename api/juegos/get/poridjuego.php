<?php
require_once __DIR__ . '/../../../config/cors.php';
require_once __DIR__ . '/../../../config/db.php';
header('Content-Type: application/json');
// Obtener el idgenero de los parámetros de la URL
$idjuego = isset($_GET['idjuego']) ? (int)$_GET['idjuego'] : null;
if ($idjuego === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El campo idgenero es obligatorio']);
    exit;
}
try {
    // Preparar la consulta SQL de forma segura
    $stmt = $pdo->prepare("
        SELECT
            a.idjuego,
            a.idestatus,
            a.nombre AS nombre,
            a.descripcion,
            a.fechapublicacion,
            a.precio,
            a.imagen,
            b.idgenero,
            b.nombre AS genero,
            b.descripcion AS dgenero
        FROM
            juegos AS a
        INNER JOIN
            generos AS b ON a.idgenero = b.idgenero
        WHERE
            a.idjuego = :idjuego
    ");
    $stmt->execute(['idjuego' => $idjuego]);
    $juego = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($juego);
} catch (Exception $e) {
    // Registrar el error en los logs
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Ha ocurrido un error interno']);
}
?>
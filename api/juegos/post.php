<?php
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? htmlspecialchars(trim($_POST['nombre'])) : null;
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars(trim($_POST['descripcion'])) : null;
$idestatus = isset($_POST['idestatus']) ? (int)$_POST['idestatus'] : null;
$idgenero = isset($_POST['idgenero']) ? (int)$_POST['idgenero'] : null;
$fechapublicacion = isset($_POST['fechapublicacion']) ? $_POST['fechapublicacion'] : null;
$precio = isset($_POST['precio']) ? (float)$_POST['precio'] : null;
$valoracion = isset($_POST['valoracion']) ? (int)$_POST['valoracion'] : null;

// Manejo de la imagen
$imagen = $_FILES["imagen"];
$nombreImagen = $imagen["name"];
$tipoImagen = strtolower(pathinfo($nombreImagen, PATHINFO_EXTENSION));
$directorio = '../../img/';

if (in_array($tipoImagen, ["jpg", "jpeg", "png"])) {
    $idRegistro = uniqid();
    $nombreArchivo = $idRegistro . "." . $tipoImagen;
    $ruta = $directorio . $nombreArchivo;

    if (move_uploaded_file($imagen["tmp_name"], $ruta)) {
        try {
            // Preparar la consulta SQL
            $stmt = $pdo->prepare("INSERT INTO juegos (idestatus, idgenero, nombre, descripcion, fechapublicacion, precio, valoracion, imagen) VALUES (:idestatus, :idgenero, :nombre, :descripcion, :fechapublicacion, :precio, :valoracion, :imagen)");

            // Ejecutar la consulta con los valores proporcionados
            $stmt->execute([
                'idestatus' => $idestatus,
                'idgenero' => $idgenero,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'fechapublicacion' => $fechapublicacion,
                'precio' => $precio,
                'valoracion' => $valoracion,
                'imagen' => $nombreArchivo
            ]);

            // Respuesta exitosa
            echo json_encode(['message' => 'Juego creado correctamente']);
        } catch (Exception $e) {
            error_log($e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Ha ocurrido un error interno: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al subir la imagen']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de imagen no permitido']);
}
?>

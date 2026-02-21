<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/db.php';

if (!isset($_GET['slug'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Slug is required']);
    exit;
}

try {
    $pdo->exec("USE `$dbname`");

    $stmt = $pdo->prepare("SELECT * FROM productos WHERE slug = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$_GET['slug']]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        $producto['galeria'] = json_decode($producto['galeria'], true) ?: [];
        $producto['tags'] = json_decode($producto['tags'], true) ?: [];
        echo json_encode(['status' => 'success', 'data' => $producto]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
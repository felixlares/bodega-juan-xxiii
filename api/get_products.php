<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/db.php';

try {
    $pdo->exec("USE `$dbname`");

    $query = "SELECT id, titulo, slug, precio, precio_anterior, categoria, imagen_principal, destacado, stock FROM productos WHERE activo = 1";
    $params = [];

    if (isset($_GET['categoria']) && $_GET['categoria'] !== '') {
        $query .= " AND categoria = :categoria";
        $params[':categoria'] = $_GET['categoria'];
    }

    if (isset($_GET['q']) && $_GET['q'] !== '') {
        $query .= " AND titulo LIKE :q";
        $params[':q'] = '%' . $_GET['q'] . '%';
    }

    $query .= " ORDER BY destacado DESC, id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $productos]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
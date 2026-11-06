<?php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_api.php';
require_once dirname(__DIR__) . '/php/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$sql = "SELECT MAQ_ID, MAQ_TAG, MAQ_DESC, MAQ_IMPACTO_PRODUCAO FROM tab_maq ORDER BY MAQ_TAG ASC";
$result = $conn->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'data' => $data,
    'error' => null
]);

<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/auth_api.php';
require_once dirname(__DIR__, 2) . '/php/conexao.php';
require_once dirname(__DIR__, 2) . '/models/Indicadores.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$maq = $_GET['maq'] ?? null;
$inicio = $_GET['inicio'] ?? null;
$fim = $_GET['fim'] ?? null;
$setor = $_GET['setor'] ?? null;

$model = new Indicadores($conn);
$data = $model->getDisponibilidade($maq, $inicio, $fim, $setor);

echo json_encode([
    'success' => true,
    'data' => $data,
    'error' => null
]);

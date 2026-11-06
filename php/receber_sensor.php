<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

require_once dirname(__DIR__) . '/api/auth_api.php';
require_once __DIR__ . '/conexao.php';

$tag = trim((string)($_POST['tag_maq'] ?? ''));
$evento = strtoupper(trim((string)($_POST['evento'] ?? '')));
$eventosValidos = ['CONEXAO', 'DESCONEXAO', 'INPUT'];

if ($tag === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $tag) || !in_array($evento, $eventosValidos, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Dados do evento inválidos.']);
    exit;
}

$totalInputs = filter_input(INPUT_POST, 'total_inputs', FILTER_VALIDATE_INT);
$tempoConectado = filter_input(INPUT_POST, 'tempo_conectado', FILTER_VALIDATE_INT);
$totalInputs = ($totalInputs === false || $totalInputs === null || $totalInputs < 0) ? 0 : $totalInputs;
$tempoConectado = ($tempoConectado === false || $tempoConectado === null || $tempoConectado < 0) ? 0 : $tempoConectado;
$agora = date('Y-m-d H:i:s');

$tagStmt = $conn->prepare('SELECT MAQ_TAG FROM tab_maq WHERE MAQ_TAG = ?');
if (!$tagStmt) {
    error_log($conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Não foi possível registrar o evento.']);
    exit;
}
$tagStmt->bind_param('s', $tag);
$tagStmt->execute();
$tagExiste = $tagStmt->get_result()->fetch_assoc();
$tagStmt->close();
if (!$tagExiste) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Máquina não cadastrada.']);
    exit;
}

function responderErro($conn) {
    error_log($conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Não foi possível registrar o evento.']);
    exit;
}

if ($evento === 'CONEXAO') {
    $stmt = $conn->prepare('INSERT INTO tab_sensor_log (LOG_TAG_MAQ, LOG_EVENTO, LOG_TIMESTAMP, LOG_TOTAL_INPUTS, LOG_TEMPO_CONECTADO) VALUES (?, \'CONEXAO\', ?, 0, 0)');
    if (!$stmt) {
        responderErro($conn);
    }
    $stmt->bind_param('ss', $tag, $agora);
    if (!$stmt->execute()) {
        error_log($stmt->error);
        $stmt->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Não foi possível registrar o evento.']);
        exit;
    }
    $id = $stmt->insert_id;
    $stmt->close();
    echo json_encode(['success' => true, 'id' => $id, 'message' => 'Conexão registrada.']);
    $conn->close();
    exit;
}

$sel = $conn->prepare("SELECT LOG_ID FROM tab_sensor_log WHERE LOG_TAG_MAQ = ? AND LOG_EVENTO = 'CONEXAO' AND LOG_TIMESTAMP_FIM IS NULL ORDER BY LOG_ID DESC LIMIT 1");
if (!$sel) {
    responderErro($conn);
}
$sel->bind_param('s', $tag);
$sel->execute();
$aberta = $sel->get_result()->fetch_assoc();
$sel->close();

if ($aberta) {
    $id = (int)$aberta['LOG_ID'];
    if ($evento === 'DESCONEXAO') {
        $upd = $conn->prepare("UPDATE tab_sensor_log SET LOG_EVENTO = 'DESCONEXAO', LOG_TOTAL_INPUTS = ?, LOG_TEMPO_CONECTADO = ?, LOG_TIMESTAMP_FIM = ? WHERE LOG_ID = ?");
        if (!$upd) {
            responderErro($conn);
        }
        $upd->bind_param('iisi', $totalInputs, $tempoConectado, $agora, $id);
    } else {
        $upd = $conn->prepare('UPDATE tab_sensor_log SET LOG_TOTAL_INPUTS = ?, LOG_TEMPO_CONECTADO = ? WHERE LOG_ID = ?');
        if (!$upd) {
            responderErro($conn);
        }
        $upd->bind_param('iii', $totalInputs, $tempoConectado, $id);
    }
    if (!$upd->execute()) {
        error_log($upd->error);
        $upd->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Não foi possível registrar o evento.']);
        exit;
    }
    $upd->close();
    echo json_encode(['success' => true, 'message' => 'Conexão atualizada.']);
    $conn->close();
    exit;
}

if ($evento === 'DESCONEXAO') {
    $ins = $conn->prepare("INSERT INTO tab_sensor_log (LOG_TAG_MAQ, LOG_EVENTO, LOG_TIMESTAMP, LOG_TIMESTAMP_FIM, LOG_TOTAL_INPUTS, LOG_TEMPO_CONECTADO) VALUES (?, 'DESCONEXAO', ?, ?, ?, ?)");
    if (!$ins) {
        responderErro($conn);
    }
    $ins->bind_param('sssii', $tag, $agora, $agora, $totalInputs, $tempoConectado);
} else {
    $ins = $conn->prepare("INSERT INTO tab_sensor_log (LOG_TAG_MAQ, LOG_EVENTO, LOG_TIMESTAMP, LOG_TOTAL_INPUTS, LOG_TEMPO_CONECTADO) VALUES (?, 'CONEXAO', ?, ?, ?)");
    if (!$ins) {
        responderErro($conn);
    }
    $ins->bind_param('ssii', $tag, $agora, $totalInputs, $tempoConectado);
}
if (!$ins->execute()) {
    error_log($ins->error);
    $ins->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Não foi possível registrar o evento.']);
    exit;
}
$ins->close();
echo json_encode(['success' => true, 'message' => 'Sessão registrada.']);
$conn->close();

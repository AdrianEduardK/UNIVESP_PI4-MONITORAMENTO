<?php
require_once __DIR__ . '/auth.php';
verificarCSRF();
include __DIR__ . '/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/producao.php');
    exit;
}

$opId = filter_input(INPUT_POST, 'op_id', FILTER_VALIDATE_INT);
$opLinha = trim((string)($_POST['op_linha'] ?? ''));
$data = trim((string)($_POST['data'] ?? ''));
$inicio = trim((string)($_POST['inicio'] ?? ''));
$fim = trim((string)($_POST['fim'] ?? ''));
$operador = trim((string)($_POST['operador'] ?? ''));
$obs = trim((string)($_POST['obs'] ?? ''));
$opcoes = ['CONFORME', 'NÃO CONFORME', 'NÃO APLICÁVEL'];
$respostas = [];

$dataOk = DateTime::createFromFormat('!Y-m-d', $data);
$errosData = DateTime::getLastErrors();
$dataInvalida = !$dataOk || (is_array($errosData) && ((($errosData['warning_count'] ?? 0) > 0) || (($errosData['error_count'] ?? 0) > 0)));
$horasOk = preg_match('/^\d{2}:\d{2}$/', $inicio) && preg_match('/^\d{2}:\d{2}$/', $fim);
$tamanho = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';
$valido = $opId && $opId > 0
    && $opLinha !== '' && $tamanho($opLinha) <= 50
    && !$dataInvalida
    && $horasOk
    && $operador !== '' && $tamanho($operador) <= 100
    && $tamanho($obs) <= 5000;

for ($i = 1; $i <= 14 && $valido; $i++) {
    $valor = $_POST['Q' . $i] ?? '';
    if (!in_array($valor, $opcoes, true)) {
        $valido = false;
        break;
    }
    $respostas[] = $valor;
}

$destinoErro = '../pages/nr12_formulario.php?id=' . (int)$opId . '&error=1';
if (!$valido) {
    header('Location: ' . ($opId ? $destinoErro : '../pages/producao.php'));
    exit;
}

$opStmt = $conn->prepare('SELECT OP_ID FROM tab_op WHERE OP_ID = ?');
if (!$opStmt) {
    error_log($conn->error);
    header('Location: ' . $destinoErro);
    exit;
}
$opStmt->bind_param('i', $opId);
$opStmt->execute();
$opExiste = $opStmt->get_result()->fetch_assoc();
$opStmt->close();
if (!$opExiste) {
    header('Location: ../pages/producao.php');
    exit;
}

$check = $conn->prepare('SELECT ID_TAB_NR12 FROM tab_nr12 WHERE NR12_OP = ?');
if (!$check) {
    error_log($conn->error);
    header('Location: ' . $destinoErro);
    exit;
}
$check->bind_param('i', $opId);
$check->execute();
$check->store_result();
$existe = $check->num_rows > 0;
$check->close();

if ($existe) {
    $sql = 'UPDATE tab_nr12 SET
        NR12_DATA = ?, NR12_LINHA = ?, NR12_INICIO = ?, NR12_FIM = ?, NR12_OPERADOR = ?,
        Q1 = ?, Q2 = ?, Q3 = ?, Q4 = ?, Q5 = ?, Q6 = ?, Q7 = ?, Q8 = ?, Q9 = ?, Q10 = ?,
        Q11 = ?, Q12 = ?, Q13 = ?, Q14 = ?, NR12_OBS = ?
        WHERE NR12_OP = ?';
    $types = 'sssss' . str_repeat('s', 14) . 'si';
} else {
    $sql = 'INSERT INTO tab_nr12 (
        NR12_DATA, NR12_LINHA, NR12_INICIO, NR12_FIM, NR12_OPERADOR,
        Q1, Q2, Q3, Q4, Q5, Q6, Q7, Q8, Q9, Q10,
        Q11, Q12, Q13, Q14, NR12_OP, NR12_OBS
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $types = 'sssss' . str_repeat('s', 14) . 'is';
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log($conn->error);
    header('Location: ' . $destinoErro);
    exit;
}

if ($existe) {
    $stmt->bind_param(
        $types,
        $data, $opLinha, $inicio, $fim, $operador,
        $respostas[0], $respostas[1], $respostas[2], $respostas[3], $respostas[4],
        $respostas[5], $respostas[6], $respostas[7], $respostas[8], $respostas[9],
        $respostas[10], $respostas[11], $respostas[12], $respostas[13],
        $obs, $opId
    );
} else {
    $stmt->bind_param(
        $types,
        $data, $opLinha, $inicio, $fim, $operador,
        $respostas[0], $respostas[1], $respostas[2], $respostas[3], $respostas[4],
        $respostas[5], $respostas[6], $respostas[7], $respostas[8], $respostas[9],
        $respostas[10], $respostas[11], $respostas[12], $respostas[13],
        $opId, $obs
    );
}

if ($stmt->execute()) {
    $stmt->close();
    header('Location: ../pages/producao.php?success=1');
    exit;
}

error_log($stmt->error);
$stmt->close();
header('Location: ' . $destinoErro);
exit;

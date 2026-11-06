<?php
require_once __DIR__ . '/auth.php';
exigirManutencao();
verificarCSRF();
require_once __DIR__ . '/flash_messages.php';
require_once __DIR__ . '/conexao.php';

$tag = trim((string)($_POST['tag_maq'] ?? ''));
$voltar = '../pages/sensor_log.php';

if ($tag === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $tag)) {
    setFlashMessage('Tag da máquina inválida.', 'error');
    header('Location: ' . $voltar);
    exit;
}

$stmt = $conn->prepare('SELECT MAQ_TAG FROM tab_maq WHERE MAQ_TAG = ?');
$existe = false;
if ($stmt) {
    $stmt->bind_param('s', $tag);
    $stmt->execute();
    $existe = (bool)$stmt->get_result()->fetch_assoc();
    $stmt->close();
}
if (!$existe) {
    setFlashMessage('Máquina não cadastrada.', 'error');
    header('Location: ' . $voltar);
    exit;
}

$script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sensor_monitor.py';
$comando = 'start /B python ' . escapeshellarg($script) . ' --tag ' . escapeshellarg($tag);
pclose(popen($comando, 'r'));

setFlashMessage('Monitoramento iniciado para ' . $tag . '.', 'success');
header('Location: ' . $voltar);
exit;

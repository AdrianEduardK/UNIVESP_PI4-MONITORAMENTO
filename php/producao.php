<?php
require_once __DIR__ . '/auth.php';
include __DIR__ . '/conexao.php';

$hoje = date('Y-m-d');
$stmt = $conn->prepare('SELECT OP_ID, OP_LINHA FROM tab_op WHERE OP_DATA_PROG = ?');
if (!$stmt) {
    error_log($conn->error);
    echo 'Não foi possível consultar a produção.';
    exit;
}
$stmt->bind_param('s', $hoje);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($op = $result->fetch_assoc()) {
        $id = (int)$op['OP_ID'];
        echo '<p><a href="detalhes_op.php?id=' . $id . '">OP: ' . $id . ' - Linha: ' . htmlspecialchars($op['OP_LINHA']) . '</a></p>';
    }
} else {
    echo 'Nenhuma OP para hoje.';
}
$stmt->close();

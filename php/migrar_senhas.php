<?php
require_once __DIR__ . '/cli_only.php';
exigirCli();
require_once __DIR__ . '/conexao.php';

if (!$conn->query('ALTER TABLE tab_login MODIFY LOGIN_SENHA VARCHAR(255) NOT NULL')) {
    error_log($conn->error);
    fwrite(STDERR, "Nao foi possivel ajustar a coluna de senha.\n");
    exit(1);
}

$res = $conn->query('SELECT LOGIN_ID, LOGIN_SENHA FROM tab_login');
if (!$res) {
    error_log($conn->error);
    fwrite(STDERR, "Nao foi possivel ler os usuarios.\n");
    exit(1);
}

$upd = $conn->prepare('UPDATE tab_login SET LOGIN_SENHA = ? WHERE LOGIN_ID = ?');
if (!$upd) {
    error_log($conn->error);
    fwrite(STDERR, "Nao foi possivel preparar a atualizacao.\n");
    exit(1);
}

$migradas = 0;
while ($row = $res->fetch_assoc()) {
    $armazenada = (string)$row['LOGIN_SENHA'];
    $info = password_get_info($armazenada);
    if (!empty($info['algo'])) {
        continue;
    }
    $hash = password_hash($armazenada, PASSWORD_DEFAULT);
    $id = (int)$row['LOGIN_ID'];
    $upd->bind_param('si', $hash, $id);
    if ($upd->execute()) {
        $migradas++;
    }
}
$upd->close();
echo "Senhas migradas: {$migradas}\n";

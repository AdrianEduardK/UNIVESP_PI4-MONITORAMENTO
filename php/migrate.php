<?php
require_once __DIR__ . '/cli_only.php';
exigirCli();
require_once __DIR__ . '/conexao.php';

$sqlFile = dirname(__DIR__) . '/tabelasSQL/dump_completo_univesp_pi.sql';

if (!file_exists($sqlFile)) {
    die("Arquivo de dump nao encontrado.\n");
}

$sql = file_get_contents($sqlFile);

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Sucesso: Dump importado corretamente.\n";
} else {
    error_log($conn->error);
    echo "Erro na importacao. Consulte o log do PHP.\n";
}

$conn->close();

<?php
require_once __DIR__ . '/php/cli_only.php';
exigirCli();
include('php/conexao.php');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 1. Inserir máquinas de teste
    $maquinas = [
        "INSERT IGNORE INTO tab_maq (MAQ_TAG, MAQ_DESC, MAQ_ATIVO, MAQ_IMPACTO_PRODUCAO) VALUES ('STRESS-01', 'Máquina Quebradiça (Extrema)', 'Sim', 5)",
        "INSERT IGNORE INTO tab_maq (MAQ_TAG, MAQ_DESC, MAQ_ATIVO, MAQ_IMPACTO_PRODUCAO) VALUES ('STRESS-02', 'Máquina Perfeita (Sem quebra)', 'Sim', 2)",
        "INSERT IGNORE INTO tab_maq (MAQ_TAG, MAQ_DESC, MAQ_ATIVO, MAQ_IMPACTO_PRODUCAO) VALUES ('STRESS-03', 'Máquina Preventiva Atrasada', 'Sim', 4)"
    ];

    foreach($maquinas as $sql) {
        $conn->query($sql);
    }

    // 2. Inserir Ordens de Serviço (Limpar testes antigos primeiro)
    $conn->query("DELETE FROM tab_ordem_servico WHERE OS_TAG_MAQ LIKE 'STRESS-%'");

    // Cenario 1: Máquina Quebradiça - 150 corretivas de 1 hora
    for ($i=1; $i<=150; $i++) {
        $dataSol = date('Y-m-d H:i:s', strtotime("-$i days"));
        $dataFim = date('Y-m-d H:i:s', strtotime("-$i days + 1 hour"));
        $conn->query("INSERT INTO tab_ordem_servico (OS_TAG_MAQ, OS_LINHA, OS_TIPO_MANUT, OS_DATA_SOLICITACAO, OS_DATA_INICIO, OS_DATA_FIM, OS_STATUS, OS_CUSTO_PECAS, OS_CUSTO_MAO_OBRA) 
        VALUES ('STRESS-01', 'Linha Teste', 'CORRETIVA', '$dataSol', '$dataSol', '$dataFim', 'Fechado', 99999.99, 50000.00)");
    }

    // Cenario 2: Máquina Perfeita - MTBF "infinito", 0 corretivas, apenas 1 preventiva
    $conn->query("INSERT INTO tab_ordem_servico (OS_TAG_MAQ, OS_LINHA, OS_TIPO_MANUT, OS_DATA_SOLICITACAO, OS_DATA_INICIO, OS_DATA_FIM, OS_STATUS, OS_DATA_PLANEJADA, OS_CUSTO_PECAS) 
    VALUES ('STRESS-02', 'Linha Teste', 'PREVENTIVA', NOW(), NOW(), NOW(), 'Fechado', NOW(), 0)");

    // Cenario 3: Preventivas Muito Atrasadas e divisão por zero potencial
    $dataPlanejada = date('Y-m-d H:i:s', strtotime("-30 days"));
    $conn->query("INSERT INTO tab_ordem_servico (OS_TAG_MAQ, OS_LINHA, OS_TIPO_MANUT, OS_DATA_SOLICITACAO, OS_DATA_INICIO, OS_DATA_FIM, OS_STATUS, OS_DATA_PLANEJADA, OS_CUSTO_MAO_OBRA) 
    VALUES ('STRESS-03', 'Linha Teste', 'PREVENTIVA', NOW(), NOW(), NOW(), 'Fechado', '$dataPlanejada', 0)");

    echo "Sucesso! Foram geradas máquinas e dezenas de OS de estresse (STRESS-01, STRESS-02, STRESS-03).\n";

} catch (Exception $e) {
    echo "Erro durante o teste de estresse: " . $e->getMessage() . "\n";
}

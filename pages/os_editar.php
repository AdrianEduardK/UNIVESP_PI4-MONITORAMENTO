<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

// Verificar ID
if (!isset($_GET['id'])) {
    echo "<script>alert('ID não informado!'); window.location.href='os.php';</script>";
    exit;
}

$os_id = intval($_GET['id']);

// Atualização
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['OS_STATUS'];
    $recebido_por = $_POST['OS_RECEBIDO_POR'];
    $manutentor = $_POST['OS_MANUTENTOR'];
    $data_promessa = $_POST['OS_DATA_PROMESSA'] ?: null;
    $data_inicio = $_POST['OS_DATA_INICIO'] ?: null;
    $data_fim = $_POST['OS_DATA_FIM'] ?: null;
    $observacoes = $_POST['OS_OBSERVACOES'];
    $horas_plan = $_POST['OS_HORAS_PLAN'] ?: null;
    $assinatura = $_POST['OS_ASSINATURA'];
    $avaliacao_comentario = $_POST['OS_AVALIACAO_COMENTARIO'];
    $avaliacao_nota = $_POST['OS_AVALIACAO_NOTA'] ?: null;
    
    $tipo_manut = $_POST['OS_TIPO_MANUT'] ?? 'CORRETIVA';
    $custo_pecas = $_POST['OS_CUSTO_PECAS'] ? (float)$_POST['OS_CUSTO_PECAS'] : 0.00;
    $custo_mao_obra = $_POST['OS_CUSTO_MAO_OBRA'] ? (float)$_POST['OS_CUSTO_MAO_OBRA'] : 0.00;
    $data_planejada = !empty($_POST['OS_DATA_PLANEJADA']) ? $_POST['OS_DATA_PLANEJADA'] : null;

    $stmt = $conn->prepare("UPDATE tab_ordem_servico SET 
        OS_STATUS = ?,
        OS_RECEBIDO_POR = ?,
        OS_MANUTENTOR = ?,
        OS_DATA_PROMESSA = ?,
        OS_DATA_INICIO = ?,
        OS_DATA_FIM = ?,
        OS_OBSERVACOES = ?,
        OS_HORAS_PLAN = ?,
        OS_ASSINATURA = ?,
        OS_AVALIACAO_COMENTARIO = ?,
        OS_AVALIACAO_NOTA = ?,
        OS_TIPO_MANUT = ?,
        OS_CUSTO_PECAS = ?,
        OS_CUSTO_MAO_OBRA = ?,
        OS_DATA_PLANEJADA = ?
        WHERE OS_ID = ?");
    
    $stmt->bind_param("sssssssdssissddsi", 
        $status, $recebido_por, $manutentor, $data_promessa, $data_inicio, 
        $data_fim, $observacoes, $horas_plan, $assinatura, 
        $avaliacao_comentario, $avaliacao_nota, 
        $tipo_manut, $custo_pecas, $custo_mao_obra, $data_planejada, $os_id);

    if ($stmt->execute()) {
        echo "<script>alert('OS atualizada com sucesso!'); window.location.href='os.php';</script>";
    } else {
        echo "<script>alert('Erro ao atualizar: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Buscar dados
$stmt = $conn->prepare("SELECT * FROM tab_ordem_servico WHERE OS_ID = ?");
$stmt->bind_param("i", $os_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('OS não encontrada!'); window.location.href='os.php';</script>";
    exit;
}

$os = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Ordem de Serviço #<?php echo $os['OS_ID']; ?></title>
<link rel="stylesheet" href="../css/style.css">
<script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

<div class="container">
    <h2>Editar Ordem de Serviço #<?php echo $os['OS_ID']; ?></h2>

    <!-- Informações Somente Leitura -->
    <div class="form-section">
        <h3>Informações da Solicitação (Somente Leitura)</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
            <div>
                <label>Solicitante:</label>
                <input type="text" class="readonly" readonly value="<?php echo htmlspecialchars($os['OS_SOLICITANTE']); ?>">
            </div>
            <div>
                <label>Data Solicitação:</label>
                <input type="text" class="readonly" readonly value="<?php echo date('d/m/Y H:i', strtotime($os['OS_DATA_SOLICITACAO'])); ?>">
            </div>
            <div>
                <label>Tag Máquina:</label>
                <input type="text" class="readonly" readonly value="<?php echo htmlspecialchars($os['OS_TAG_MAQ']); ?>">
            </div>
            <div>
                <label>Linha:</label>
                <input type="text" class="readonly" readonly value="<?php echo htmlspecialchars($os['OS_LINHA']); ?>">
            </div>
            <div>
                <label>Classificação:</label>
                <input type="text" class="readonly" readonly value="<?php echo htmlspecialchars($os['OS_CLASSIFICACAO']); ?>">
            </div>
            <div>
                <label>Situação:</label>
                <input type="text" class="readonly" readonly value="<?php echo htmlspecialchars($os['OS_SITUACAO']); ?>">
            </div>
        </div>

        <div style="margin-top: 20px;">
            <label>Descrição do Pedido:</label>
            <textarea class="readonly" readonly rows="3"><?php echo htmlspecialchars($os['OS_DESC_PEDIDO']); ?></textarea>
        </div>

        <div style="margin-top: 20px; display: flex; gap: 20px;">
            <div class="gut-info" style="flex: 1;">
                <strong>Matriz GUT:</strong><br>
                Gravidade: <?php echo $os['OS_GRAVIDADE']; ?> | 
                Urgência: <?php echo $os['OS_URGENCIA']; ?> | 
                Tendência: <?php echo $os['OS_TENDENCIA']; ?><br>
                <span style="color: var(--danger-color); font-size: 18px; font-weight: bold;">Soma: <?php echo $os['OS_SOMA_GUT']; ?></span>
            </div>
            
            <?php if($os['OS_ID_OP']): ?>
            <div class="os-info" style="flex: 1;">
                <strong>OP Relacionada:</strong><br>
                <span style="font-size: 18px; font-weight: bold;">#<?php echo $os['OS_ID_OP']; ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formulário de Edição -->
    <form method="POST">
        
        <div class="form-section">
            <h3>Status e Atribuição</h3>
            
            <label>Status:</label>
            <select name="OS_STATUS" required>
                <option value="Aberto" <?php echo $os['OS_STATUS'] == 'Aberto' ? 'selected' : ''; ?>>Aberto</option>
                <option value="Em Andamento" <?php echo $os['OS_STATUS'] == 'Em Andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                <option value="Aguardando Peças" <?php echo $os['OS_STATUS'] == 'Aguardando Peças' ? 'selected' : ''; ?>>Aguardando Peças</option>
                <option value="Concluído" <?php echo $os['OS_STATUS'] == 'Concluído' ? 'selected' : ''; ?>>Concluído</option>
                <option value="Cancelado" <?php echo $os['OS_STATUS'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
            </select>

            <label>Recebido Por:</label>
            <input type="text" name="OS_RECEBIDO_POR" value="<?php echo htmlspecialchars($os['OS_RECEBIDO_POR']); ?>">

            <label>Manutentor Responsável:</label>
            <input type="text" name="OS_MANUTENTOR" value="<?php echo htmlspecialchars($os['OS_MANUTENTOR']); ?>">
        </div>

        <div class="form-section">
            <h3>Datas e Prazos</h3>
            
            <label>Data Promessa:</label>
            <input type="datetime-local" name="OS_DATA_PROMESSA" 
                   value="<?php echo $os['OS_DATA_PROMESSA'] ? date('Y-m-d\TH:i', strtotime($os['OS_DATA_PROMESSA'])) : ''; ?>">

            <label>Data Início:</label>
            <input type="datetime-local" name="OS_DATA_INICIO" 
                   value="<?php echo $os['OS_DATA_INICIO'] ? date('Y-m-d\TH:i', strtotime($os['OS_DATA_INICIO'])) : ''; ?>">

            <label>Data Fim:</label>
            <input type="datetime-local" name="OS_DATA_FIM" 
                   value="<?php echo $os['OS_DATA_FIM'] ? date('Y-m-d\TH:i', strtotime($os['OS_DATA_FIM'])) : ''; ?>">
        </div>

        <div class="form-section">
            <h3>Execução</h3>
            
            <label>Horas Planejadas:</label>
            <input type="number" step="0.01" name="OS_HORAS_PLAN" 
                   value="<?php echo $os['OS_HORAS_PLAN']; ?>" 
                   placeholder="Ex: 2.5">

            <label>Tipo de Manutenção:</label>
            <select name="OS_TIPO_MANUT" id="OS_TIPO_MANUT" required onchange="toggleDataPlanejada()">
                <option value="CORRETIVA" <?php echo ($os['OS_TIPO_MANUT'] ?? 'CORRETIVA') == 'CORRETIVA' ? 'selected' : ''; ?>>Corretiva</option>
                <option value="PREVENTIVA" <?php echo ($os['OS_TIPO_MANUT'] ?? '') == 'PREVENTIVA' ? 'selected' : ''; ?>>Preventiva</option>
                <option value="PREDITIVA" <?php echo ($os['OS_TIPO_MANUT'] ?? '') == 'PREDITIVA' ? 'selected' : ''; ?>>Preditiva</option>
            </select>

            <div id="div_data_planejada" style="display: <?php echo ($os['OS_TIPO_MANUT'] ?? '') == 'PREVENTIVA' ? 'block' : 'none'; ?>;">
                <label>Data Planejada (Preventiva):</label>
                <input type="date" name="OS_DATA_PLANEJADA" id="OS_DATA_PLANEJADA" value="<?php echo $os['OS_DATA_PLANEJADA'] ?? ''; ?>">
            </div>

            <label>Custo Estimado Peças (R$):</label>
            <input type="number" step="0.01" name="OS_CUSTO_PECAS" value="<?php echo $os['OS_CUSTO_PECAS'] ?? '0.00'; ?>">

            <label>Custo Mão de Obra (R$):</label>
            <input type="number" step="0.01" name="OS_CUSTO_MAO_OBRA" value="<?php echo $os['OS_CUSTO_MAO_OBRA'] ?? '0.00'; ?>">

            <label>Observações do Manutentor:</label>
            <textarea name="OS_OBSERVACOES"><?php echo htmlspecialchars($os['OS_OBSERVACOES']); ?></textarea>

            <label>Assinatura:</label>
            <input type="text" name="OS_ASSINATURA" value="<?php echo htmlspecialchars($os['OS_ASSINATURA']); ?>">

            <div style="margin-top:20px;">
                <a href="os_apontamento.php?id_os=<?php echo $os['OS_ID']; ?>" class="btn btn-success">
                    🕒 Apontar Horas
                </a>
            </div>
        </div>

        <div class="form-section">
            <h3>Avaliação</h3>
            
            <label>Nota (1-5):</label>
            <select name="OS_AVALIACAO_NOTA">
                <option value="">Sem avaliação</option>
                <option value="1" <?php echo $os['OS_AVALIACAO_NOTA'] == 1 ? 'selected' : ''; ?>>1 - Péssimo</option>
                <option value="2" <?php echo $os['OS_AVALIACAO_NOTA'] == 2 ? 'selected' : ''; ?>>2 - Ruim</option>
                <option value="3" <?php echo $os['OS_AVALIACAO_NOTA'] == 3 ? 'selected' : ''; ?>>3 - Regular</option>
                <option value="4" <?php echo $os['OS_AVALIACAO_NOTA'] == 4 ? 'selected' : ''; ?>>4 - Bom</option>
                <option value="5" <?php echo $os['OS_AVALIACAO_NOTA'] == 5 ? 'selected' : ''; ?>>5 - Excelente</option>
            </select>

            <label>Comentário da Avaliação:</label>
            <textarea name="OS_AVALIACAO_COMENTARIO"><?php echo htmlspecialchars($os['OS_AVALIACAO_COMENTARIO']); ?></textarea>
        </div>

        <div class="button-group" style="margin-top: 30px; display: flex; gap: 15px;">
            <button type="submit" class="btn">💾 Salvar Alterações</button>
            <a href="os.php" class="btn btn-outline">Cancelar</a>
        </div>

    </form>
</div>

<script>
function toggleDataPlanejada() {
    var tipo = document.getElementById('OS_TIPO_MANUT').value;
    var div = document.getElementById('div_data_planejada');
    var input = document.getElementById('OS_DATA_PLANEJADA');
    if (tipo === 'PREVENTIVA') {
        div.style.display = 'block';
        input.required = true;
    } else {
        div.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
</script>

</body>
</html>

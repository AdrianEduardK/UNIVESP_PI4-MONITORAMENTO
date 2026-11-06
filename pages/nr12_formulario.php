<?php
require_once '../php/auth.php';
include('../php/conexao.php');

// Verificar se o ID da OP foi passado
if (!isset($_GET['id'])) {
    die("ID da OP não fornecido");
}

$op_id = $_GET['id'];

// Buscar dados da OP no banco de dados
$query = "SELECT OP_ID, OP_LINHA, OP_DATA_PROG FROM tab_op WHERE OP_ID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $op_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("OP não encontrada");
}

$op_data = mysqli_fetch_assoc($result);

// Verificar se já existe um checklist NR12 para esta OP
$check_query = "SELECT * FROM TAB_NR12 WHERE NR12_OP = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $op_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);
$nr12_data = mysqli_fetch_assoc($check_result);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Checklist NR12 - Preenchimento</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

<div class="container">
    <h2>Checklist Segurança NR12</h2>
    <h3 style="color: var(--text-muted); font-size: 16px; margin-bottom: 30px;">
        OP <?php echo htmlspecialchars($op_data['OP_ID']); ?> | Linha <?php echo htmlspecialchars($op_data['OP_LINHA']); ?> | Data: <?php echo date('d/m/Y', strtotime($op_data['OP_DATA_PROG'])); ?>
    </h3>
    
    <form method="POST" action="../php/salvar_nr12.php">
        <?php echo campoCsrf(); ?>
        <input type="hidden" name="op_id" value="<?php echo htmlspecialchars($op_data['OP_ID']); ?>">
        <input type="hidden" name="op_linha" value="<?php echo htmlspecialchars($op_data['OP_LINHA']); ?>">
        <input type="hidden" name="data" value="<?php echo htmlspecialchars($op_data['OP_DATA_PROG']); ?>">
        
        <div class="filtros">
            <div>
                <label>Horário Início:</label>
                <input type="time" name="inicio" value="<?php echo isset($nr12_data['NR12_INICIO']) ? substr($nr12_data['NR12_INICIO'], 0, 5) : ''; ?>" required>
            </div>
            <div>
                <label>Horário Fim:</label>
                <input type="time" name="fim" value="<?php echo isset($nr12_data['NR12_FIM']) ? substr($nr12_data['NR12_FIM'], 0, 5) : ''; ?>" required>
            </div>
            <div>
                <label>Operador:</label>
                <input type="text" name="operador" placeholder="Nome do operador..." value="<?php echo isset($nr12_data['NR12_OPERADOR']) ? htmlspecialchars($nr12_data['NR12_OPERADOR']) : ''; ?>" required>
            </div>
        </div>

        <div class="table-responsive">
            <table class="checklist-table">
                <thead>
                    <tr>
                        <th>ITEM (PERGUNTA)</th>
                        <th>CONFORME</th>
                        <th>NÃO CONFORME</th>
                        <th>NÃO APLICÁVEL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $questions = [
                        1 => "HÁ PROTEÇÕES NA MÁQUINA DE OPERAÇÃO?",
                        2 => "HÁ ALGUMA PARTE MÓVEL QUE ESTÁ EXPOSTA (CORRENTES, CORREIAS, ENGRENAGENS, ETC)?",
                        3 => "EXISTEM PROTEÇÕES FIXAS E/OU MÓVEIS QUANDO O ACESSO A UMA ZONA DE PERIGO FOR REQUERIDO UMA OU MAIS VEZES DURANTE A OPERAÇÃO?",
                        4 => "HÁ RISCO DE CHOQUE ELÉTRICO EM VIRTUDE DE CONEXÃO EXPOSTA?",
                        5 => "TODOS OS FIOS OU QUAISQUER COMPONENTES ELÉTRICOS POTENCIALMENTE PERIGOSOS FORAM DEVIDAMENTE IDENTIFICADOS?",
                        6 => "OS COMANDOS DO QUADRO ELÉTRICO ESTÃO SINALIZADOS / IDENTIFICADOS?",
                        7 => "HÁ ALGUMA FIAÇÃO SOBRE O PISO POR ONDE OS TRABALHADORES TÊM DE PASSAR?",
                        8 => "OS TRABALHADORES ESTÃO TREINADOS A FIM DE GARANTIR A PRONTA DISPONIBILIDADE DA MÁQUINA E IDENTIFICAR PROBLEMAS INTERROMPENDO SUA ATIVIDADE PARA SOLICITAR A MANUTENÇÃO DA MESMA PARA A ÁREA COMPETENTE?",
                        9 => "OS CONTROLES DE PARTIDA, PARADA E A CHAVE GERAL FORAM TESTADOS PARA DESLIGAMENTO TOTAL DO MAQUINÁRIO?",
                        10 => "A MÁQUINA POSSUI DISPOSITIVO DE PARADA DE EMERGÊNCIA E OS MESMOS ENCONTRAM-SE AO ALCANCE DAS MÃOS?",
                        11 => "OS DISPOSITIVOS DE PARTIDA, ACIONAMENTO E PARADA PODEM SER ACIONADOS OU DESLIGADOS POR OUTRA PESSOA QUE NÃO SEJA O OPERADOR?",
                        12 => "VOCÊ ESTÁ TREINADO A COMO UTILIZAR O MAQUINÁRIO DE FORMA APROPRIADA?",
                        13 => "VOCÊ RECEBEU O TREINAMENTO A COMO REAGIR CORRETAMENTE A QUESTÕES DE SEGURANÇA?",
                        14 => "OS PROCEDIMENTOS OPERACIONAIS SÃO DOCUMENTADOS NO LOCAL ONDE A MÁQUINA ESTÁ INSTALADA?"
                    ];

                    foreach ($questions as $num => $question) {
                        echo "<tr>";
                        echo "<td class='question' style='max-width: 500px; line-height: 1.5; white-space: normal; font-weight: 500;'>$num. $question</td>";
                        
                        $options = ['CONFORME', 'NÃO CONFORME', 'NÃO APLICÁVEL'];
                        foreach ($options as $option) {
                            $bd_val = isset($nr12_data["Q$num"]) ? $nr12_data["Q$num"] : '';
                            $isChecked = false;
                            if ($option == 'CONFORME' && $bd_val == 'CONFORME') $isChecked = true;
                            if ($option == 'NÃO CONFORME' && $bd_val == 'NÃO CONFORME') $isChecked = true;
                            if ($option == 'NÃO APLICÁVEL' && $bd_val == 'NÃO APLICÁVEL') $isChecked = true;
                            
                            $checked = $isChecked ? 'checked' : '';
                            echo "<td class='options' style='text-align: center;'><input type='radio' name='Q$num' value='$option' $checked required></td>";
                        }
                        
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="form-section" style="margin-top: 30px;">
            <label>Observações Adicionais (Opcional):</label>
            <textarea name="obs" rows="4" placeholder="Alguma irregularidade encontrada? Escreva aqui..."><?php echo isset($nr12_data['NR12_OBS']) ? htmlspecialchars($nr12_data['NR12_OBS']) : ''; ?></textarea>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center;">
            <a href="producao.php" class="btn btn-outline" style="border: 2px solid var(--input-border); color: var(--text-color); background: transparent;">← Cancelar</a>
            <button type="submit" class="btn btn-success" style="padding: 15px 40px; font-size: 18px;">💾 Salvar Checklist</button>
        </div>
    </form>
</div>
</body>
</html>

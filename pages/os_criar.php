<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

// Inserção sempre como nova OS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados do formulário
    $solicitante = $_POST['OS_SOLICITANTE'];
    $status = $_POST['OS_STATUS'];
    $tag_maq = $_POST['OS_TAG_MAQ'];
    $linha = $_POST['OS_LINHA'];
    $classificacao = $_POST['OS_CLASSIFICACAO'];
    $situacao = $_POST['OS_SITUACAO'];
    $data_solicitacao = $_POST['OS_DATA_SOLICITACAO'];
    $desc_pedido = $_POST['OS_DESC_PEDIDO'];
    $gravidade = $_POST['OS_GRAVIDADE'];
    $urgencia = $_POST['OS_URGENCIA'];
    $tendencia = $_POST['OS_TENDENCIA'];
    $soma_gut = $_POST['OS_SOMA_GUT'];
    $observacoes = $_POST['OS_OBSERVACOES'];
    $horas_plan = $_POST['OS_HORAS_PLAN'];
    $tipo_manut = $_POST['OS_TIPO_MANUT'] ?? 'CORRETIVA';
    $custo_pecas = $_POST['OS_CUSTO_PECAS'] ? (float)$_POST['OS_CUSTO_PECAS'] : 0.00;
    $custo_mao_obra = $_POST['OS_CUSTO_MAO_OBRA'] ? (float)$_POST['OS_CUSTO_MAO_OBRA'] : 0.00;
    $data_planejada = !empty($_POST['OS_DATA_PLANEJADA']) ? $_POST['OS_DATA_PLANEJADA'] : null;
 

    // Inserir no banco
    $stmt = $conn->prepare("INSERT INTO tab_ordem_servico 
        (OS_SOLICITANTE, OS_STATUS, OS_TAG_MAQ, OS_LINHA, OS_CLASSIFICACAO, OS_SITUACAO, OS_DATA_SOLICITACAO, OS_DESC_PEDIDO, OS_GRAVIDADE, OS_URGENCIA, OS_TENDENCIA, OS_SOMA_GUT, OS_OBSERVACOES, OS_HORAS_PLAN, OS_TIPO_MANUT, OS_CUSTO_PECAS, OS_CUSTO_MAO_OBRA, OS_DATA_PLANEJADA)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssiiiidssds", $solicitante, $status, $tag_maq, $linha, $classificacao, $situacao, $data_solicitacao, $desc_pedido, $gravidade, $urgencia, $tendencia, $soma_gut, $observacoes, $horas_plan, $tipo_manut, $custo_pecas, $custo_mao_obra, $data_planejada);

    if ($stmt->execute()) {
        require_once '../php/flash_messages.php';
        setFlashMessage("Ordem de Serviço criada com sucesso!", "success");
        header("Location: os.php");
        exit;
    } else {
        require_once '../php/flash_messages.php';
        setFlashMessage("Erro ao criar Ordem de Serviço.", "error");
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Criar Nova Ordem de Serviço</title>
<link rel="stylesheet" href="../css/style.css">

<script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>




<div class="container">
    <h2>Criar Nova Ordem de Serviço</h2>

    <form method="POST">
        <label>Solicitante:</label>
        <input type="text" name="OS_SOLICITANTE" value="<?php echo $_SESSION['nome']; ?>" readonly>


        <label>Status:</label>
        <input type="text" name="OS_STATUS" value="Aberto" readonly>


        <label>Tag Máquina:</label>
        <select name="OS_TAG_MAQ" required>
            <option value="">Selecione</option>
            <?php
            $sqlMaq = "SELECT MAQ_TAG FROM tab_maq ORDER BY MAQ_TAG";
            $resultMaq = $conn->query($sqlMaq);

            if ($resultMaq->num_rows > 0) {
                while ($row = $resultMaq->fetch_assoc()) {
                    echo '<option value="'.$row['MAQ_TAG'].'">'.$row['MAQ_TAG'].'</option>';
                }
            }    
            ?>
        </select>

        <label>Linha:</label>
        <select name="OS_LINHA" required>
            <option value="">Selecione</option>
            <?php
            $sqlLinhas = "SELECT linha_desc FROM tab_linhas ORDER BY linha_desc";
            $resultLinhas = $conn->query($sqlLinhas);

            if ($resultLinhas->num_rows > 0) {
                while ($row = $resultLinhas->fetch_assoc()) {
                    echo '<option value="'.$row['linha_desc'].'">'.$row['linha_desc'].'</option>';
                }
            }    
            ?>
        </select>


        <label>Classificação:</label>
        <select name="OS_CLASSIFICACAO" required>
            <option value="">Selecione</option>
            <option value="ELETRICA">ELETRICA</option>
            <option value="PREDIAL">PREDIAL</option>
            <option value="MECANICA">MECANICA</option>
        </select>

        <label>Situação:</label>
        <select name="OS_SITUACAO" required>
            <option value="Linha parada">Linha parada</option>
            <option value="Linha segue em atuação">Linha segue em atuação</option>
        </select>   

        <label>Data Solicitação:</label>
        <input type="datetime-local" name="OS_DATA_SOLICITACAO" value="<?php echo date('Y-m-d\TH:i'); ?>" readonly>

        <label>Descrição do Pedido:</label>
        <textarea name="OS_DESC_PEDIDO"></textarea>

        <label>Gravidade:</label>
        <select name="OS_GRAVIDADE" id="OS_GRAVIDADE" onchange="calcularGUT()">
            <option value="">Selecione</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>

        <label>Urgência:</label>
        <select name="OS_URGENCIA" id="OS_URGENCIA" onchange="calcularGUT()">
            <option value="">Selecione</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>

        <label>Tendência:</label>
        <select name="OS_TENDENCIA" id="OS_TENDENCIA" onchange="calcularGUT()">
            <option value="">Selecione</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>

        <label>Soma GUT:</label>
        <input type="number" name="OS_SOMA_GUT" id="OS_SOMA_GUT" readonly>

        <label>Observações:</label>
        <textarea name="OS_OBSERVACOES"></textarea>

        <label>Horas Planejadas:</label>
        <input type="number" step="0.01" name="OS_HORAS_PLAN">

        <label>Tipo de Manutenção:</label>
        <select name="OS_TIPO_MANUT" id="OS_TIPO_MANUT" required onchange="toggleDataPlanejada()">
            <option value="CORRETIVA">Corretiva</option>
            <option value="PREVENTIVA">Preventiva</option>
            <option value="PREDITIVA">Preditiva</option>
        </select>

        <div id="div_data_planejada" style="display: none;">
            <label>Data Planejada (Preventiva):</label>
            <input type="date" name="OS_DATA_PLANEJADA" id="OS_DATA_PLANEJADA">
        </div>

        <label>Custo Estimado Peças (R$):</label>
        <input type="number" step="0.01" name="OS_CUSTO_PECAS" value="0.00">

        <label>Custo Mão de Obra (R$):</label>
        <input type="number" step="0.01" name="OS_CUSTO_MAO_OBRA" value="0.00">

       

        <button type="submit">Salvar</button>
    </form>

    <script>
    function calcularGUT() {
        // Pegar valores
        let gravidade = parseInt(document.getElementById('OS_GRAVIDADE').value) || 0;
        let urgencia = parseInt(document.getElementById('OS_URGENCIA').value) || 0;
        let tendencia = parseInt(document.getElementById('OS_TENDENCIA').value) || 0;

        // Somar
        let soma = gravidade + urgencia + tendencia;

        // Atualizar campo Soma GUT
        document.getElementById('OS_SOMA_GUT').value = soma;
    }

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

    

</div>

</body>
</html>


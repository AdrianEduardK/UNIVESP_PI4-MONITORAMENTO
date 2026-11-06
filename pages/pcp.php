<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

$query_maquinas = "SELECT MAQ_TAG, MAQ_DESC FROM TAB_MAQ WHERE MAQ_ATIVO = 'ATIVO'";
$result_maquinas = mysqli_query($conn, $query_maquinas) or die("Erro: " . mysqli_error($conn));

$query_produtos = "SELECT PROD_ID, PROD_DESC FROM TAB_PRODUTOS";
$result_produtos = mysqli_query($conn, $query_produtos) or die("Erro: " . mysqli_error($conn));

$query_linhas = "SELECT LINHA_ID, LINHA_DESC FROM TAB_LINHAS";
$result_linhas = mysqli_query($conn, $query_linhas) or die("Erro: " . mysqli_error($conn));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>PCP - Cadastro de OP</title>
    <link rel="stylesheet" href="../css/style.css">
    
    <script>
        function filterProducts() {
            const input = document.getElementById('productFilter').value.toLowerCase();
            const options = document.getElementById('productSelect').options;
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                const text = option.text.toLowerCase();
                option.style.display = text.includes(input) ? '' : 'none';
            }
        }

        function cadastrarOP(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('formOP'));

            fetch('../php/cadastrar_op.php', {
                method: 'POST',
                body: formData
            })
            .then(resp => resp.text())
            .then(data => {
                if (data.trim() === 'sucesso') {
                    document.getElementById('notificacao').style.display = 'block';
                    document.getElementById('formOP').reset();
                } else {
                    alert('Parabéns! ' + data);
                }
            })
            .catch(error => alert('Erro de conexão: ' + error));
        }
    </script>
<script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>



    

    <div class="container">
        <div id="notificacao" class="notification" style="display: none; background: var(--success-color); color: white; padding: 15px; border-radius: 8px; margin-bottom: 25px; text-align: center; font-weight: bold;">✅ Ordem de Produção cadastrada com sucesso!</div>

        <h2>Cadastro de Ordem de Produção (PCP)</h2>

        <form id="formOP" onsubmit="cadastrarOP(event)">
            <div>
                <label for="programador">Programador:</label>
                <input type="text" name="programador" id="programador" value="<?php echo $_SESSION['nome']; ?>" readonly required>
            </div>

            <div>
                <label for="data_prog">Data Programada:</label>
                <input type="date" name="data_prog" id="data_prog" required>
            </div>

            <div>
                <label for="qtd_produto">Quantidade:</label>
                <input type="number" name="qtd_produto" id="qtd_produto" required>
            </div>

            <div>
                <label for="linha">Selecionar Linha Produtiva:</label>
                <select id="linha" name="linha" required>
                    <option value="">Selecione uma linha</option>
                    <?php while ($rowLinha = mysqli_fetch_assoc($result_linhas)) { ?>
                        <option value="<?php echo $rowLinha['LINHA_DESC']; ?>"><?php echo $rowLinha['LINHA_DESC']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label for="productFilter">Filtrar por Produto:</label>
                <input type="text" id="productFilter" onkeyup="filterProducts()" placeholder="Digite para filtrar...">
            </div>

            <div>
                <label for="productSelect">Selecionar Produto:</label>
                <select id="productSelect" name="id_produto" required>
                    <option value="">Selecione um produto</option>
                    <?php while ($rowProd = mysqli_fetch_assoc($result_produtos)) { ?>
                        <option value="<?php echo $rowProd['PROD_ID']; ?>"><?php echo $rowProd['PROD_DESC']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div>
                <label for="tag_maq">Selecionar Máquina:</label>
                <select id="tag_maq" name="tag_maq">
                    <option value="">Todas</option>
                    <?php while ($rowMaq = mysqli_fetch_assoc($result_maquinas)) { ?>
                        <option value="<?php echo $rowMaq['MAQ_TAG']; ?>"><?php echo $rowMaq['MAQ_TAG']; ?></option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit">Cadastrar OP</button>
        </form>
    </div>
</body>
</html>


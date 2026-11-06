<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

$mensagem = "";

// Inserir novo produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produto_desc'])) {
    $desc = $_POST['produto_desc'];
    $stmt = $conn->prepare("INSERT INTO TAB_PRODUTOS (PROD_DESC) VALUES (?)");
    if ($stmt) {
        $stmt->bind_param("s", $desc);
        if ($stmt->execute()) {
            $mensagem = "✅ Produto cadastrado com sucesso!";
        } else {
            $mensagem = "❌ Erro ao cadastrar produto: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensagem = "❌ Erro na preparação da query: " . $conn->error;
    }
}

// Inserir estrutura
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_final']) && isset($_POST['id_necessario'])) {
    $id_final = $_POST['id_final'];
    $id_necessario = $_POST['id_necessario'];
    $stmt = $conn->prepare("INSERT INTO TAB_ESTRUTURA_PROD (ESTRUTURA_ID_FINAL, ESTRUTURA_ID_NECESSARIO) VALUES (?, ?)");
    if ($stmt) {
        $stmt->bind_param("ii", $id_final, $id_necessario);
        if ($stmt->execute()) {
            $mensagem = "✅ Estrutura cadastrada com sucesso!";
        } else {
            $mensagem = "❌ Erro ao cadastrar estrutura: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensagem = "❌ Erro na preparação da query: " . $conn->error;
    }
}

// Buscar produtos para o select
$produtos = $conn->query("SELECT PROD_ID, PROD_DESC FROM TAB_PRODUTOS");

// Buscar estrutura existente se um produto final foi selecionado
$estrutura = [];
if (isset($_GET['produto_selecionado'])) {
    $idSelecionado = intval($_GET['produto_selecionado']);
    $query = "
        SELECT ep.ESTRUTURA_ID, p.PROD_DESC
        FROM TAB_ESTRUTURA_PROD ep
        JOIN TAB_PRODUTOS p ON p.PROD_ID = ep.ESTRUTURA_ID_NECESSARIO
        WHERE ep.ESTRUTURA_ID_FINAL = ?";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $idSelecionado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estrutura = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Cadastro de Produto</title>
<link rel="stylesheet" href="../css/style.css">

<script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>



<div class="container">

    

    <?php if ($mensagem): ?>
        <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <h2>Cadastro de Produto</h2>
    <form method="POST">
        <label for="produto_desc">Descrição do Produto:</label>
        <input type="text" name="produto_desc" required>
        <button type="submit">Cadastrar Produto</button>
    </form>

    <hr>

    <h2>Cadastro de Estrutura de Produto</h2>
    <form method="POST">
        <label for="id_final">Produto Final:</label>
        <select name="id_final" id="id_final" onchange="buscarEstrutura()" required>
            <option value="">Selecione</option>
            <?php
            $produtos->data_seek(0);
            while ($row = $produtos->fetch_assoc()) {
                $selected = (isset($idSelecionado) && $idSelecionado == $row['PROD_ID']) ? 'selected' : '';
                echo "<option value='{$row['PROD_ID']}' $selected>" . htmlspecialchars($row['PROD_DESC']) . "</option>";
            }
            ?>
        </select>

        <label for="id_necessario">Produto Necessário:</label>
        <select name="id_necessario" required>
            <?php
            $produtos->data_seek(0);
            while ($row = $produtos->fetch_assoc()) {
                echo "<option value='{$row['PROD_ID']}'>" . htmlspecialchars($row['PROD_DESC']) . "</option>";
            }
            ?>
        </select>

        <button type="submit">Cadastrar Estrutura</button>
    </form>

    <?php if (!empty($estrutura)): ?>
        <h3>Produtos já relacionados com este Produto Final:</h3>
        <table>
            <tr>
                <th>ID da Estrutura</th>
                <th>Produto Necessário</th>
            </tr>
            <?php foreach ($estrutura as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['ESTRUTURA_ID']) ?></td>
                    <td><?= htmlspecialchars($item['PROD_DESC']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<script>
function buscarEstrutura() {
    const idFinal = document.getElementById('id_final').value;
    if (idFinal) {
        window.location.href = '?produto_selecionado=' + idFinal;
    }
}
</script>
</body>
</html>


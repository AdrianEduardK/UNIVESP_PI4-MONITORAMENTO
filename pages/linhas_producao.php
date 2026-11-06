<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');

// Inserção ou Edição
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['linha_desc'])) {
    $desc = $_POST['linha_desc'];
    
    if (isset($_POST['linha_id']) && $_POST['linha_id'] != '') {
        $id = intval($_POST['linha_id']);
        $stmt = $conn->prepare("UPDATE TAB_LINHAS SET LINHA_DESC = ? WHERE LINHA_ID = ?");
        $stmt->bind_param("si", $desc, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO TAB_LINHAS (LINHA_DESC) VALUES (?)");
        $stmt->bind_param("s", $desc);
        $stmt->execute();
    }
    header("Location: linhas_producao.php");
    exit;
}

// Exclusão
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $stmt = $conn->prepare("DELETE FROM TAB_LINHAS WHERE LINHA_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: linhas_producao.php");
    exit;
}

// Modo Edição
$linhaEdit = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $conn->prepare("SELECT * FROM TAB_LINHAS WHERE LINHA_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) $linhaEdit = $res->fetch_assoc();
}

// Buscar linhas
$linhas = $conn->query("SELECT * FROM TAB_LINHAS ORDER BY LINHA_ID DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Linhas de Produção</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

    <div class="container">
        <h2><?php echo $linhaEdit ? 'Editar Linha de Produção' : 'Cadastrar Linha de Produção'; ?></h2>
      
        <div class="form-section">
            <form method="POST">
                <?php if ($linhaEdit): ?>
                    <input type="hidden" name="linha_id" value="<?php echo $linhaEdit['LINHA_ID']; ?>">
                <?php endif; ?>
                
                <label for="linha_desc">Descrição da Linha:</label>
                <input type="text" name="linha_desc" id="linha_desc" value="<?php echo $linhaEdit ? htmlspecialchars($linhaEdit['LINHA_DESC']) : ''; ?>" required placeholder="Ex: Linha 1">
                
                <div style="margin-top: 15px;">
                    <button type="submit" class="btn btn-success"><?php echo $linhaEdit ? 'Atualizar Linha' : 'Cadastrar Linha'; ?></button>
                    <?php if ($linhaEdit): ?>
                        <a href="linhas_producao.php" class="btn btn-outline" style="margin-left: 10px;">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <hr>
        
        <h3 style="margin-top: 40px;">Linhas Cadastradas</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($linha = $linhas->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $linha['LINHA_ID']; ?></td>
                            <td><?php echo htmlspecialchars($linha['LINHA_DESC']); ?></td>
                            <td>
                                <a href="linhas_producao.php?editar=<?php echo $linha['LINHA_ID']; ?>" class="btn" style="padding: 6px 12px; font-size: 13px;">Editar</a>
                                <a href="?excluir=<?php echo $linha['LINHA_ID']; ?>" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px; margin-left: 5px;" onclick="return confirm('Tem certeza que deseja excluir esta linha?')">Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

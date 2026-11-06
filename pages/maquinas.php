<?php
require_once '../php/auth.php';
exigirManutencao();
include('../php/conexao.php');
require_once '../controllers/MaquinaController.php';

// Instancia o Controller (que puxará o Model automaticamente)
$controller = new MaquinaController($conn);

// Manda o Controller lidar com qualquer requisição POST (Salvar) ou GET (Excluir)
$controller->handleRequest();

// Se houver um $_GET['editar'], o controller retorna os dados daquela máquina
$maqEdit = $controller->getEditData();

// Traz todas as máquinas para desenhar a tabela HTML abaixo
$todasMaquinas = $controller->getAllData();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Máquinas</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/theme.js"></script>
</head>
<body>
<?php include_once dirname(__DIR__) . '/php/header.php'; ?>

    <div class="container">
        <h2><?php echo $maqEdit ? 'Editar Máquina' : 'Cadastrar Máquina'; ?></h2>
        
        <div class="form-section">
            <form method="POST">
                <?php if ($maqEdit): ?>
                    <input type="hidden" name="editar_id" value="<?php echo $maqEdit['MAQ_ID']; ?>">
                <?php endif; ?>

                <label>Descrição:</label>
                <input type="text" name="MAQ_DESC" value="<?php echo $maqEdit ? htmlspecialchars($maqEdit['MAQ_DESC']) : ''; ?>" required placeholder="Ex: Torno CNC">

                <label>TAG:</label>
                <input type="text" name="MAQ_TAG" value="<?php echo $maqEdit ? htmlspecialchars($maqEdit['MAQ_TAG']) : ''; ?>" required placeholder="Ex: TR-01">

                <label>Ativo:</label>
                <select name="MAQ_ATIVO">
                    <option value="ATIVO" <?php if ($maqEdit && $maqEdit['MAQ_ATIVO'] == 'ATIVO') echo 'selected'; ?>>ATIVO</option>
                    <option value="INATIVO" <?php if ($maqEdit && $maqEdit['MAQ_ATIVO'] == 'INATIVO') echo 'selected'; ?>>INATIVO</option>
                </select>

                <div style="margin-top: 15px;">
                    <button type="submit" class="btn btn-success"><?php echo $maqEdit ? 'Atualizar Máquina' : 'Cadastrar Máquina'; ?></button>
                    <?php if ($maqEdit): ?>
                        <a href="maquinas.php" class="btn btn-outline" style="margin-left: 10px;">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <hr>
        
        <h3 style="margin-top: 40px;">Máquinas Cadastradas</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>TAG</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($todasMaquinas as $row) { ?>
                        <tr>
                            <td><?php echo $row['MAQ_ID']; ?></td>
                            <td><?php echo htmlspecialchars($row['MAQ_DESC']); ?></td>
                            <td><?php echo htmlspecialchars($row['MAQ_TAG']); ?></td>
                            <td>
                                <?php if ($row['MAQ_ATIVO'] == 'ATIVO'): ?>
                                    <span style="color: var(--success-color); font-weight: bold;">ATIVO</span>
                                <?php else: ?>
                                    <span style="color: var(--danger-color); font-weight: bold;">INATIVO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="maquinas.php?editar=<?php echo $row['MAQ_ID']; ?>" class="btn" style="padding: 6px 12px; font-size: 13px;">Editar</a>
                                <a href="?excluir=<?php echo $row['MAQ_ID']; ?>" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px; margin-left: 5px;" onclick="return confirm('Tem certeza que deseja excluir esta máquina?');">Excluir</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

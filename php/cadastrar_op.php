<?php
require_once 'auth.php';
exigirManutencao();
include 'conexao.php';

$data_prog = $_POST['data_prog'] ?? '';
$id_produto = $_POST['id_produto'] ?? '';
$qtd_produto = $_POST['qtd_produto'] ?? '';
$linha = $_POST['linha'] ?? '';
$programador = $_POST['programador'] ?? '';
$tag_maq = $_POST['tag_maq'] ?? '';

// Inserção com prepared statement
$sql = "INSERT INTO TAB_OP (OP_DATA_PROG, OP_ID_PRODUTO, OP_QTD_PRODUTO, OP_LINHA, OP_PROGRAMADOR, OP_TAG_MAQ) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisss", $data_prog, $id_produto, $qtd_produto, $linha, $programador, $tag_maq);

if ($stmt->execute()) {
    echo "sucesso"; // Retornando apenas a string para o JS tratar no frontend
} else {
    echo "Erro ao cadastrar OP: " . $stmt->error;
}
$stmt->close();
?>


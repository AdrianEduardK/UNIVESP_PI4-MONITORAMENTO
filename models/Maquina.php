<?php
// Classe de Modelo (Model) para lidar exclusivamente com o Banco de Dados.
// Nenhuma regra de negócio ou HTML deve entrar aqui.

class Maquina {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM TAB_MAQ ORDER BY MAQ_ID DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM TAB_MAQ WHERE MAQ_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($desc, $tag, $ativo) {
        $stmt = $this->conn->prepare("INSERT INTO TAB_MAQ (MAQ_DESC, MAQ_TAG, MAQ_ATIVO) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $desc, $tag, $ativo);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Erro no DB (Create Maquina): " . $stmt->error);
            return false;
        }
    }

    public function update($id, $desc, $tag, $ativo) {
        $stmt = $this->conn->prepare("UPDATE TAB_MAQ SET MAQ_DESC = ?, MAQ_TAG = ?, MAQ_ATIVO = ? WHERE MAQ_ID = ?");
        $stmt->bind_param("sssi", $desc, $tag, $ativo, $id);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Erro no DB (Update Maquina): " . $stmt->error);
            return false;
        }
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM TAB_MAQ WHERE MAQ_ID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Erro no DB (Delete Maquina): " . $stmt->error);
            return false;
        }
    }
}
?>

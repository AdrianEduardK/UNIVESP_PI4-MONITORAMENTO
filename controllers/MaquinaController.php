<?php
// Classe de Controle (Controller)
// Recebe as requisições (POST/GET), chama o Model e decide para onde redirecionar (View).

require_once dirname(__DIR__) . '/models/Maquina.php';

class MaquinaController {
    private $maquinaModel;

    public function __construct($conn) {
        $this->maquinaModel = new Maquina($conn);
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->save($_POST);
        }

        if (isset($_GET['excluir'])) {
            $this->delete($_GET['excluir']);
        }
    }

    private function save($data) {
        $desc = $data['MAQ_DESC'] ?? '';
        $tag = $data['MAQ_TAG'] ?? '';
        $ativo = $data['MAQ_ATIVO'] ?? '';

        if (isset($data['editar_id']) && $data['editar_id'] != '') {
            $id = intval($data['editar_id']);
            $success = $this->maquinaModel->update($id, $desc, $tag, $ativo);
        } else {
            $success = $this->maquinaModel->create($desc, $tag, $ativo);
        }

        if ($success) {
            require_once dirname(__DIR__) . '/php/flash_messages.php';
            setFlashMessage("Máquina salva com sucesso!", "success");
            header("Location: maquinas.php");
            exit();
        } else {
            require_once dirname(__DIR__) . '/php/flash_messages.php';
            setFlashMessage("Ocorreu um erro interno ao salvar a máquina.", "error");
            header("Location: maquinas.php");
            exit();
        }
    }

    private function delete($id) {
        $id = intval($id);
        $this->maquinaModel->delete($id);
        header("Location: maquinas.php");
        exit();
    }

    public function getEditData() {
        if (isset($_GET['editar'])) {
            return $this->maquinaModel->getById(intval($_GET['editar']));
        }
        return null;
    }

    public function getAllData() {
        return $this->maquinaModel->getAll();
    }
}
?>

<?php
require_once dirname(__DIR__) . '/models/Indicadores.php';

class IndicadoresController {
    private $model;
    private $filtros;

    public function __construct($db) {
        $this->model = new Indicadores($db);
        $maq = trim((string)($_GET['maq'] ?? ''));
        $setor = trim((string)($_GET['setor'] ?? ''));
        $cortar = function_exists('mb_substr') ? 'mb_substr' : 'substr';
        $this->filtros = [
            'maq' => $cortar($maq, 0, 50),
            'setor' => $cortar($setor, 0, 100),
            'data_inicio' => IndicadoresCalculo::dataValida((string)($_GET['data_inicio'] ?? '')) ?? '',
            'data_fim' => IndicadoresCalculo::dataValida((string)($_GET['data_fim'] ?? '')) ?? '',
        ];
    }

    public function getFiltros() {
        return $this->filtros;
    }

    public function handleRequest() {
        $maq = $this->filtros['maq'];
        $setor = $this->filtros['setor'];
        $inicio = $this->filtros['data_inicio'];
        $fim = $this->filtros['data_fim'];

        $mtbf = $this->model->getMTBF($maq, $inicio, $fim, $setor);
        $mttr = $this->model->getMTTR($maq, $inicio, $fim, $setor);
        $disp = $this->model->getDisponibilidade($maq, $inicio, $fim, $setor);
        $custo = $this->model->getCustoTotal($maq, $inicio, $fim, $setor);
        $prev = $this->model->getPercentualPreventivaNoPrazo($maq, $inicio, $fim, $setor);
        $paradas = $this->model->getParadasNaoPlanejadas($maq, $inicio, $fim, $setor);
        $ranking = $this->model->getRankingCriticidade(10, $inicio, $fim, $maq, $setor);
        $evolucao = $this->model->getEvolucaoMensal($maq, $inicio, $fim, $setor);

        return [
            'mtbf' => $mtbf,
            'mttr' => $mttr,
            'disponibilidade' => $disp,
            'custo' => $custo,
            'preventiva_prazo' => $prev,
            'paradas' => $paradas,
            'ranking' => $ranking,
            'evolucao' => $evolucao,
            'setores' => $this->model->getSetores(),
            'resumo' => [
                'mtbfMedio' => $this->arredondar($this->calcularMediaGlobal($mtbf, 'MTBF_HORAS')),
                'mttrMedio' => $this->arredondar($this->calcularMediaGlobal($mttr, 'MTTR_HORAS')),
                'dispMedia' => $this->arredondar($this->calcularMediaGlobal($disp, 'DISPONIBILIDADE')),
                'custoTotal' => round($this->calcularSomaGlobal($custo, 'CUSTO_TOTAL'), 2),
                'prevMedia' => $this->arredondar($this->calcularMediaGlobal($prev, 'PERCENTUAL')),
                'paradasTotal' => (int)$this->calcularSomaGlobal($paradas, 'N_PARADAS'),
            ],
        ];
    }

    private function arredondar($valor) {
        return $valor === null ? null : round($valor, 2);
    }

    private function calcularMediaGlobal($arrayData, $key) {
        if (empty($arrayData)) {
            return null;
        }
        $soma = 0;
        $count = 0;
        foreach ($arrayData as $item) {
            if (isset($item[$key]) && $item[$key] !== null) {
                $soma += (float)$item[$key];
                $count++;
            }
        }
        return $count > 0 ? $soma / $count : null;
    }

    private function calcularSomaGlobal($arrayData, $key) {
        if (empty($arrayData)) {
            return 0;
        }
        $soma = 0;
        foreach ($arrayData as $item) {
            if (isset($item[$key])) {
                $soma += (float)$item[$key];
            }
        }
        return $soma;
    }
}

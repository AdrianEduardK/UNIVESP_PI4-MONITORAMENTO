<?php
require_once __DIR__ . '/IndicadoresCalculo.php';

class Indicadores {
    private $conn;
    private $cacheFalhas = [];

    const PESO_FALHAS = 0.3;
    const PESO_DISPONIBILIDADE = 0.4;
    const PESO_IMPACTO = 0.3;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function normalizarData($data) {
        if ($data === null) {
            return null;
        }
        return IndicadoresCalculo::dataValida((string)$data);
    }

    private function buildWhereClause($tagMaq, $dataInicio, $dataFim, $setor, $prefix = 'WHERE') {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($tagMaq)) {
            $where[] = 'OS_TAG_MAQ = ?';
            $params[] = $tagMaq;
            $types .= 's';
        }
        if (!empty($setor)) {
            $where[] = 'OS_LINHA = ?';
            $params[] = $setor;
            $types .= 's';
        }
        if (!empty($dataInicio)) {
            $where[] = 'DATE(OS_DATA_SOLICITACAO) >= ?';
            $params[] = $dataInicio;
            $types .= 's';
        }
        if (!empty($dataFim)) {
            $where[] = 'DATE(OS_DATA_SOLICITACAO) <= ?';
            $params[] = $dataFim;
            $types .= 's';
        }

        $sql = !empty($where) ? ' ' . $prefix . ' ' . implode(' AND ', $where) : '';
        return ['sql' => $sql, 'types' => $types, 'params' => $params];
    }

    private function fetchAll($sql, $types, $params) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log('Erro de prepare: ' . $this->conn->error);
            return [];
        }
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            error_log('Erro de execute: ' . $stmt->error);
            $stmt->close();
            return [];
        }
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    private function periodoEfetivo($tagMaq, $dataInicio, $dataFim, $setor) {
        $ini = $this->normalizarData($dataInicio);
        $fim = $this->normalizarData($dataFim);
        if ($ini && $fim && $ini > $fim) {
            [$ini, $fim] = [$fim, $ini];
        }
        if ($ini && !$fim) {
            $fim = date('Y-m-d');
            if ($ini > $fim) {
                $fim = $ini;
            }
        } elseif ($fim && !$ini) {
            $d = new DateTime($fim);
            $d->modify('-29 days');
            $ini = $d->format('Y-m-d');
        } elseif (!$ini && !$fim) {
            $where = $this->buildWhereClause($tagMaq, null, null, $setor, 'WHERE');
            $sql = 'SELECT MIN(DATE(OS_DATA_SOLICITACAO)) AS ini, MAX(DATE(OS_DATA_SOLICITACAO)) AS fim
                    FROM tab_ordem_servico' . $where['sql'];
            $rows = $this->fetchAll($sql, $where['types'], $where['params']);
            $ini = $this->normalizarData($rows[0]['ini'] ?? null);
            $fim = $this->normalizarData($rows[0]['fim'] ?? null);
        }
        return [$ini, $fim, IndicadoresCalculo::horasCalendario($ini, $fim)];
    }

    private function getFalhasAgregadas($tagMaq, $dataInicio, $dataFim, $setor) {
        $chave = json_encode([$tagMaq, $dataInicio, $dataFim, $setor]);
        if (isset($this->cacheFalhas[$chave])) {
            return $this->cacheFalhas[$chave];
        }

        [$ini, $fim, $horas] = $this->periodoEfetivo($tagMaq, $dataInicio, $dataFim, $setor);
        $where = $this->buildWhereClause($tagMaq, $ini, $fim, $setor, 'AND');
        $sql = "SELECT OS_TAG_MAQ,
                    COUNT(*) AS N_FALHAS,
                    SUM(CASE WHEN OS_STATUS = 'Fechado'
                              AND OS_DATA_INICIO IS NOT NULL
                              AND OS_DATA_FIM IS NOT NULL
                              AND OS_DATA_FIM >= OS_DATA_INICIO
                         THEN TIMESTAMPDIFF(MINUTE, OS_DATA_INICIO, OS_DATA_FIM) ELSE 0 END) AS MINUTOS_REPARO,
                    SUM(CASE WHEN OS_STATUS = 'Fechado'
                              AND OS_DATA_INICIO IS NOT NULL
                              AND OS_DATA_FIM IS NOT NULL
                              AND OS_DATA_FIM >= OS_DATA_INICIO
                         THEN 1 ELSE 0 END) AS N_REPAROS
                FROM tab_ordem_servico
                WHERE OS_TIPO_MANUT = 'CORRETIVA' " . $where['sql'] . "
                GROUP BY OS_TAG_MAQ";

        $rows = $this->fetchAll($sql, $where['types'], $where['params']);
        $dados = [];
        foreach ($rows as $row) {
            $calc = IndicadoresCalculo::indicadoresDoRecorte(
                $horas ?? 0.0,
                (int)$row['N_FALHAS'],
                (int)$row['MINUTOS_REPARO'],
                (int)$row['N_REPAROS']
            );
            $dados[] = [
                'OS_TAG_MAQ' => $row['OS_TAG_MAQ'],
                'N_FALHAS' => $calc['n_falhas'],
                'N_REPAROS' => $calc['n_reparos'],
                'MTBF_HORAS' => $calc['mtbf'] === null ? null : round($calc['mtbf'], 2),
                'MTTR_HORAS' => $calc['mttr'] === null ? null : round($calc['mttr'], 2),
                'DISPONIBILIDADE' => $calc['disponibilidade'] === null ? null : round($calc['disponibilidade'], 2),
                'HORAS_TOTAL' => $calc['horas_calendario'],
                'HORAS_OPERACAO' => round($calc['horas_operacao'], 2),
                'HORAS_PARADA' => round($calc['horas_parada'], 2),
            ];
        }
        $this->cacheFalhas[$chave] = $dados;
        return $dados;
    }

    public function getMTTR($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $dados = [];
        foreach ($this->getFalhasAgregadas($tagMaq, $dataInicio, $dataFim, $setor) as $row) {
            if ($row['MTTR_HORAS'] === null) {
                continue;
            }
            $dados[] = [
                'OS_TAG_MAQ' => $row['OS_TAG_MAQ'],
                'MTTR_HORAS' => $row['MTTR_HORAS'],
            ];
        }
        return $dados;
    }

    public function getMTBF($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $dados = [];
        foreach ($this->getFalhasAgregadas($tagMaq, $dataInicio, $dataFim, $setor) as $row) {
            $dados[] = [
                'OS_TAG_MAQ' => $row['OS_TAG_MAQ'],
                'MTBF_HORAS' => $row['MTBF_HORAS'],
                'N_FALHAS' => $row['N_FALHAS'],
                'HORAS_TOTAL' => $row['HORAS_TOTAL'],
                'HORAS_OPERACAO' => $row['HORAS_OPERACAO'],
                'HORAS_PARADA' => $row['HORAS_PARADA'],
            ];
        }
        return $dados;
    }

    public function getDisponibilidade($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $dados = [];
        foreach ($this->getFalhasAgregadas($tagMaq, $dataInicio, $dataFim, $setor) as $row) {
            $dados[] = [
                'OS_TAG_MAQ' => $row['OS_TAG_MAQ'],
                'DISPONIBILIDADE' => $row['DISPONIBILIDADE'],
            ];
        }
        return $dados;
    }

    public function getCustoTotal($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $ini = $this->normalizarData($dataInicio);
        $fim = $this->normalizarData($dataFim);
        if ($ini && $fim && $ini > $fim) {
            [$ini, $fim] = [$fim, $ini];
        }
        $where = $this->buildWhereClause($tagMaq, $ini, $fim, $setor, 'WHERE');
        $sql = 'SELECT OS_TAG_MAQ, SUM(COALESCE(OS_CUSTO_PECAS, 0) + COALESCE(OS_CUSTO_MAO_OBRA, 0)) AS CUSTO_TOTAL
                FROM tab_ordem_servico' . $where['sql'] . '
                GROUP BY OS_TAG_MAQ';
        return $this->fetchAll($sql, $where['types'], $where['params']);
    }

    public function getPercentualPreventivaNoPrazo($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $ini = $this->normalizarData($dataInicio);
        $fim = $this->normalizarData($dataFim);
        if ($ini && $fim && $ini > $fim) {
            [$ini, $fim] = [$fim, $ini];
        }
        $where = $this->buildWhereClause($tagMaq, $ini, $fim, $setor, 'AND');
        $sql = "SELECT OS_TAG_MAQ,
                    COUNT(OS_ID) AS TOTAL_PREVENTIVAS,
                    SUM(CASE WHEN OS_DATA_FIM <= OS_DATA_PLANEJADA THEN 1 ELSE 0 END) AS PREVENTIVAS_NO_PRAZO
                FROM tab_ordem_servico
                WHERE OS_TIPO_MANUT = 'PREVENTIVA' AND OS_STATUS = 'Fechado' " . $where['sql'] . "
                GROUP BY OS_TAG_MAQ";
        $dados = [];
        foreach ($this->fetchAll($sql, $where['types'], $where['params']) as $row) {
            $total = (int)$row['TOTAL_PREVENTIVAS'];
            $noPrazo = (int)$row['PREVENTIVAS_NO_PRAZO'];
            $dados[] = [
                'OS_TAG_MAQ' => $row['OS_TAG_MAQ'],
                'PERCENTUAL' => $total > 0 ? round(($noPrazo / $total) * 100.0, 2) : 100.0,
                'TOTAL' => $total,
                'NO_PRAZO' => $noPrazo,
            ];
        }
        return $dados;
    }

    public function getParadasNaoPlanejadas($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $dados = [];
        foreach ($this->getFalhasAgregadas($tagMaq, $dataInicio, $dataFim, $setor) as $row) {
            $dados[] = [
                'OS_TAG_MAQ' => $row['OS_TAG_MAQ'],
                'N_PARADAS' => $row['N_FALHAS'],
            ];
        }
        return $dados;
    }

    public function getEvolucaoMensal($tagMaq = null, $dataInicio = null, $dataFim = null, $setor = null) {
        $ini = $this->normalizarData($dataInicio);
        $fim = $this->normalizarData($dataFim);
        if ($ini && $fim && $ini > $fim) {
            [$ini, $fim] = [$fim, $ini];
        }
        $where = $this->buildWhereClause($tagMaq, $ini, $fim, $setor, 'WHERE');
        $sql = "SELECT DATE_FORMAT(OS_DATA_SOLICITACAO, '%Y-%m') AS mes,
                    SUM(CASE WHEN OS_TIPO_MANUT = 'CORRETIVA' THEN 1 ELSE 0 END) AS N_FALHAS,
                    SUM(CASE WHEN OS_TIPO_MANUT = 'CORRETIVA'
                              AND OS_STATUS = 'Fechado'
                              AND OS_DATA_INICIO IS NOT NULL
                              AND OS_DATA_FIM IS NOT NULL
                              AND OS_DATA_FIM >= OS_DATA_INICIO
                         THEN TIMESTAMPDIFF(MINUTE, OS_DATA_INICIO, OS_DATA_FIM) ELSE 0 END) AS MINUTOS_REPARO,
                    SUM(CASE WHEN OS_TIPO_MANUT = 'CORRETIVA'
                              AND OS_STATUS = 'Fechado'
                              AND OS_DATA_INICIO IS NOT NULL
                              AND OS_DATA_FIM IS NOT NULL
                              AND OS_DATA_FIM >= OS_DATA_INICIO
                         THEN 1 ELSE 0 END) AS N_REPAROS,
                    SUM(COALESCE(OS_CUSTO_PECAS, 0) + COALESCE(OS_CUSTO_MAO_OBRA, 0)) AS CUSTO
                FROM tab_ordem_servico" . $where['sql'] . "
                GROUP BY DATE_FORMAT(OS_DATA_SOLICITACAO, '%Y-%m')
                ORDER BY mes";

        $saida = [];
        foreach ($this->fetchAll($sql, $where['types'], $where['params']) as $row) {
            $horas = IndicadoresCalculo::horasNoRecorte($row['mes'], $ini, $fim);
            if ($horas === null) {
                continue;
            }
            $calc = IndicadoresCalculo::indicadoresDoRecorte(
                $horas,
                (int)$row['N_FALHAS'],
                (int)$row['MINUTOS_REPARO'],
                (int)$row['N_REPAROS']
            );
            $saida[] = [
                'mes' => $row['mes'],
                'rotulo' => substr($row['mes'], 5, 2) . '/' . substr($row['mes'], 0, 4),
                'paradas' => $calc['n_falhas'],
                'mttr' => $calc['mttr'] === null ? null : round($calc['mttr'], 2),
                'mtbf' => $calc['mtbf'] === null ? null : round($calc['mtbf'], 2),
                'disponibilidade' => $calc['disponibilidade'] === null ? null : round($calc['disponibilidade'], 2),
                'custo' => round((float)$row['CUSTO'], 2),
            ];
        }
        return $saida;
    }

    public function getSetores() {
        $sql = "SELECT setor FROM (
                    SELECT LINHA_DESC AS setor FROM tab_linhas
                    UNION
                    SELECT OS_LINHA FROM tab_ordem_servico
                    WHERE OS_LINHA IS NOT NULL AND OS_LINHA <> ''
                ) linhas
                ORDER BY setor";
        $result = $this->conn->query($sql);
        if (!$result) {
            error_log($this->conn->error);
            return [];
        }
        $setores = [];
        while ($row = $result->fetch_assoc()) {
            $setores[] = $row['setor'];
        }
        return $setores;
    }

    public function getRankingCriticidade($limite = 10, $dataInicio = null, $dataFim = null, $tagMaq = null, $setor = null) {
        $limite = max(1, min(100, (int)$limite));
        $falhas = $this->getFalhasAgregadas($tagMaq, $dataInicio, $dataFim, $setor);
        $mapa = [];
        foreach ($falhas as $d) {
            $mapa[$d['OS_TAG_MAQ']] = $d;
        }

        $impacto = [];
        $resMaq = $this->conn->query('SELECT MAQ_TAG, MAQ_IMPACTO_PRODUCAO FROM tab_maq');
        if ($resMaq) {
            while ($m = $resMaq->fetch_assoc()) {
                $impacto[$m['MAQ_TAG']] = (float)$m['MAQ_IMPACTO_PRODUCAO'];
            }
        }

        if (!empty($tagMaq)) {
            $tags = [$tagMaq];
        } elseif (!empty($setor) || $this->normalizarData($dataInicio) || $this->normalizarData($dataFim)) {
            $ini = $this->normalizarData($dataInicio);
            $fim = $this->normalizarData($dataFim);
            if ($ini && $fim && $ini > $fim) {
                [$ini, $fim] = [$fim, $ini];
            }
            $where = $this->buildWhereClause($tagMaq, $ini, $fim, $setor, 'WHERE');
            $tags = [];
            foreach ($this->fetchAll('SELECT DISTINCT OS_TAG_MAQ FROM tab_ordem_servico' . $where['sql'], $where['types'], $where['params']) as $row) {
                $tags[] = $row['OS_TAG_MAQ'];
            }
        } else {
            $tags = array_keys($impacto);
        }

        $ranking = [];
        foreach ($tags as $tag) {
            $d = $mapa[$tag] ?? null;
            $nFalhas = $d['N_FALHAS'] ?? 0;
            $disp = $d['DISPONIBILIDADE'] ?? ($nFalhas === 0 ? 100.0 : null);
            $dispScore = $disp === null ? 0.0 : (float)$disp;
            $imp = $impacto[$tag] ?? 1.0;
            $score = ($nFalhas * self::PESO_FALHAS) + ((100 - $dispScore) * self::PESO_DISPONIBILIDADE) + ($imp * self::PESO_IMPACTO);
            $ranking[] = [
                'OS_TAG_MAQ' => $tag,
                'N_FALHAS' => $nFalhas,
                'MTTR' => $d['MTTR_HORAS'] ?? null,
                'DISPONIBILIDADE' => $disp,
                'IMPACTO' => $imp,
                'SCORE' => round($score, 2),
            ];
        }

        usort($ranking, function ($a, $b) {
            return $b['SCORE'] <=> $a['SCORE'];
        });
        return array_slice($ranking, 0, $limite);
    }
}

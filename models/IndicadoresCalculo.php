<?php
/**
 * Cálculo puro dos indicadores de manutenção.
 *
 * Regra (EN 15341 e NBR 5462), também descrita em docs/indicadores.md:
 * - Falha: ordem de serviço corretiva (parada não planejada).
 * - Tempo de reparo: duração das corretivas fechadas, em minutos convertidos para horas.
 *   A preventiva e a preditiva não entram no MTTR.
 * - MTTR = soma dos tempos de reparo / número de reparos concluídos.
 * - Tempo de funcionamento = horas de calendário do período − tempo de reparo.
 * - MTBF = tempo de funcionamento / número de falhas. Sem falhas, o MTBF não se aplica (nulo).
 * - Disponibilidade = MTBF / (MTBF + MTTR). Sem falhas, 100%. Com MTBF igual a zero, 0%.
 */
class IndicadoresCalculo {

    public static function dataValida($data) {
        if (!is_string($data) || $data === '') {
            return null;
        }
        $dt = DateTime::createFromFormat('!Y-m-d', $data);
        $erros = DateTime::getLastErrors();
        $temErro = is_array($erros) && ((($erros['warning_count'] ?? 0) > 0) || (($erros['error_count'] ?? 0) > 0));
        if (!$dt || $temErro) {
            return null;
        }
        return $dt->format('Y-m-d');
    }

    public static function horasCalendario($inicio, $fim) {
        $inicio = self::dataValida(is_string($inicio) ? $inicio : '');
        $fim = self::dataValida(is_string($fim) ? $fim : '');
        if (!$inicio || !$fim || $inicio > $fim) {
            return null;
        }
        $de = new DateTime($inicio);
        $ate = new DateTime($fim);
        return ((int)$de->diff($ate)->days + 1) * 24.0;
    }

    public static function horasNoRecorte($anoMes, $filtroInicio, $filtroFim) {
        if (!is_string($anoMes) || !preg_match('/^\d{4}-\d{2}$/', $anoMes)) {
            return null;
        }
        $inicioMes = self::dataValida($anoMes . '-01');
        if (!$inicioMes) {
            return null;
        }
        $fimMes = (new DateTime($inicioMes))->modify('last day of this month')->format('Y-m-d');
        $de = $inicioMes;
        $ate = $fimMes;
        $filtroInicio = self::dataValida(is_string($filtroInicio) ? $filtroInicio : '');
        $filtroFim = self::dataValida(is_string($filtroFim) ? $filtroFim : '');
        if ($filtroInicio && $filtroInicio > $de) {
            $de = $filtroInicio;
        }
        if ($filtroFim && $filtroFim < $ate) {
            $ate = $filtroFim;
        }
        return self::horasCalendario($de, $ate);
    }

    public static function mttr($minutosReparo, $nReparos) {
        $nReparos = (int)$nReparos;
        if ($nReparos <= 0) {
            return null;
        }
        return ((int)$minutosReparo / 60.0) / $nReparos;
    }

    public static function mtbf($horasCalendario, $horasParada, $nFalhas) {
        $nFalhas = (int)$nFalhas;
        if ($nFalhas <= 0 || $horasCalendario === null) {
            return null;
        }
        $operacao = (float)$horasCalendario - (float)$horasParada;
        if ($operacao < 0) {
            $operacao = 0.0;
        }
        return $operacao / $nFalhas;
    }

    public static function disponibilidade($mtbf, $mttr, $nFalhas) {
        $nFalhas = (int)$nFalhas;
        if ($nFalhas <= 0) {
            return 100.0;
        }
        if ($mtbf === null) {
            return null;
        }
        if ((float)$mtbf <= 0.0) {
            return 0.0;
        }
        if ($mttr === null) {
            return null;
        }
        $denominador = (float)$mtbf + (float)$mttr;
        if ($denominador <= 0.0) {
            return 0.0;
        }
        return ((float)$mtbf / $denominador) * 100.0;
    }

    public static function indicadoresDoRecorte($horasCalendario, $nFalhas, $minutosReparo, $nReparos) {
        $horasParada = (int)$minutosReparo / 60.0;
        $mttr = self::mttr($minutosReparo, $nReparos);
        $mtbf = self::mtbf($horasCalendario, $horasParada, $nFalhas);
        return [
            'horas_calendario' => $horasCalendario,
            'horas_parada' => $horasParada,
            'horas_operacao' => max(0.0, (float)$horasCalendario - $horasParada),
            'n_falhas' => (int)$nFalhas,
            'n_reparos' => (int)$nReparos,
            'mttr' => $mttr,
            'mtbf' => $mtbf,
            'disponibilidade' => self::disponibilidade($mtbf, $mttr, $nFalhas),
        ];
    }
}

<?php
use PHPUnit\Framework\TestCase;

class IndicadoresCalculoTest extends TestCase {

    public function testDataInvalidaEhIgnorada() {
        $this->assertNull(IndicadoresCalculo::dataValida(''));
        $this->assertNull(IndicadoresCalculo::dataValida('abc'));
        $this->assertNull(IndicadoresCalculo::dataValida('2025-02-31'));
        $this->assertNull(IndicadoresCalculo::horasCalendario('2025-11-30', '2025-11-01'));
    }

    public function testHorasInclusivasDoMesCivil() {
        $this->assertSame(720.0, IndicadoresCalculo::horasCalendario('2025-11-01', '2025-11-30'));
        $this->assertSame(744.0, IndicadoresCalculo::horasCalendario('2025-05-01', '2025-05-31'));
        $this->assertSame(24.0, IndicadoresCalculo::horasCalendario('2025-11-01', '2025-11-01'));
        $this->assertSame(720.0, IndicadoresCalculo::horasNoRecorte('2025-11', null, null));
        $this->assertSame(72.0, IndicadoresCalculo::horasNoRecorte('2025-11', '2025-11-10', '2025-11-12'));
    }

    public function testMttrUsaMinutosEmVezDeHorasTruncadas() {
        $this->assertEqualsWithDelta(1.5, IndicadoresCalculo::mttr(90, 1), 0.0001);
        $this->assertNull(IndicadoresCalculo::mttr(90, 0));
    }

    public function testMtbfZeroNaoViraDisponibilidadeTotal() {
        $r = IndicadoresCalculo::indicadoresDoRecorte(10, 1, 600, 1);
        $this->assertEqualsWithDelta(0.0, $r['mtbf'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $r['disponibilidade'], 0.0001);
    }

    public function testSemFalhasDisponibilidadeCemEMtbfNulo() {
        $r = IndicadoresCalculo::indicadoresDoRecorte(720, 0, 0, 0);
        $this->assertNull($r['mtbf']);
        $this->assertNull($r['mttr']);
        $this->assertEqualsWithDelta(100.0, $r['disponibilidade'], 0.0001);
    }

    public function testNovembro2025DescontaReparoCorretivo() {
        // OS corretivas fechadas do dump: 4 h, 1,5 h e 12 h. Seis falhas no mês.
        // Preventivas do mês não entram nestes minutos.
        $r = IndicadoresCalculo::indicadoresDoRecorte(720, 6, 1050, 3);
        $this->assertSame(6, $r['n_falhas']);
        $this->assertEqualsWithDelta(17.5, $r['horas_parada'], 0.0001);
        $this->assertEqualsWithDelta(5.833333, $r['mttr'], 0.001);
        $this->assertEqualsWithDelta(117.083333, $r['mtbf'], 0.001);
        $this->assertEqualsWithDelta(95.254237, $r['disponibilidade'], 0.01);
    }

    public function testMaio2025DescontaReparoCorretivo() {
        // Duas corretivas; um reparo fechado de 3 h. Maio tem 744 h de calendário.
        $r = IndicadoresCalculo::indicadoresDoRecorte(744, 2, 180, 1);
        $this->assertSame(2, $r['n_falhas']);
        $this->assertEqualsWithDelta(3.0, $r['mttr'], 0.001);
        $this->assertEqualsWithDelta(370.5, $r['mtbf'], 0.001);
        $this->assertEqualsWithDelta(99.196787, $r['disponibilidade'], 0.01);
    }
}

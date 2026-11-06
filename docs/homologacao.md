# Homologação dos indicadores

Evidência do cálculo, sem depender de um banco ligado na hora do teste.

## Como reproduzir

1. Na pasta do projeto: `composer install`
2. `vendor\bin\phpunit`

O teste não altera o MySQL. Ele reproduz as durações das ordens do dump `tabelasSQL/dump_completo_univesp_pi_fixed.sql`. Na verificação desta entrega o serviço MySQL local não aceitou conexão, então a conta foi conferida pelos testes da fórmula, não por uma consulta ao banco ligado.

## Conferência de novembro de 2025

Filtro de 2025-11-01 a 2025-11-30 (720 h).

Corretivas:

| OS | Status | Duração |
|---|---|---|
| 4 | Aberta | sem fim |
| 5 | Fechada | 4 h |
| 6 | Aberta | sem fim |
| 7 | Fechada | 1,5 h |
| 8 | Aberta | sem fim |
| 9 | Fechada | 12 h |

Seis falhas. Três reparos. 17,5 h de parada. Tempo de funcionamento = 720 − 17,5 = 702,5 h.

- MTTR = 17,5 / 3 = 5,83 h
- MTBF = 702,5 / 6 = 117,08 h
- Disponibilidade = 117,08 / (117,08 + 5,83) = 95,25%

As OS 10 e 11 são preventivas e não entram no MTTR.

## Conferência de maio de 2025

Filtro de 2025-05-01 a 2025-05-31 (744 h). OS 1 fechada em 3 h e OS 3 corretiva aberta.

- MTTR = 3 h
- MTBF = (744 − 3) / 2 = 370,5 h
- Disponibilidade = 370,5 / (370,5 + 3) = 99,20%

## Limites conhecidos

- Ordem corretiva ainda aberta conta como falha, mas não reduz o tempo de funcionamento enquanto não houver fim.
- O gráfico mensal sem filtro usa o mês civil inteiro. Os cartões, sem filtro, usam o vão entre a primeira e a última OS.
- O teste automatizado cobre a fórmula. A consulta SQL foi revisada no código; a execução contra o MySQL local depende do ambiente de quem for apresentar.

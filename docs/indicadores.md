# Indicadores de manutenção

A tela de indicadores e a API usam a mesma regra, alinhada à EN 15341 (indicadores de manutenção) e à NBR 5462 (confiabilidade e mantenabilidade). A disponibilidade segue a relação pedida no projeto: MTBF / (MTBF + MTTR).

## Definições

- **Falha / parada não planejada:** ordem de serviço com tipo `CORRETIVA`.
- **Reparo concluído:** corretiva com status `Fechado`, data de início e data de fim, e fim maior ou igual ao início.
- **Tempo de reparo:** soma de `TIMESTAMPDIFF(MINUTE, início, fim) / 60` desses reparos. O cálculo em minutos evita o truncamento de `TIMESTAMPDIFF(HOUR, ...)`.
- **MTTR:** tempo de reparo / número de reparos concluídos. Preventiva e preditiva não entram.
- **Tempo de calendário:** dias inclusivos do período × 24 h. De 1 a 30 de novembro são 720 h; de 1 a 31 de maio são 744 h.
- **Tempo de funcionamento:** tempo de calendário − tempo de reparo. Não fica negativo.
- **MTBF:** tempo de funcionamento / número de falhas. Sem falhas, o MTBF não se aplica (valor nulo). Não é zero.
- **Disponibilidade:** MTBF / (MTBF + MTTR), em porcentagem.
  - Sem falhas: 100%.
  - MTBF igual a zero (o reparo consumiu todo o calendário): 0%.
  - Há falhas, mas nenhum reparo concluído: o MTTR não existe e a disponibilidade fica em branco.

Uma mesma máquina pode ter ordens em linhas diferentes. O filtro Setor/Linha usa `OS_LINHA`. `tab_maq` não tem setor.

## O que cada visão usa como calendário

- **Cartões, ranking e gráficos por equipamento:** o período escolhido nos filtros. Se só a data inicial vier preenchida, o fim é hoje. Se só a data final vier preenchida, o início recua 30 dias inclusivos. Sem datas, o intervalo vai da primeira à última solicitação do recorte.
- **Gráfico de evolução:** cada mês civil, cortado pelo filtro quando o usuário informa datas. Sem filtro, o mês entra inteiro (novembro = 720 h), mesmo que as ordens não cubram todos os dias.

A média dos cartões de MTBF, MTTR e disponibilidade considera apenas equipamentos que têm o indicador no recorte.

## Score de criticidade

`score = (falhas × 0,3) + ((100 − disponibilidade) × 0,4) + (impacto em produção × 0,3)`.

O impacto vem de `MAQ_IMPACTO_PRODUCAO`. O MTTR aparece na tabela. Segurança, qualidade e custo não têm coluna própria na máquina; o custo de manutenção está no cartão e no gráfico, separado do score.

## Leitura do dump de referência

No arquivo `tabelasSQL/dump_completo_univesp_pi_fixed.sql`, com o mês filtrado por inteiro:

| Mês | Falhas corretivas | Reparo considerado | MTTR | MTBF | Disponibilidade |
|---|---|---|---|---|---|
| Novembro/2025 | 6 (OS 4 a 9) | 4 h + 1,5 h + 12 h (OS 5, 7 e 9) | 5,83 h | 117,08 h | 95,25% |
| Maio/2025 | 2 (OS 1 e 3) | 3 h (OS 1) | 3 h | 370,5 h | 99,20% |

As preventivas 10 e 11 de novembro ficam de fora do MTTR. A conta antiga (calendário ÷ falhas, sem descontar o reparo, e MTTR de toda OS fechada) dava novembro com MTTR 5,5 h, MTBF 120 h e disponibilidade 95,62%. Essa conta não é mais a do sistema.

Os testes em `tests/IndicadoresCalculoTest.php` fixam estes números.

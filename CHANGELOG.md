# Histórico

## PI4 — camada analítica e endurecimento

- Dashboard com paradas não planejadas, evolução mensal, filtro de setor/linha e exportação CSV.
- MTBF, MTTR e disponibilidade passam a seguir a regra descrita em `docs/indicadores.md`.
- Login obrigatório nas páginas, logout com encerramento da sessão, senha com `password_hash`, consultas críticas com prepared statement.
- Token da API e credenciais do banco saem do código e vão para `.env`.
- `migrate.php`, `test_math.php` e `stress_test.php` só rodam pela linha de comando.
- Testes PHPUnit da fórmula dos indicadores.

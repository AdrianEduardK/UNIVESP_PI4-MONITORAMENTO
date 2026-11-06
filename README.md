# UNIVESP - Universidade Virtual do Estado de São Paulo

<p align="center">
  <a href="https://univesp.br/">
    <img src="Univesp_logo_png_rgb.png" 
         alt="UNIVESP - Universidade Virtual do Estado de São Paulo" 
         border="0" 
         width="40%" 
         height="40%">
  </a>
</p>

# Plataforma web para gestão e monitoramento de manutenção

## Disciplina

Relatório técnico-científico da disciplina de Projeto Integrador IV. Evolui a plataforma web de gestão de manutenção industrial desenvolvida no Projeto Integrador III para a Borkar Acessórios Automotivos Originais.

## Integrantes

- Amanda Fragnan de Oliveira
- Adrian Eduard Justo Sillex Zimmermann
- André Cicero Nunes Silva
- Gabriel Durval Vieira de Almeida
- Robson Cruz
- Riane Miranda de Franca
- Vitor Gabriel da Cruz
- Wilson Batista da Cruz

## Tutor(a)

- Lara Santana Correia Costa

## Polo(s)

Itapecerica da Serra, Juquitiba, Jandira e Suzano.

## Cursos

- Engenharia da Computação
- Ciência de Dados

## Tema

Plataforma web para gestão e monitoramento de manutenção.

## Problema

A plataforma do Projeto Integrador III centralizou os registros de manutenção da Borkar, mas esses dados ainda não viravam informação para decidir. Faltava acompanhar MTBF, MTTR, disponibilidade, custo, paradas não planejadas e o cumprimento das preventivas, e identificar quais equipamentos exigem atenção primeiro.

## Objetivo

Aprimorar a plataforma do Projeto Integrador III com uma frente analítica: indicadores de desempenho, dashboard, filtros, exportação e priorização dos equipamentos. A base continua sendo o cadastro de produção, máquinas e ordens de serviço já existente.

Fora do escopo: automação física, controle direto do processo produtivo e manutenção preditiva completa. O script Python não lê temperatura, vibração nem treina modelo de machine learning.

---

## Descrição

O sistema registra ordens de serviço e calcula:

- MTBF, MTTR e disponibilidade, pela regra de `docs/indicadores.md` (EN 15341 e NBR 5462; disponibilidade = MTBF / (MTBF + MTTR))
- custo de manutenção
- percentual de preventivas fechadas até a data planejada
- paradas não planejadas (ordens corretivas)
- evolução mensal desses índices
- ranking de criticidade por falhas, indisponibilidade e impacto em produção

Os filtros da tela analítica são período, equipamento e setor/linha (`OS_LINHA`). O ranking e a série mensal podem ser exportados em CSV.

A coleta chamada de IoT é um monitor local: `sensor_monitor.py` usa Pygame para detectar um controle USB (joystick), contar inputs e enviar os eventos ao PHP. Não há Arduino nem Raspberry Pi no código.

A organização é MVC apenas na parte analítica e no cadastro de máquinas (`models/`, `controllers/`). Várias telas ainda acessam o banco direto.

## Tecnologias

- Frontend: HTML, CSS, JavaScript e Chart.js 4.4.6
- Backend: PHP 8.1+ 
- Banco: MySQL / MariaDB
- API: PHP com token Bearer (`api/openapi.yaml`)
- Coleta local: Python 3, Pygame e Requests
- Servidor de desenvolvimento: XAMPP (Apache + MySQL)

## Segurança prevista no código

- Páginas e ações exigem sessão. O perfil PRODUÇÃO não abre a área de manutenção pelo link direto; o perfil PCP acessa essa área.
- Logout em `php/logout.php` encerra a sessão.
- Senha conferida com `password_verify`. Senhas antigas em texto são regravadas no primeiro login, ou de uma vez com `php php/migrar_senhas.php`.
- Consultas dos indicadores, do login, do sensor e das ações citadas no endurecimento usam prepared statement.
- Usuário do banco, senha do banco e token da API ficam no arquivo `.env`, que não deve ir para o Git. O exemplo sem segredo é `.env.example`.
- `php/migrate.php`, `test_math.php` e `stress_test.php` recusam chamada pelo navegador.

## Estrutura

```
PI4
├── api            Endpoints e openapi.yaml
├── controllers
├── css
├── docs           Regra dos indicadores e homologação
├── js
├── models
├── pages
├── php
├── tabelasSQL
├── tests
├── index.php
├── sensor_monitor.py
├── requirements.txt
├── composer.json
└── README.md
```

## Como executar

1. Suba Apache e MySQL no XAMPP.
2. Coloque esta pasta em `htdocs`. O nome da pasta vira o caminho na URL. Exemplo: `C:\xampp\htdocs\PI4` abre em `http://localhost/PI4/index.php`.
3. Copie `.env.example` para `.env`. Preencha o banco e gere um `API_TOKEN` longo. Use um usuário MySQL da aplicação, sem ser `root`.
4. Crie o banco `univesp_pi` e importe **`tabelasSQL/dump_completo_univesp_pi_fixed.sql`**. Esse é o dump com as colunas do PI4 (tipo de manutenção, custos, data planejada, impacto e log de sensor).
   - `dump_completo_univesp_pi.sql` é a exportação anterior.
   - `migracao_pi4_indicadores.sql` serve só para quem já tem o banco do PI III e não vai reimportar o dump.
5. Se o banco ainda tiver senha em texto puro, rode `php php/migrar_senhas.php` com o PHP do XAMPP (o `php.exe` de `C:\xampp\php`). O `php` que estiver no PATH pode não ter a extensão `mysqli`.
6. Instale o monitor: `pip install -r requirements.txt`
7. A tag é obrigatória e precisa existir em `tab_maq` (por exemplo `EXT001`):

   `python sensor_monitor.py --tag EXT001`

   A URL e o token saem do `.env` (`SENSOR_URL` e `API_TOKEN`).
8. Abra `http://localhost/PI4/index.php`.
9. Testes da fórmula: `composer install` e depois `vendor\bin\phpunit`. No XAMPP padrão pode faltar a extensão `zip`; nesse caso habilite `extension=zip` no `php.ini` e rode o Composer de novo. O roteiro da conta está em `docs/homologacao.md`.

## Funcionalidades

- Ordens de serviço, produção, produtos, linhas, máquinas, DDE e checklist NR12
- Dashboard analítico com cartões, gráficos por equipamento e evolução mensal
- Filtros por máquina, período e setor/linha
- Exportação CSV do ranking e da evolução
- API dos indicadores com token Bearer
- Monitor local de controle USB, com sessão que permanece aberta até a desconexão

## Histórico

O detalhe desta entrega está em `CHANGELOG.md`. A versão anterior do repositório era o fluxo de ordens e produção do PI III, com o monitor de joystick já acoplado e os indicadores ainda em fórmula simplificada.

## Licença

Projeto acadêmico do Projeto Integrador da UNIVESP. Uso restrito aos autores e à instituição.

Repositório: https://github.com/AdrianEduardK/UNIVESP_PI4-MONITORAMENTO

<p align="center"> <b>© 2026 - UNIVESP | Projeto Integrador</b> </p>

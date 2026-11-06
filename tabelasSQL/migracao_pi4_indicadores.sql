-- Script de Migração para PI4 (Camada Analítica de Indicadores)
-- Executar este script se você já possui o banco de dados do PI3 populado.

-- 1. Adicionar colunas na tabela tab_ordem_servico
ALTER TABLE tab_ordem_servico
  ADD COLUMN OS_TIPO_MANUT ENUM('PREVENTIVA','CORRETIVA','PREDITIVA') NOT NULL DEFAULT 'CORRETIVA',
  ADD COLUMN OS_CUSTO_PECAS DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN OS_CUSTO_MAO_OBRA DECIMAL(10,2) DEFAULT 0,
  ADD COLUMN OS_DATA_PLANEJADA DATE DEFAULT NULL;

-- 2. Adicionar coluna de impacto de produção na tabela tab_maq
ALTER TABLE tab_maq
  ADD COLUMN MAQ_IMPACTO_PRODUCAO TINYINT DEFAULT 1; -- escala 1 a 5

-- 3. Limpeza/Correção de dados (Integridade Referencial)
-- Antes de adicionar a constraint de chave estrangeira, garantimos que 
-- qualquer OS apontando para uma máquina inexistente seja atualizada 
-- para NULL ou para uma máquina padrão. Aqui iremos ignorar OS inválidas ou deletá-las.
-- DELETE FROM tab_ordem_servico WHERE OS_TAG_MAQ NOT IN (SELECT MAQ_TAG FROM tab_maq);
-- Ou, de forma mais segura, apenas identificar e limpar a tag da máquina se for inválida, 
-- caso o campo permita NULL (se não permitir, teremos de deletar as órfãs).
DELETE FROM tab_ordem_servico WHERE OS_TAG_MAQ NOT IN (SELECT MAQ_TAG FROM tab_maq);

-- 4. Adicionar Integridade Referencial (Foreign Key)
ALTER TABLE tab_ordem_servico
  ADD CONSTRAINT fk_os_maq FOREIGN KEY (OS_TAG_MAQ) REFERENCES tab_maq(MAQ_TAG) ON DELETE CASCADE ON UPDATE CASCADE;

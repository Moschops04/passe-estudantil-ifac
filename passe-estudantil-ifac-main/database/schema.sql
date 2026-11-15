-- Base local do portal de declarações de passe estudantil
-- Esta base é o "espelho" local do SUAP: quem alimenta a tabela `alunos`
-- em produção é o bot de sincronização em Python (rodando 2x ao dia,
-- conforme a proposta de TCC). Por enquanto ela é populada com dados
-- de exemplo (seed.sql) para o desenvolvimento do site funcionar sozinho.

CREATE TABLE IF NOT EXISTS alunos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    matricula TEXT NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    curso TEXT NOT NULL,
    semestre INTEGER NOT NULL,
    email_institucional TEXT NOT NULL,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS solicitacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    protocolo TEXT NOT NULL UNIQUE,
    aluno_id INTEGER NOT NULL,
    email_destino TEXT NOT NULL,
    token_hash TEXT NOT NULL,
    token_expira_em DATETIME NOT NULL,
    tentativas INTEGER NOT NULL DEFAULT 0,
    status TEXT NOT NULL DEFAULT 'aguardando_token'
        CHECK (status IN ('aguardando_token', 'confirmada', 'expirada', 'cancelada')),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    confirmado_em DATETIME
);

CREATE INDEX IF NOT EXISTS idx_solicitacoes_protocolo ON solicitacoes(protocolo);
CREATE INDEX IF NOT EXISTS idx_solicitacoes_aluno ON solicitacoes(aluno_id);

-- Dados de EXEMPLO para desenvolvimento/testes/demonstração.
-- Em produção, esta tabela é preenchida pelo bot Python de sincronização
-- com o SUAP, não manualmente.

INSERT OR IGNORE INTO alunos (matricula, nome, curso, semestre, email_institucional) VALUES
('2024001234', 'João da Silva Santos', 'Técnico em Informática', 4, '2024001234@aluno.ifac.edu.br'),
('2023005678', 'Maria Oliveira Costa', 'Sistemas para Internet', 6, '2023005678@aluno.ifac.edu.br'),
('2022009911', 'Gabriel Nascimento Diógenes', 'Sistemas para Internet', 8, '2022009911@aluno.ifac.edu.br'),
('2022009922', 'Paulo Vinícius Moure de Araújo', 'Sistemas para Internet', 8, '2022009922@aluno.ifac.edu.br'),
('2024002211', 'Ana Beatriz Ferreira Lima', 'Técnico em Agropecuária', 2, '2024002211@aluno.ifac.edu.br'),
('2023007744', 'Lucas Gabriel Pereira Souza', 'Técnico em Edificações', 5, '2023007744@aluno.ifac.edu.br'),
('2021003399', 'Camila Rodrigues Almeida', 'Licenciatura em Química', 7, '2021003399@aluno.ifac.edu.br'),
('2024004455', 'Pedro Henrique Barbosa Costa', 'Técnico em Informática', 1, '2024004455@aluno.ifac.edu.br'),
('2022006688', 'Larissa Mendes Carvalho', 'Administração', 6, '2022006688@aluno.ifac.edu.br'),
('2023008822', 'Rafael Augusto Nogueira Dias', 'Técnico em Enfermagem', 3, '2023008822@aluno.ifac.edu.br'),
('2024001122', 'Beatriz Cunha Ribeiro', 'Sistemas para Internet', 2, '2024001122@aluno.ifac.edu.br'),
('2022005533', 'Thiago Martins Rocha', 'Técnico em Agropecuária', 8, '2022005533@aluno.ifac.edu.br'),
('2023009911', 'Isabela Correia Freitas', 'Licenciatura em Química', 4, '2023009911@aluno.ifac.edu.br'),
('2021002277', 'Matheus Vinícius Alves Batista', 'Administração', 8, '2021002277@aluno.ifac.edu.br'),
('2024003366', 'Sophia Gabrielly Teixeira', 'Técnico em Edificações', 1, '2024003366@aluno.ifac.edu.br');

-- Solicitações de exemplo, em diferentes status, só para a página
-- de Suporte (consulta de protocolo) ter o que mostrar numa demo.
-- Os protocolos abaixo são fixos de propósito, para facilitar testar:
--   IFAC-2026-100001  -> confirmada
--   IFAC-2026-100002  -> aguardando_token
--   IFAC-2026-100003  -> expirada

INSERT OR IGNORE INTO solicitacoes
  (protocolo, aluno_id, email_destino, token_hash, token_expira_em, status, criado_em, confirmado_em)
VALUES
  ('IFAC-2026-100001', (SELECT id FROM alunos WHERE matricula='2024001234'), (SELECT email_institucional FROM alunos WHERE matricula='2024001234'), '$2y$10$SNNBF8ysnP6hZfJbXtxM1.3/KM/EaqsZBkawTeCSjbK/15.JEdOtm', datetime('now', '-3 days', '+10 minutes'), 'confirmada', datetime('now', '-3 days'), datetime('now', '-3 days')),
  ('IFAC-2026-100002', (SELECT id FROM alunos WHERE matricula='2023005678'), (SELECT email_institucional FROM alunos WHERE matricula='2023005678'), '$2y$10$hv67IHa4y4w19NVCVBKvhu6EMtKRUP/.ZF3AUXj8wwtKZ1SlSsm5m', datetime('now', '-0 days', '+10 minutes'), 'aguardando_token', datetime('now', '-0 days'), NULL),
  ('IFAC-2026-100003', (SELECT id FROM alunos WHERE matricula='2022006688'), (SELECT email_institucional FROM alunos WHERE matricula='2022006688'), '$2y$10$M45RBppjyTNyle6V2TF6ZurJf4C32jtbNuHIAI1mht8qT.jSbQrKi', datetime('now', '-5 days', '+10 minutes'), 'expirada', datetime('now', '-5 days'), NULL);

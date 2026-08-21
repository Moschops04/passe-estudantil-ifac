# Portal de Declaração de Passe Estudantil — IFAC

Base funcional do site descrito na proposta de TCC (*Sistema Automatizado
de Emissão de Declarações de Passe Estudantil com Integração ao SUAP*).

Este pacote já roda de ponta a ponta localmente: identificação por
matrícula, geração de token de uso único, expiração em 10 minutos,
confirmação e protocolo — usando um banco local que simula os dados que,
em produção, o bot Python trará do SUAP.

## Como rodar

Requisitos: PHP 8.1+ com extensões `sqlite3`, `pdo_sqlite` e `mbstring`.

```bash
cd public
php -S localhost:8000
```

Abra `http://localhost:8000`. No primeiro acesso o arquivo
`database/app.sqlite` é criado automaticamente a partir de
`database/schema.sql` e populado com alunos de exemplo
(`database/seed.sql`) — inclusive as matrículas de vocês dois, já cadastradas.

Matrículas de teste: `2024001234`, `2023005678`, `2022009911`, `2022009922`,
e mais 11 alunos fictícios cobrindo outros cursos do IFAC (veja a lista
completa em `database/seed.sql`).

Protocolos de demonstração já cadastrados (para testar a consulta de
status na página de Suporte sem precisar solicitar nada):
- `IFAC-2026-100001` — confirmada
- `IFAC-2026-100002` — aguardando confirmação do token
- `IFAC-2026-100003` — expirada

## Estrutura

```
public/                 # tudo que é servido pelo navegador (document root)
  index.php             # a página do portal (HTML do protótipo de vocês)
  assets/css/style.css  # estilos (extraídos do protótipo)
  assets/js/app.js      # front-end: chama a API de verdade em vez de simular
  api/
    verificar.php       # POST matrícula -> dados do aluno
    solicitar.php       # POST email -> gera token + protocolo
    confirmar.php       # POST token -> confirma e libera protocolo

src/
  db.php                # conexão PDO + criação automática do schema
  helpers.php           # protocolo, token, máscara de e-mail, JSON
  bootstrap.php         # sessão + includes comuns dos endpoints
  env.php               # leitor simples do arquivo .env
  mailer.php            # envio do e-mail com o token (PHPMailer + SMTP)
  vendor/phpmailer/     # PHPMailer "puro" (3 arquivos, sem Composer)

database/
  schema.sql            # tabelas: alunos, solicitacoes
  seed.sql              # dados de exemplo (mock do SUAP)
  app.sqlite            # gerado automaticamente, não versionar

.env.example            # copiar para .env e preencher (nunca commitar o .env)
storage/declaracoes/    # onde os PDFs gerados vão ficar (próxima etapa)
```

## Enviando o e-mail de confirmação (token) de verdade

O envio já está implementado (`src/mailer.php`, usando **PHPMailer**
via SMTP) e plugado em `public/api/solicitar.php`. Sem nenhuma
configuração, o site continua em "modo desenvolvimento" — não envia
nada, só mostra o token na tela, exatamente como antes.

### Como ligar o envio real

1. **Consigam as credenciais de SMTP** do e-mail institucional com o
   setor de TI do IFAC: host, porta, usuário e senha. Normalmente é
   algo como `smtp.ifac.edu.br`, porta `587` com TLS.

2. **Copiem `.env.example` para `.env`** (mesma pasta do README) e
   preencham:
   ```
   SMTP_HOST=smtp.ifac.edu.br
   SMTP_PORT=587
   SMTP_SECURE=tls
   SMTP_USER=seu_usuario_aqui
   SMTP_PASS=sua_senha_aqui
   SMTP_FROM=passe-estudantil@ifac.edu.br
   SMTP_FROM_NAME=Portal do Estudante - IFAC
   ```
   Assim que `SMTP_HOST` estiver preenchido, o site passa a enviar
   e-mails de verdade automaticamente — não precisa mexer em mais nada.

3. **Não têm acesso ao e-mail institucional ainda?** Testem com um
   "SMTP de mentira" gratuito feito pra isso, sem risco de mandar
   e-mail de verdade sem querer:
   - [Mailtrap](https://mailtrap.io) ou [Ethereal Email](https://ethereal.email/)
     — criam uma caixa de entrada de teste com host/usuário/senha de
     SMTP prontos, é só colar no `.env`.

### Sobre a biblioteca

Usei o **PHPMailer** (a biblioteca mais usada pra isso em PHP) mas sem
Composer — só os 3 arquivos essenciais (`src/vendor/phpmailer/`), pra
não depender de instalar mais nada no servidor da coordenação. Se
preferirem, podem trocar por Composer normalmente depois.

### Segurança

- O `.env` **nunca** deve ir para o Git nem ficar acessível
  publicamente (já está no `.gitignore`) — é nele que fica a senha do
  e-mail.
- Se o envio falhar (SMTP fora do ar, senha errada etc.), o aluno vê
  uma mensagem genérica e amigável; o erro técnico completo vai só
  para o log do servidor (`error_log`), nunca para a resposta da API.

## Página de Suporte

`public/suporte.php` traz:
- FAQ em acordeão com as dúvidas mais comuns (prazo, token não recebido,
  matrícula não encontrada, validade do documento etc.) — para editar
  as perguntas, é só mexer direto no HTML dentro de `suporte.php`.
- Consulta pública de status por número de protocolo, ligada a um novo
  endpoint `public/api/status.php` (não expõe nome nem e-mail do aluno,
  só o status da solicitação).
- Canais de atendimento (e-mail, horário, presencial, WhatsApp) — troque
  pelos dados reais da coordenação antes de publicar.

O cabeçalho e o menu de navegação viraram um include compartilhado
(`public/partials/header.php`) entre `index.php` e `suporte.php`, para
não duplicar HTML — se criarem uma terceira página, é só repetir o
mesmo `include`.

## O que já é real vs. o que ainda é mock

**Real e funcional:**
- Fluxo dos 3 passos (identificação → token → confirmação) rodando contra
  um banco de verdade, com sessão de servidor (ninguém troca de matrícula
  no meio do fluxo só editando o front-end).
- Token de 6 dígitos com **hash** salvo no banco (nunca em texto puro),
  expiração de 10 minutos e limite de 5 tentativas.
- Geração de protocolo único e persistência da solicitação.
- Envio do e-mail de confirmação por SMTP de verdade (ver seção acima) —
  cai em modo desenvolvimento automaticamente sem `.env` configurado.

**Mockado de propósito, para o site funcionar sozinho enquanto o restante
do TCC é desenvolvido:**
- A tabela `alunos` é alimentada por `seed.sql`. Em produção, quem
  preenche essa tabela é o bot Python de scraping do SUAP (rodando ao
  menos 2x ao dia, conforme a metodologia da proposta) — o site já está
  pronto para consumir essa tabela sem mudar nada quando o bot existir.
- A emissão do PDF com hash de autenticidade ainda não está implementada
  — o lugar certo para isso é logo após a confirmação do token em
  `public/api/confirmar.php` (tem um comentário `TODO` marcando o ponto exato).

## Próximos passos sugeridos (para dividir entre vocês dois)

1. Implementar o bot Python que autentica no SUAP e sincroniza a tabela
   `alunos` (pode gravar direto no mesmo `app.sqlite`, ou vocês migram
   para MySQL nesse momento — o código PHP não muda, só a string de
   conexão em `src/db.php`).
2. Conseguir as credenciais de SMTP institucional com o TI e preencher
   o `.env` (ver seção "Enviando o e-mail de confirmação" acima) — o
   código já está pronto, só falta a senha de verdade.
3. Implementar `gerar_pdf_declaracao()` com uma lib como `dompdf` ou
   `mpdf`, incluindo um hash/token de verificação no rodapé do documento.
4. Trocar o SQLite pelo banco definitivo no servidor
   `coordenacao.ifac.si` quando for para produção.

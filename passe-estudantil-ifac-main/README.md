# Portal de Declaração de Passe Estudantil — IFAC

Aplicação PHP para autenticar o próprio estudante na API do SUAP, consultar os dados acadêmicos mínimos e gerar a declaração em memória. A emissão atual não usa banco de dados, sessão PHP, fila, e-mail ou arquivo temporário.

## Estrutura

```text
public/                     # Document root do servidor web
  index.php                 # Formulário de matrícula e senha
  validar.php               # Verificação pública do selo HMAC
  api/emitir.php            # Autenticação SUAP e resposta PDF
  api/{verificar,solicitar,confirmar,status}.php  # Rotas antigas desativadas (410)
  assets/                   # CSS, JS e logotipo IFAC
src/
  suap.php                  # Cliente HTTPS/JWT; normaliza perfil do aluno
  integridade.php           # Hash SHA-256 e selo HMAC autoverificável
  pdf.php                   # Geração FPDF em memória (Output S)
  assinador_pdf.php         # Contrato e ponto de integração para API PAdES futura
  env.php                   # Leitura de .env
  vendor/fpdf/              # FPDF e fontes-base usadas pelo gerador
tests/
  integridade_test.php      # Testes do código de validação
  pdf_test.php              # Teste do PDF gerado sem gravar arquivo
  assinador_pdf_test.php    # Teste do ponto de integração do assinador remoto
  suap_status_test.php      # Testes do vínculo ativo de estudante
storage/declaracoes/        # PDFs existentes preservados; não usados pela emissão
```

## Requisitos

- PHP 8.1+ com `curl` e `iconv` habilitados; certificado CA do sistema atualizado.
- PHP-FPM/Apache ou Nginx em HTTPS.
- Composer e Pip não são necessários nesta implementação: FPDF está incluído e não há componentes Python.

No Debian, por exemplo:

```bash
sudo apt update
sudo apt install php8.2-cli php8.2-fpm php8.2-curl php8.2-mbstring
```

O suporte a `iconv` normalmente vem com os pacotes comuns do PHP no Debian. Confirme com `php -m`.

No Windows, a distribuição de PHP pode não incluir um bundle CA para o cURL. Baixe o bundle oficial do curl.se para um caminho local (não para o webroot) e informe-o em `SUAP_CA_BUNDLE` no `.env`:

```powershell
New-Item -ItemType Directory -Force "$env:LOCALAPPDATA\PHP" | Out-Null
Invoke-WebRequest https://curl.se/ca/cacert.pem -OutFile "$env:LOCALAPPDATA\PHP\cacert.pem"
```

O cliente usa esse arquivo mantendo a validação TLS habilitada. Em Debian, normalmente basta instalar/atualizar `ca-certificates`; deixe `SUAP_CA_BUNDLE` vazio.

## Configuração

1. Copie `.env.example` para `.env`, mantenha-o fora do document root e proteja-o contra leitura por outros usuários.
2. Gere uma chave secreta aleatória:

   ```bash
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```

3. Configure `APP_INTEGRITY_SECRET` com a saída de 64 caracteres e ajuste `APP_VALIDATION_URL` para a URL pública real de `validar.php`.
4. Confira no [Swagger oficial do SUAP/IFAC](https://suap.ifac.edu.br/api/docs/) as rotas atuais. A emissão usa `POST /api/token/pair`, `GET /api/rh/eu/` (dados pessoais), `GET /api/ensino/meus-dados-aluno/` (curso e dados acadêmicos) e `GET /api/rh/meus-vinculos/` (situação ativa do vínculo discente), configuráveis no `.env`.
5. Configure o servidor web para servir **somente** a pasta `public/`. Não publique `.env`, `src/`, `database/` ou `storage/`.

Exemplo de início local:

```bash
cp .env.example .env
# Edite .env e substitua a chave de exemplo por uma chave aleatória.
php -S 127.0.0.1:8000 -t public
```

A API do SUAP exige conexão HTTPS e pode não aceitar autenticação a partir de um ambiente local. Não use credenciais reais em testes nem desative a validação TLS.

### Proteções recomendadas no Nginx

Declare a zona dentro do bloco `http`:

```nginx
limit_req_zone $binary_remote_addr zone=suap_login:10m rate=5r/m;
```

Na configuração do host, limite o endpoint e não registre o corpo das requisições:

```nginx
location = /api/emitir.php {
    limit_req zone=suap_login burst=3 nodelay;
    client_max_body_size 16k;
    access_log off;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root/api/emitir.php;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
}
```

Não habilite logs de corpo, dumps de requisição, debug do cURL nem ferramentas de APM que capturem o POST de emissão. Os logs padrão do servidor não devem incluir corpo, mas verifique a configuração efetiva do ambiente.

## Fluxo e API SUAP

1. O formulário envia matrícula e senha por HTTPS para `POST /api/emitir.php`.
2. O servidor envia `username` e `password` a `/api/token/pair`. HTTP 401 retorna erro de credenciais inválidas.
3. O token `access` é usado em consultas `GET` a `/api/rh/eu/`, `/api/ensino/meus-dados-aluno/` e `/api/rh/meus-vinculos/`, com `Authorization: Bearer ...`.
4. A aplicação valida a situação ativa, cria o PDF em memória, responde com `Cache-Control: no-store` e descarta as variáveis locais. Não é criada sessão PHP nem arquivo de declaração.

Somente nome, matrícula, CPF, curso e situação são usados no documento. A situação ativa é confirmada pelo campo booleano `detalhamento.ativo` no vínculo discente de `/rh/meus-vinculos/`. O campo textual `situacao` de `/ensino/meus-dados-aluno/` pode descrever a migração de sistemas (“Migrado do SIGAA para o SUAP”), portanto não é usado como estado da matrícula. Respostas, tokens e credenciais do SUAP não são gravados em log. O cliente registra apenas códigos HTTP, rota e erros de conexão sem credenciais, para facilitar o diagnóstico. Em caso de mudança no formato/rotas SUAP, atualize os caminhos e aliases em `src/suap.php` de acordo com o Swagger oficial; o endpoint deve falhar fechado quando campos obrigatórios ou vínculo ativo estiverem ausentes.

## Integridade e limitações da assinatura

O PDF reserva campos visuais para as assinaturas do aluno e do diretor, sem nome ou cargo abaixo das linhas, conforme o modelo atual. O espaço acima da linha do diretor está reservado para futura assinatura visual; **o PDF atual não está assinado pelo diretor**. `src/assinador_pdf.php` define o contrato `AssinadorPdfDiretor` e a função `assinar_pdf_diretor()`: no futuro, implemente um adaptador para a API institucional e invoque essa função em `public/api/emitir.php`, após gerar o PDF e antes de enviá-lo ao navegador. O adaptador deve enviar o PDF por HTTPS, autenticar a API conforme o contrato oficial e devolver o documento efetivamente assinado em PAdES. A função valida apenas a estrutura básica do PDF, não verifica criptograficamente a assinatura recebida; implemente verificação PAdES antes de confiar no resultado. Nenhuma chamada de assinatura é feita enquanto a API não estiver configurada.

O rodapé recebe um código assinado com HMAC-SHA-256 e um hash SHA-256 dos campos de identificação emitidos pelo SUAP. O código carrega esses dados e a página pública `validar.php` verifica a assinatura, apresenta os campos e permite compará-los com o PDF. Isso não requer banco ou registro de estudante. O conteúdo codificado no selo não é criptografado; trate o código como informação pública do próprio documento. O selo não calcula nem atesta o hash dos bytes do PDF; alterações no restante do arquivo devem ser avaliadas comparando os campos autenticados com o documento.

**Este selo não é assinatura digital PAdES nem certificado ICP-Brasil**, e não deve ser apresentado como tal. HMAC comprova que o selo foi emitido por quem controla o segredo e que o conteúdo assinado não foi alterado; a conferência visual entre os dados autenticados e o PDF é necessária. Confirme com o IFAC e o órgão recebedor a aceitação jurídica do formato. Para PAdES, integre uma biblioteca compatível (por exemplo, FPDI + ferramenta de assinatura PKI), mantenha a chave privada fora do webroot em cofre/HSM e valide o fluxo jurídico antes de produção.

A chave `APP_INTEGRITY_SECRET` é essencial: mantenha-a secreta e não a troque sem planejar como validar documentos antigos. Uma rotação invalida códigos anteriores se a chave antiga não for mantida pelo verificador.

## Testes locais

```bash
php tests/integridade_test.php
php tests/pdf_test.php
php tests/suap_connectivity.php
php tests/suap_status_test.php
php tests/assinador_pdf_test.php
```

`suap_connectivity.php` testa apenas HTTPS/cURL e a rota pública (GET sem credenciais); ele não autentica. Teste de autenticação com SUAP somente em ambiente autorizado e com conta de teste, sem registrar credenciais.

## Migração do protótipo

As antigas rotas de sincronização, solicitação por e-mail, confirmação e status agora respondem HTTP 410 e não consultam banco. A interface principal não usa banco local. Os PDFs históricos em `storage/` foram preservados por poderem conter dados importantes: revise-os e faça a limpeza/retention segura antes de qualquer implantação pública. Os arquivos auxiliares SQL, scripts de teste antigos, código de banco/e-mail, PHPMailer sem uso, cópias duplicadas de FPDF e ferramentas/fontes não utilizadas foram removidos. As rotas HTTP 410 foram mantidas para informar clientes antigos de que o fluxo foi desativado. O diretório `public/` deve ser o único exposto pelo servidor.

O PDF de referência foi usado como base visual: cabeçalho institucional, faixa verde, título, declaração, linhas de assinatura do aluno e do diretor e selo no rodapé foram recriados em FPDF usando o logotipo já existente. Os campos do modelo que não estão na lista mínima da integração (RG, filiação, data de nascimento, endereço, período/semestre, horários e campus) foram omitidos para não buscar nem inventar dados. Confirme com a coordenação se esses campos são obrigatórios antes da aprovação para produção; o PDF original não é embutido como fundo.

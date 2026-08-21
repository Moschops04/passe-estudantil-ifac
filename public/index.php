<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal do Estudante · Declaração de Passe Estudantil · IFAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="portal-wrapper">
  <?php $pagina_ativa = 'declaracoes'; include __DIR__ . '/partials/header.php'; ?>

  <div class="main">
    <div class="page-header">
      <div class="page-title">Solicitação de Declaração de Passe Estudantil</div>
      <div class="page-subtitle">Solicite sua declaração de forma autônoma. O documento será gerado automaticamente após a validação dos seus dados.</div>
    </div>

    <div class="step-indicator" id="steps">
      <div class="step active" id="step1-ind">
        <div class="step-num">1</div>
        <div class="step-label">Identificação</div>
      </div>
      <div class="step" id="step2-ind">
        <div class="step-num">2</div>
        <div class="step-label">Confirmação</div>
      </div>
      <div class="step" id="step3-ind">
        <div class="step-num"><i class="ti ti-check" style="font-size:12px"></i></div>
        <div class="step-label">Concluído</div>
      </div>
    </div>

    <!-- Alerta de erro genérico, usado pelo app.js -->
    <div id="alerta-erro" class="notice" style="display:none; background:#fdecea; border-color:rgba(200,10,10,0.25); color:#7a1408; margin-bottom:16px;">
      <i class="ti ti-alert-circle" aria-hidden="true"></i>
      <span id="alerta-erro-texto"></span>
    </div>

    <!-- PASSO 1: identificação -->
    <div id="screen-step1">
      <div class="card">
        <div class="card-title"><i class="ti ti-user-circle" aria-hidden="true"></i> Identificação do estudante</div>
        <div class="card-desc">Informe sua matrícula para que seus dados sejam localizados no sistema acadêmico.</div>

        <div class="form-grid full">
          <div class="field">
            <label>Matrícula</label>
            <div class="matricula-wrapper">
              <input type="text" id="matricula" placeholder="Ex: 20241CRB12345678910" maxlength="19" />
              <button class="btn-verificar" id="btn-verificar" type="button">
                <i class="ti ti-search" aria-hidden="true"></i> Verificar
              </button>
            </div>
          </div>

          <div id="aluno-info" style="display:none">
            <div class="aluno-info">
              <div class="aluno-avatar" id="aluno-initials">--</div>
              <div>
                <div class="aluno-nome" id="aluno-nome"></div>
                <div class="aluno-curso" id="aluno-curso"></div>
              </div>
              <div class="badge-ok"><i class="ti ti-circle-check" aria-hidden="true"></i> Verificado</div>
            </div>
          </div>

          <div class="field">
            <label>E-mail institucional</label>
            <input type="email" id="email" placeholder="matricula@aluno.ifac.edu.br" />
          </div>
        </div>

        <div class="divider"></div>

        <div class="notice">
          <i class="ti ti-info-circle" aria-hidden="true"></i>
          <span>Um token de uso único será enviado para o seu e-mail institucional para validar a solicitação. O token expira em <strong>10 minutos</strong>.</span>
        </div>
      </div>

      <div class="actions">
        <span></span>
        <button class="btn-primary" id="btn-enviar-confirmacao" type="button" disabled>
          Enviar confirmação <i class="ti ti-send" aria-hidden="true"></i>
        </button>
      </div>
    </div>

    <!-- PASSO 2: token -->
    <div id="screen-step2" style="display:none">
      <div class="codigo-card" id="codigo-card">
        <div class="codigo-icon"><i class="ti ti-mail-opened" aria-hidden="true"></i></div>
        <div class="codigo-title">Verifique seu e-mail</div>
        <div class="codigo-desc">Enviamos um código de 6 dígitos para <strong id="email-display"></strong>. Insira o código abaixo para confirmar sua solicitação.</div>

        <div id="aviso-dev" class="notice" style="display:none; margin-bottom:20px; max-width:440px; margin-left:auto; margin-right:auto;">
          <i class="ti ti-flask" aria-hidden="true"></i>
          <span>Modo desenvolvimento: como ainda não há envio de e-mail configurado, o código gerado é <strong id="codigo-dev-valor"></strong>.</span>
        </div>

        <div class="timer">Código válido por <strong id="countdown">10:00</strong></div>

        <div class="codigo-input-wrap">
          <input type="text" class="codigo-code" id="codigo-input" placeholder="000000" maxlength="6" inputmode="numeric" />
          <button class="btn-primary" id="btn-confirmar-token" type="button">
            <i class="ti ti-check" aria-hidden="true"></i>
          </button>
        </div>

        <div style="font-size:13px; color:var(--color-text-secondary);">
          Não recebeu? <a href="#" id="link-reenviar" style="color:var(--ifac-green);">Reenviar código</a>
        </div>
      </div>

      <div class="actions" style="margin-top:16px">
        <button class="btn-secondary" id="btn-voltar" type="button">
          <i class="ti ti-arrow-left" aria-hidden="true"></i> Voltar
        </button>
      </div>
    </div>

    <!-- PASSO 3: concluído -->
    <div id="screen-step3" style="display:none">
      <div class="confirmed-card">
        <div class="confirmed-icon"><i class="ti ti-circle-check" aria-hidden="true"></i></div>
        <div class="token-title">Solicitação realizada com sucesso!</div>
        <div class="token-desc">Sua declaração foi solicitada e está sendo gerada. Você receberá o documento em seu e-mail institucional em até <strong>1 dia útil</strong>.</div>

        <div class="protocol-box" id="protocol-num"></div>

        <div style="font-size:13px; color:var(--color-text-secondary); margin-bottom: 8px;">
          Guarde o número de protocolo para acompanhar ou validar seu documento.
        </div>

        <div class="confirmed-actions">
          <button class="btn-secondary" id="btn-nova-solicitacao" type="button">
            <i class="ti ti-plus" aria-hidden="true"></i> Nova solicitação
          </button>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>

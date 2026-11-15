<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="referrer" content="no-referrer">
  <title>Declaração de Passe Estudantil · IFAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="portal-wrapper">
  <?php $pagina_ativa = 'declaracoes'; include __DIR__ . '/partials/header.php'; ?>
  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Declaração para Passe Estudantil</h1>
      <p class="page-subtitle">Autentique-se no SUAP para emitir e baixar sua declaração.</p>
    </div>

    <div id="alerta-erro" class="notice" role="alert" style="display:none; background:#fdecea; border-color:rgba(200,10,10,0.25); color:#7a1408; margin-bottom:16px;">
      <i class="ti ti-alert-circle" aria-hidden="true"></i>
      <span id="alerta-erro-texto"></span>
    </div>

    <div class="card">
      <div class="card-title"><i class="ti ti-user-circle" aria-hidden="true"></i> Acesso do estudante</div>
      <p class="card-desc">Use sua própria matrícula e senha do SUAP. A autenticação é feita na API oficial do IFAC.</p>

      <form id="form-emissao" autocomplete="on">
        <div class="form-grid full">
          <div class="field">
            <label for="matricula">Matrícula</label>
            <input id="matricula" name="username" type="text" autocomplete="username" maxlength="64"
              placeholder="Sua matrícula do SUAP" required>
          </div>
          <div class="field">
            <label for="senha">Senha do SUAP</label>
            <input id="senha" name="password" type="password" autocomplete="current-password" maxlength="256"
              placeholder="Sua senha do SUAP" required>
          </div>
        </div>

        <div class="divider"></div>
        <div class="notice">
          <i class="ti ti-shield-lock" aria-hidden="true"></i>
          <span><strong>Privacidade:</strong> sua senha é enviada somente à API oficial do SUAP para autenticação. Este portal não a salva, não armazena token nem mantém dados acadêmicos em banco local. Os dados necessários ficam em memória apenas durante a geração do PDF.</span>
        </div>
        <div id="emissao-status" role="status" aria-live="polite" style="margin-top:16px; color:var(--color-text-secondary);"></div>

        <div class="actions">
          <span></span>
          <button class="btn-primary" id="btn-emitir" type="submit">
            Emitir e baixar PDF <i class="ti ti-download" aria-hidden="true"></i>
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

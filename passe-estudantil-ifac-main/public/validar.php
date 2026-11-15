<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/env.php';
carregar_env(__DIR__ . '/../.env');
require_once __DIR__ . '/../src/integridade.php';

header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

$codigo = '';
$emitidoEm = null;
$configuracaoInvalida = false;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $codigo = trim((string) ($_POST['codigo'] ?? ''));
    try {
        $emitidoEm = strlen($codigo) <= 1200 ? validar_codigo_validacao($codigo) : null;
    } catch (RuntimeException $erro) {
        http_response_code(500);
        $configuracaoInvalida = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="referrer" content="no-referrer">
  <title>Validação de Documento · IFAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="portal-wrapper">
  <?php $pagina_ativa = 'validar'; include __DIR__ . '/partials/header.php'; ?>
  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Validação de documentos</h1>
      <p class="page-subtitle">Confira o selo de autenticidade impresso no rodapé da declaração.</p>
    </div>

    <div class="card" style="max-width:680px; margin:0 auto;">
      <form method="post" action="validar.php">
        <div class="field">
          <label for="codigo">Código de verificação</label>
          <textarea id="codigo" name="codigo" rows="5" maxlength="1200" required
            autocomplete="off" style="font-family:monospace; padding:9px 12px; border:0.5px solid var(--color-border-tertiary); border-radius:var(--border-radius-md);"><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="actions">
          <span></span>
          <button class="btn-primary" type="submit"><i class="ti ti-shield-check" aria-hidden="true"></i> Validar</button>
        </div>
      </form>

      <?php if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'): ?>
        <div class="divider"></div>
        <?php if ($configuracaoInvalida): ?>
          <div class="notice" role="alert" style="background:#fdecea; color:#7a1408;">
            O serviço de validação está sem configuração. Contate o suporte institucional.
          </div>
        <?php elseif ($emitidoEm !== null): ?>
          <div class="notice" role="status" style="background:#eafaf1; border-color:rgba(10,200,10,0.25); color:#087a2d;">
            <i class="ti ti-circle-check" aria-hidden="true" style="font-size:24px;"></i>
            <div style="margin-left:10px;">
              <strong> Selo autêntico; dados assinados íntegros.</strong>
              <div>O selo foi gerado pelo Portal do Estudante e os dados vinculados não foram alterados. Isso não verifica a diagramação nem o restante do conteúdo do arquivo PDF.</div>
              <div>Estudante: <?= htmlspecialchars($emitidoEm['nome'], ENT_QUOTES, 'UTF-8') ?></div>
              <div>Matrícula: <?= htmlspecialchars($emitidoEm['matricula'], ENT_QUOTES, 'UTF-8') ?></div>
              <div>CPF: <?= htmlspecialchars($emitidoEm['cpf'], ENT_QUOTES, 'UTF-8') ?></div>
              <div>Curso: <?= htmlspecialchars($emitidoEm['curso'], ENT_QUOTES, 'UTF-8') ?></div>
              <div>Situação: <?= htmlspecialchars($emitidoEm['situacao'], ENT_QUOTES, 'UTF-8') ?></div>
              <div>Emissão: <?= htmlspecialchars(
                  (new DateTimeImmutable('@' . $emitidoEm['emitido_em']))
                      ->setTimezone(new DateTimeZone('America/Rio_Branco'))
                      ->format('d/m/Y H:i'),
                  ENT_QUOTES,
                  'UTF-8'
              ) ?> (horário do Acre).</div>
              <div>Compare os dados pessoais exibidos no documento com os dados do estudante.</div>
            </div>
          </div>
        <?php else: ?>
          <div class="notice" role="alert" style="background:#fdecea; border-color:rgba(200,10,10,0.25); color:#7a1408;">
            <i class="ti ti-alert-circle" aria-hidden="true" style="font-size:24px;"></i>
            <span>Código inválido ou alterado. Confira o código no documento.</span>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </main>
</div>
</body>
</html>

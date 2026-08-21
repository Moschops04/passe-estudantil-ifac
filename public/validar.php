<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/env.php';
carregar_env(__DIR__ . '/../.env');
require_once __DIR__ . '/../src/db.php';

$protocolo = trim($_GET['protocolo'] ?? '');
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $protocolo = trim($_POST['protocolo'] ?? '');
    
    if ($protocolo) {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT s.status, s.confirmado_em, a.nome, a.matricula, a.curso 
             FROM solicitacoes s 
             JOIN alunos a ON s.aluno_id = a.id 
             WHERE s.protocolo = :protocolo'
        );
        $stmt->execute(['protocolo' => $protocolo]);
        $resultado = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Validação de Autenticidade · IFAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="portal-wrapper">
  <?php $pagina_ativa = 'validar'; include __DIR__ . '/partials/header.php'; ?>

  <div class="main">
    <div class="page-header">
      <div class="page-title">Validação de Documentos</div>
      <div class="page-subtitle">Verifique a autenticidade de declarações emitidas pelo Portal do Estudante digitando o código impresso no rodapé do documento.</div>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
      <form method="POST" action="validar.php">
        <div class="field">
          <label>Código de Autenticidade (Protocolo)</label>
          <div class="matricula-wrapper">
            <input type="text" name="protocolo" placeholder="Ex: IFAC-2026-123456" value="<?= htmlspecialchars($protocolo) ?>" required />
            <button class="btn-verificar" type="submit">
              <i class="ti ti-search" aria-hidden="true"></i> Verificar
            </button>
          </div>
        </div>
      </form>

      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="divider"></div>
        
        <?php if ($resultado && $resultado['status'] === 'confirmada'): ?>
          <div class="notice" style="background:#eafaf1; border-color:rgba(10,200,10,0.25); color:#087a2d;">
            <i class="ti ti-circle-check" aria-hidden="true" style="font-size: 24px;"></i>
            <div style="margin-left: 10px;">
              <strong style="display:block; margin-bottom: 5px;">Documento Autêntico!</strong>
              Esta declaração foi emitida pelo IFAC para <strong><?= htmlspecialchars($resultado['nome']) ?></strong> (Matrícula: <?= htmlspecialchars($resultado['matricula']) ?>), do curso de <?= htmlspecialchars($resultado['curso']) ?>. Emitido em: <?= date('d/m/Y H:i', strtotime($resultado['confirmado_em'])) ?>.
            </div>
          </div>
        <?php else: ?>
          <div class="notice" style="background:#fdecea; border-color:rgba(200,10,10,0.25); color:#7a1408;">
            <i class="ti ti-alert-circle" aria-hidden="true" style="font-size: 24px;"></i>
            <div style="margin-left: 10px;">
              <strong style="display:block; margin-bottom: 5px;">Documento Não Encontrado ou Inválido!</strong>
              O código informado não consta em nossa base de dados como um documento emitido ou válido. Verifique se digitou corretamente.
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>

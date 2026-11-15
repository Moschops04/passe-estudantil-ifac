<?php
/**
 * Cabeçalho + barra de navegação, reaproveitado em todas as páginas.
 * Cada página define $pagina_ativa antes de dar include neste arquivo
 * ('declaracoes', 'validar' ou 'suporte') para destacar o link certo.
 */
$pagina_ativa = $pagina_ativa ?? '';
?>
<div class="top-bar">
  <img src="./assets/imagem/ifac.png" width="32" alt="Logo do IFAC">
  <div class="top-bar-name">Instituto Federal do Acre</div>
  <div class="top-bar-divider"></div>
  <div class="top-bar-sub">Portal do Estudante</div>

  <div style="margin-left: auto;">
    <button id="btn-contraste" onclick="document.body.classList.toggle('alto-contraste')" style="background:transparent; border:1px solid currentColor; color:inherit; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:12px; display:flex; align-items:center; gap:4px;">
      <i class="ti ti-contrast" aria-hidden="true"></i> Alto Contraste
    </button>
  </div>
</div>

<div class="nav-bar">
  <a class="nav-link <?= $pagina_ativa === 'declaracoes' ? 'active' : '' ?>" href="index.php">Declarações</a>
  <a class="nav-link <?= $pagina_ativa === 'validar' ? 'active' : '' ?>" href="validar.php">Validar documento</a>
  <a class="nav-link <?= $pagina_ativa === 'suporte' ? 'active' : '' ?>" href="suporte.php">Suporte</a>
</div>

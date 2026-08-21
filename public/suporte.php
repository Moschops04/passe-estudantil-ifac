<?php
declare(strict_types=1);
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Suporte · Portal do Estudante · IFAC</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="portal-wrapper">
  <?php $pagina_ativa = 'suporte'; include __DIR__ . '/partials/header.php'; ?>

  <div class="main">
    <div class="page-header">
      <div class="page-title">Central de Suporte</div>
      <div class="page-subtitle">Dúvidas frequentes sobre a declaração de passe estudantil e canais de atendimento da coordenação.</div>
    </div>

    <div class="support-intro">
      <i class="ti ti-info-circle" aria-hidden="true"></i>
      <p>Antes de entrar em contato, dá uma olhada nas perguntas frequentes abaixo — a maioria das dúvidas sobre prazo, token e validade do documento já está respondida ali. Se não resolver, os canais de contato estão logo em seguida.</p>
    </div>

    <div class="section-title"><i class="ti ti-help-circle" aria-hidden="true"></i> Consultar o status da minha solicitação</div>

    <div class="card" style="margin-bottom: 24px;">
      <div class="card-desc" style="margin-bottom: 14px;">Digite o número de protocolo que você recebeu ao concluir a solicitação.</div>
      <div class="status-form">
        <input type="text" id="protocolo-input" placeholder="Ex: IFAC-2026-123456" />
        <button class="btn-primary" id="btn-consultar-protocolo" type="button">
          <i class="ti ti-search" aria-hidden="true"></i> Consultar
        </button>
      </div>
      <div id="status-resultado" style="display:none"></div>
    </div>

    <div class="section-title"><i class="ti ti-message-question" aria-hidden="true"></i> Perguntas frequentes</div>

    <div class="faq-list">
      <div class="faq-item">
        <button class="faq-question" type="button">
          Quanto tempo demora para receber a declaração?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Depois que você confirma o código enviado por e-mail, a declaração é gerada e enviada ao seu e-mail institucional em até 1 dia útil.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          Não recebi o e-mail com o código. O que fazer?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Confira a caixa de spam/lixo eletrônico do seu e-mail institucional. Se ainda assim não chegar, use o botão "Reenviar código" na tela de confirmação — um novo código é gerado e o anterior deixa de valer.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          Minha matrícula não foi encontrada no sistema. Por quê?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Os dados do portal são sincronizados periodicamente com o SUAP. Se você acabou de se matricular ou rematricular, pode levar até um dia para os dados aparecerem por aqui. Se persistir, procure a coordenação do seu curso.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          O código expirou antes de eu conseguir digitar. E agora?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Sem problema — o código vale por 10 minutos por segurança. Basta clicar em "Reenviar código" para receber um novo, ou voltar ao início e refazer a solicitação.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          A declaração emitida por aqui tem validade oficial?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Sim. O documento é gerado com um código de verificação de autenticidade, que pode ser conferido pela empresa de transporte ou por qualquer terceiro para confirmar que ele é legítimo.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          Posso solicitar a declaração para outra pessoa?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Não. O código de confirmação é enviado exclusivamente para o e-mail institucional vinculado à sua matrícula, justamente para garantir que só o próprio estudante consiga gerar o documento.</div>
        </div>
      </div>
    </div>

    <div class="section-title"><i class="ti ti-headset" aria-hidden="true"></i> Canais de atendimento</div>

    <div class="contact-grid">
      <a class="contact-card" href="mailto:coordenacao.si@ifac.edu.br">
        <div class="contact-card-icon"><i class="ti ti-mail" aria-hidden="true"></i></div>
        <div>
          <div class="contact-card-title">E-mail da coordenação</div>
          <div class="contact-card-desc">coordenacao.si@ifac.edu.br</div>
        </div>
      </a>

      <div class="contact-card">
        <div class="contact-card-icon"><i class="ti ti-clock" aria-hidden="true"></i></div>
        <div>
          <div class="contact-card-title">Horário de atendimento</div>
          <div class="contact-card-desc">Segunda a sexta, 8h às 12h e 14h às 18h</div>
        </div>
      </div>

      <div class="contact-card">
        <div class="contact-card-icon"><i class="ti ti-map-pin" aria-hidden="true"></i></div>
        <div>
          <div class="contact-card-title">Atendimento presencial</div>
          <div class="contact-card-desc">Coordenação de Sistemas para Internet — Campus Rio Branco</div>
        </div>
      </div>

      <div class="contact-card">
        <div class="contact-card-icon"><i class="ti ti-brand-whatsapp" aria-hidden="true"></i></div>
        <div>
          <div class="contact-card-title">WhatsApp da coordenação</div>
          <div class="contact-card-desc">Disponível em horário de expediente</div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="assets/js/suporte.js"></script>
</body>
</html>

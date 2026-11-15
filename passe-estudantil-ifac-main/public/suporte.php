<?php
declare(strict_types=1);
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
      <p>Consulte as orientações sobre autenticação SUAP, emissão e validação da declaração. Se precisar, use os canais de atendimento abaixo.</p>
    </div>

    <div class="section-title"><i class="ti ti-message-question" aria-hidden="true"></i> Perguntas frequentes</div>
    <div class="faq-list">
      <div class="faq-item">
        <button class="faq-question" type="button">
          Como emito a declaração?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Informe sua matrícula e senha do SUAP na página de declarações. Após a autenticação e validação da situação acadêmica, o PDF é gerado e baixado nesta mesma sessão.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          O portal salva minha senha ou meus dados?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Não. A senha é usada apenas para autenticação na API oficial do SUAP. Os dados necessários e o token são mantidos em memória durante a emissão e não são gravados em banco local.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          Não consigo autenticar ou emitir o PDF. O que fazer?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Confira seus dados de acesso no SUAP. Se a matrícula estiver inativa ou houver indisponibilidade da API, procure a coordenação do seu curso.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          Como verifico a autenticidade do documento?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Acesse "Validar documento" e informe o código do rodapé. A página confirma a assinatura HMAC do selo e apresenta os dados vinculados para comparação com o PDF.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          O código é uma assinatura digital ICP-Brasil?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">Não. O código é um selo eletrônico de integridade/autenticidade com HMAC-SHA-256; ele não substitui assinatura PAdES nem certificado ICP-Brasil. Confirme com a instituição ou o órgão recebedor se esse formato atende à finalidade.</div>
        </div>
      </div>

      <div class="faq-item">
        <button class="faq-question" type="button">
          Posso emitir para outra pessoa?
          <i class="ti ti-chevron-down" aria-hidden="true"></i>
        </button>
        <div class="faq-answer">
          <div class="faq-answer-inner">O sistema autentica diretamente as credenciais do SUAP informadas e consulta o perfil associado a essa conta. Use apenas suas próprias credenciais.</div>
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

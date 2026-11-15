<?php
/**
 * Envio do e-mail com o código de confirmação (token).
 *
 * Usa SMTP de verdade (via PHPMailer) sempre que houver credenciais
 * configuradas no .env (SMTP_HOST, SMTP_USER, SMTP_PASS...).
 *
 * Sem SMTP configurado, cai automaticamente no "modo desenvolvimento":
 * não envia nada, só devolve true — quem chama (solicitar.php) então
 * mostra o token na própria tela, como já fazia antes. Assim o site
 * continua funcionando no computador de vocês mesmo sem e-mail
 * configurado, e passa a enviar de verdade assim que o .env for
 * preenchido (no PC de vocês ou no servidor da coordenação).
 */

require_once __DIR__ . '/vendor/phpmailer/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/** true se houver um SMTP configurado no .env */
function smtp_configurado(): bool
{
    return (bool) getenv('SMTP_HOST');
}

/**
 * Envia o e-mail com o token. Retorna true em caso de sucesso
 * (ou se estiver em modo desenvolvimento, sem SMTP configurado).
 * Lança PHPMailerException se o envio de verdade falhar.
 */
function enviar_email_token(string $destino, string $nomeAluno, string $token): bool
{
    if (!smtp_configurado()) {
        // Modo desenvolvimento: não envia nada, quem chamou decide
        // mostrar o token na tela (ver solicitar.php).
        return true;
    }

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = (int) (getenv('SMTP_PORT') ?: 587);
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER');
    $mail->Password = getenv('SMTP_PASS');
    $mail->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS; // 'tls' ou 'ssl'
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        getenv('SMTP_FROM') ?: getenv('SMTP_USER'),
        getenv('SMTP_FROM_NAME') ?: 'Portal do Estudante - IFAC'
    );
    $mail->addAddress($destino, $nomeAluno);

    $mail->Subject = 'Código de confirmação - Declaração de Passe Estudantil';
    $mail->isHTML(true);
    $mail->Body = corpo_email_token($nomeAluno, $token);
    $mail->AltBody = "Olá, {$nomeAluno}. Seu código de confirmação é: {$token}. Ele expira em 10 minutos.";

    // Lança PHPMailerException em caso de erro (quem chama decide o que fazer).
    return $mail->send();
}

function corpo_email_token(string $nomeAluno, string $token): string
{
    $nomeSeguro = htmlspecialchars($nomeAluno, ENT_QUOTES, 'UTF-8');
    return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; color:#111827;">
          <p>Olá, {$nomeSeguro}!</p>
          <p>Recebemos uma solicitação de declaração de passe estudantil. Use o código abaixo para confirmar:</p>
          <p style="font-size: 28px; font-weight: bold; letter-spacing: 4px; text-align:center;
                    background:#f4f5f7; padding: 16px; border-radius: 8px;">{$token}</p>
          <p style="font-size: 13px; color:#6b7280;">Este código expira em 10 minutos. Se você não fez essa solicitação, pode ignorar este e-mail.</p>
          <p style="font-size: 13px; color:#6b7280;">Instituto Federal do Acre — Portal do Estudante</p>
        </div>
        HTML;
}

/**
 * Envia o PDF gerado da declaração para o e-mail institucional do aluno.
 */
function enviar_email_declaracao(string $destino, string $nomeAluno, string $caminhoPdf): bool
{
    if (!smtp_configurado()) {
        return true;
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = getenv('SMTP_HOST');
    $mail->Port = (int) (getenv('SMTP_PORT') ?: 587);
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USER');
    $mail->Password = getenv('SMTP_PASS');
    $mail->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        getenv('SMTP_FROM') ?: getenv('SMTP_USER'),
        getenv('SMTP_FROM_NAME') ?: 'Portal do Estudante - IFAC'
    );
    $mail->addAddress($destino, $nomeAluno);

    $mail->Subject = 'Sua Declaração de Passe Estudantil';
    $mail->isHTML(true);
    
    $nomeSeguro = htmlspecialchars($nomeAluno, ENT_QUOTES, 'UTF-8');
    $mail->Body = <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; color:#111827;">
          <p>Olá, {$nomeSeguro}!</p>
          <p>A sua Declaração de Passe Estudantil foi gerada com sucesso e está em anexo.</p>
          <p style="font-size: 13px; color:#6b7280;">Instituto Federal do Acre — Portal do Estudante</p>
        </div>
        HTML;
    $mail->AltBody = "Olá, {$nomeAluno}. Sua declaração foi gerada com sucesso e está em anexo.";

    // Anexa o PDF
    $mail->addAttachment($caminhoPdf, 'Declaracao_Passe_Estudantil.pdf');

    return $mail->send();
}

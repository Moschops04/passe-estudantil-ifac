<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

if (empty($_SESSION['aluno_id'])) {
    json_resposta(['erro' => 'Verifique sua matrícula antes de solicitar a declaração.'], 401);
}

$dados = corpo_json();
$email = trim((string) ($dados['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_resposta(['erro' => 'Informe um e-mail válido.'], 422);
}

$pdo = db();
$token = gerar_token();
$protocolo = gerar_protocolo();

$aluno = $pdo->prepare('SELECT nome FROM alunos WHERE id = :id');
$aluno->execute(['id' => $_SESSION['aluno_id']]);
$nomeAluno = $aluno->fetchColumn() ?: 'estudante';

$stmt = $pdo->prepare(
    'INSERT INTO solicitacoes (protocolo, aluno_id, email_destino, token_hash, token_expira_em)
     VALUES (:protocolo, :aluno_id, :email, :token_hash, :expira)'
);
$stmt->execute([
    'protocolo' => $protocolo,
    'aluno_id' => $_SESSION['aluno_id'],
    'email' => $email,
    'token_hash' => password_hash($token, PASSWORD_DEFAULT),
    'expira' => date('Y-m-d H:i:s', time() + 600), // 10 minutos, igual ao protótipo
]);

$_SESSION['solicitacao_protocolo'] = $protocolo;

// Envia de verdade se houver SMTP configurado no .env. Sem SMTP
// configurado, enviar_email_token() não faz nada e retorna true — aí
// mostramos o token na tela mesmo (modo desenvolvimento).
try {
    enviar_email_token($email, $nomeAluno, $token);
} catch (\Throwable $e) {
    // Não expõe o erro técnico ao aluno; registra no log do servidor
    // para quem administra investigar (ex.: senha de SMTP errada).
    error_log('Falha ao enviar e-mail do token: ' . $e->getMessage());
    json_resposta(['erro' => 'Não foi possível enviar o e-mail agora. Tente novamente em instantes.'], 502);
}

$respostaDev = !smtp_configurado()
    || filter_var(getenv('APP_DEBUG') ?: '0', FILTER_VALIDATE_BOOLEAN);

json_resposta([
    'email_mascarado' => mascarar_email($email),
    'expira_em_segundos' => 600,
    'token_dev' => $respostaDev ? $token : null, // nunca aparece em produção com SMTP configurado
]);

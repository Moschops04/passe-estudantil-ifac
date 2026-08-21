<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

if (empty($_SESSION['solicitacao_protocolo'])) {
    json_resposta(['erro' => 'Nenhuma solicitação em andamento.'], 401);
}

$dados = corpo_json();
$token = trim((string) ($dados['token'] ?? ''));

if ($token === '') {
    json_resposta(['erro' => 'Informe o código recebido por e-mail.'], 422);
}

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM solicitacoes WHERE protocolo = :protocolo');
$stmt->execute(['protocolo' => $_SESSION['solicitacao_protocolo']]);
$solicitacao = $stmt->fetch();

if (!$solicitacao || $solicitacao['status'] !== 'aguardando_token') {
    json_resposta(['erro' => 'Solicitação inválida ou já processada.'], 404);
}

if (strtotime($solicitacao['token_expira_em']) < time()) {
    $pdo->prepare('UPDATE solicitacoes SET status = "expirada" WHERE id = :id')
        ->execute(['id' => $solicitacao['id']]);
    json_resposta(['erro' => 'Código expirado. Solicite um novo.'], 410);
}

// Máximo de 5 tentativas por solicitação, contra tentativa de força bruta do token.
if ($solicitacao['tentativas'] >= 5) {
    json_resposta(['erro' => 'Número de tentativas excedido. Solicite um novo código.'], 429);
}

if (!password_verify($token, $solicitacao['token_hash'])) {
    $pdo->prepare('UPDATE solicitacoes SET tentativas = tentativas + 1 WHERE id = :id')
        ->execute(['id' => $solicitacao['id']]);
    json_resposta(['erro' => 'Código incorreto.'], 401);
}

$pdo->prepare(
    'UPDATE solicitacoes SET status = "confirmada", confirmado_em = CURRENT_TIMESTAMP WHERE id = :id'
)->execute(['id' => $solicitacao['id']]);

require_once __DIR__ . '/../../src/pdf.php';

// Busca dados do aluno para o PDF
$stmt = $pdo->prepare('SELECT * FROM alunos WHERE id = :id');
$stmt->execute(['id' => $solicitacao['aluno_id']]);
$aluno = $stmt->fetch();

try {
    // Gera o PDF
    $caminhoPdf = gerar_pdf_declaracao($aluno, $solicitacao['protocolo']);
    
    // Tenta enviar o e-mail com o anexo
    enviar_email_declaracao($solicitacao['email_destino'], $aluno['nome'], $caminhoPdf);
} catch (\Throwable $e) {
    error_log('Erro ao gerar/enviar PDF: ' . $e->getMessage());
    // Mesmo se o e-mail falhar, a solicitação foi confirmada e o protocolo é válido.
}
unset($_SESSION['solicitacao_protocolo'], $_SESSION['aluno_id']);

json_resposta(['protocolo' => $solicitacao['protocolo']]);

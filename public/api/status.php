<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

$dados = corpo_json();
$protocolo = trim((string) ($dados['protocolo'] ?? ''));

if ($protocolo === '') {
    json_resposta(['erro' => 'Informe o número de protocolo.'], 422);
}

// Consulta pública: de propósito não expõe nome do aluno nem e-mail
// aqui, só o essencial para tirar a dúvida de "cadê minha declaração".
$stmt = db()->prepare('SELECT status, criado_em, confirmado_em FROM solicitacoes WHERE protocolo = :protocolo');
$stmt->execute(['protocolo' => $protocolo]);
$solicitacao = $stmt->fetch();

if (!$solicitacao) {
    json_resposta(['erro' => 'Protocolo não encontrado. Verifique se digitou corretamente.'], 404);
}

$rotulos = [
    'aguardando_token' => 'Aguardando confirmação do código enviado por e-mail.',
    'confirmada' => 'Confirmada. A declaração está sendo gerada e será enviada ao seu e-mail institucional.',
    'expirada' => 'O código de confirmação expirou. Faça uma nova solicitação.',
    'cancelada' => 'Solicitação cancelada.',
];

json_resposta([
    'status' => $solicitacao['status'],
    'mensagem' => $rotulos[$solicitacao['status']] ?? 'Status desconhecido.',
    'criado_em' => $solicitacao['criado_em'],
]);

<?php
declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

$dados = corpo_json();
$matricula = trim((string) ($dados['matricula'] ?? ''));

if ($matricula === '') {
    json_resposta(['erro' => 'Informe a matrícula.'], 422);
}

$stmt = db()->prepare('SELECT * FROM alunos WHERE matricula = :matricula');
$stmt->execute(['matricula' => $matricula]);
$aluno = $stmt->fetch();

if (!$aluno) {
    // Mensagem genérica de propósito: não revela se a matrícula existe
    // ou não em outra base, só que não foi encontrada na sincronização local.
    json_resposta(['erro' => 'Matrícula não encontrada. Verifique o número informado.'], 404);
}

// Guarda o aluno identificado na sessão para os próximos passos —
// assim ninguém consegue solicitar declaração em nome de outra
// matrícula só trocando o valor enviado no passo seguinte.
$_SESSION['aluno_id'] = $aluno['id'];

json_resposta([
    'nome' => $aluno['nome'],
    'curso' => $aluno['curso'],
    'semestre' => $aluno['semestre'],
    'email_institucional' => $aluno['email_institucional'],
]);

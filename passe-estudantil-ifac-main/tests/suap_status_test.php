<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/suap.php';

$casos = [
    ['Matriculado', null, 'ativa'],
    ['MATRICULADO(A)', null, 'ativa'],
    ['Regularmente matriculada', null, 'ativa'],
    ['Matrícula ativa', null, 'ativa'],
    ['Cursando', null, 'ativa'],
    ['Concluído', null, 'inativa'],
    ['Matrícula cancelada', null, 'inativa'],
    ['Trancado', null, 'inativa'],
    ['', true, 'ativa'],
    ['', false, 'inativa'],
    ['Pendente de matrícula', null, 'desconhecida'],
    ['', null, 'desconhecida'],
];

foreach ($casos as [$situacao, $ativo, $esperado]) {
    $resultado = classificar_situacao_matricula($situacao, $ativo);
    if ($resultado !== $esperado) {
        throw new RuntimeException(
            'Classificação incorreta para "' . $situacao . '": esperado ' .
            $esperado . ', recebido ' . $resultado . '.'
        );
    }
}

if (texto_suap(['descricao_situacao' => 'Matriculado']) !== 'Matriculado') {
    throw new RuntimeException('Não foi possível ler situação no formato objeto.');
}

echo "Testes de situação acadêmica SUAP OK.\n";

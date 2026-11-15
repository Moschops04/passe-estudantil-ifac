<?php
declare(strict_types=1);

putenv('APP_INTEGRITY_SECRET=' . str_repeat('a', 64));
require_once __DIR__ . '/../src/integridade.php';

$aluno = [
    'nome' => 'Estudante de Teste',
    'matricula' => '2024001234',
    'cpf' => '12345678901',
    'curso' => 'Sistemas para Internet',
    'situacao' => 'Ativo',
];
$emitidoEm = time();
$codigo = criar_codigo_validacao($aluno, $emitidoEm);
$validado = validar_codigo_validacao($codigo);

if (!is_array($validado)
    || $validado['nome'] !== $aluno['nome']
    || $validado['matricula'] !== $aluno['matricula']
    || $validado['cpf'] !== $aluno['cpf']
    || $validado['curso'] !== $aluno['curso']
    || $validado['emitido_em'] !== $emitidoEm
) {
    throw new RuntimeException('O código gerado não validou com os dados originais.');
}

$codigoAlterado = substr($codigo, 0, -1) . ($codigo[strlen($codigo) - 1] === 'A' ? 'B' : 'A');
if (validar_codigo_validacao($codigoAlterado) !== null) {
    throw new RuntimeException('O validador aceitou um código alterado.');
}

echo "Testes de integridade OK.\n";

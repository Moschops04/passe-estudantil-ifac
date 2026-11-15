<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/pdf.php';

$aluno = [
    'nome' => 'Estudante de Teste',
    'matricula' => '2024001234',
    'cpf' => '12345678901',
    'curso' => 'Sistemas para Internet',
    'situacao' => 'Ativo',
];
$codigo = 'IFAC1.' . str_repeat('a', 500) . '.' . str_repeat('b', 43);
$pdf = gerar_pdf_declaracao($aluno, $codigo, time());

if (substr($pdf, 0, 5) !== '%PDF-' || strpos($pdf, '%%EOF') === false) {
    throw new RuntimeException('O gerador não retornou um documento PDF completo.');
}

echo "Geração PDF em memória OK (" . strlen($pdf) . " bytes).\n";

<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/suap.php';

$casos = [
    [
        ['results' => [
            ['tipo' => 'Servidor', 'detalhamento' => ['cargo' => 'Docente', 'categoria' => 'Ativo']],
            [
                'tipo' => 'Aluno',
                'detalhamento' => [
                    'modalidade' => 'Presencial',
                    'nivel_ensino' => 'Superior',
                    'curso' => 'Sistemas para Internet',
                    'ativo' => true,
                ],
            ],
        ]],
        ['encontrado' => true, 'ativo' => true, 'curso' => 'Sistemas para Internet'],
    ],
    [
        ['results' => [[
            'tipo' => 'Aluno',
            'detalhamento' => [
                'modalidade' => 'Presencial',
                'nivel_ensino' => 'Superior',
                'curso' => 'Sistemas para Internet',
                'ativo' => false,
            ],
        ]]],
        ['encontrado' => true, 'ativo' => false, 'curso' => ''],
    ],
    [
        ['results' => [[
            'tipo' => 'Aluno',
            'detalhamento' => [
                'modalidade' => 'Presencial',
                'nivel_ensino' => 'Superior',
                'curso' => 'Sistemas para Internet',
                'ativo' => 1,
            ],
        ]]],
        ['encontrado' => true, 'ativo' => true, 'curso' => 'Sistemas para Internet'],
    ],
    [
        ['results' => [[
            'tipo' => 'Aluno',
            'situacao' => 'Migrado do SIGAA para o SUAP',
            'detalhamento' => [
                'modalidade' => 'Presencial',
                'nivel_ensino' => 'Superior',
                'curso' => 'Sistemas para Internet',
                'ativo' => true,
            ],
        ]]],
        ['encontrado' => true, 'ativo' => true, 'curso' => 'Sistemas para Internet'],
    ],
    [
        ['results' => []],
        ['encontrado' => false, 'ativo' => false, 'curso' => ''],
    ],
];

foreach ($casos as [$resposta, $esperado]) {
    $resultado = avaliar_vinculos_estudante($resposta);
    if ($resultado !== $esperado) {
        throw new RuntimeException('A avaliação do vínculo estudantil não corresponde ao esperado.');
    }
}

echo "Testes de vínculo estudantil SUAP OK.\n";

<?php
declare(strict_types=1);

function segredo_integridade(): string
{
    $segredo = getenv('APP_INTEGRITY_SECRET') ?: '';
    if (strlen($segredo) < 64) {
        throw new RuntimeException('APP_INTEGRITY_SECRET precisa ter pelo menos 64 caracteres.');
    }

    return $segredo;
}

function criar_codigo_validacao(array $aluno, int $emitidoEm): string
{
    $campos = [
        'nome' => $aluno['nome'],
        'matricula' => $aluno['matricula'],
        'cpf' => $aluno['cpf'],
        'curso' => $aluno['curso'],
        'situacao' => $aluno['situacao'],
        'emitido_em' => $emitidoEm,
    ];
    $conteudo = json_encode($campos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($conteudo === false) {
        throw new RuntimeException('Não foi possível preparar o conteúdo da declaração.');
    }

    $payload = [
        'v' => 1,
        't' => $emitidoEm,
        'h' => hash('sha256', $conteudo),
        'n' => $aluno['nome'],
        'm' => $aluno['matricula'],
        'c' => $aluno['cpf'],
        'r' => $aluno['curso'],
        's' => $aluno['situacao'],
    ];
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonPayload === false) {
        throw new RuntimeException('Não foi possível preparar o selo de autenticidade.');
    }

    $codificado = rtrim(strtr(base64_encode($jsonPayload), '+/', '-_'), '=');
    $assinatura = rtrim(strtr(
        base64_encode(hash_hmac('sha256', 'IFAC1|' . $codificado, segredo_integridade(), true)),
        '+/',
        '-_'
    ), '=');

    return 'IFAC1.' . $codificado . '.' . $assinatura;
}

function validar_codigo_validacao(string $codigo): ?array
{
    $codigo = preg_replace('/\s+/', '', trim($codigo)) ?? '';
    if (!preg_match('/^IFAC1\.([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]{43})$/', $codigo, $partes)) {
        return null;
    }

    $assinaturaEsperada = rtrim(strtr(
        base64_encode(hash_hmac('sha256', 'IFAC1|' . $partes[1], segredo_integridade(), true)),
        '+/',
        '-_'
    ), '=');
    if (!hash_equals($assinaturaEsperada, $partes[2])) {
        return null;
    }

    $jsonPayload = base64_decode(strtr($partes[1], '-_', '+/'), true);
    $payload = is_string($jsonPayload) ? json_decode($jsonPayload, true) : null;
    if (!is_array($payload) || ($payload['v'] ?? null) !== 1) {
        return null;
    }

    $campos = [
        'nome' => $payload['n'] ?? null,
        'matricula' => $payload['m'] ?? null,
        'cpf' => $payload['c'] ?? null,
        'curso' => $payload['r'] ?? null,
        'situacao' => $payload['s'] ?? null,
        'emitido_em' => $payload['t'] ?? null,
    ];
    if (!is_int($payload['t'] ?? null) || $payload['t'] > time() + 300) {
        return null;
    }
    foreach (['nome', 'matricula', 'cpf', 'curso', 'situacao'] as $campo) {
        if (!is_string($campos[$campo])) {
            return null;
        }
    }

    $conteudo = json_encode($campos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($conteudo === false || !hash_equals(hash('sha256', $conteudo), (string) ($payload['h'] ?? ''))) {
        return null;
    }

    return $campos;
}

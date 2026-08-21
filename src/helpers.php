<?php
/**
 * Funções auxiliares do portal.
 */

/** Gera um número de protocolo no padrão IFAC-AAAA-NNNNNN */
function gerar_protocolo(): string
{
    return 'IFAC-' . date('Y') . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/** Gera um token numérico de 6 dígitos */
function gerar_token(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/** Mascara um e-mail para exibição: jo****@aluno.ifac.edu.br */
function mascarar_email(string $email): string
{
    [$usuario, $dominio] = array_pad(explode('@', $email, 2), 2, '');
    $visivel = mb_substr($usuario, 0, 2);
    return $visivel . str_repeat('*', max(4, mb_strlen($usuario) - 2)) . '@' . $dominio;
}

/** Resposta JSON padronizada + encerra o script */
function json_resposta(array $dados, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Lê o corpo JSON da requisição como array associativo */
function corpo_json(): array
{
    $bruto = file_get_contents('php://input');
    $dados = json_decode($bruto, true);
    return is_array($dados) ? $dados : [];
}

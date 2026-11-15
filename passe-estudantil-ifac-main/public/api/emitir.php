<?php
declare(strict_types=1);

header('Cache-Control: no-store, private, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');

function resposta_erro(string $mensagem, int $status): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    resposta_erro('Método não permitido.', 405);
}

$tipoConteudo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
if ($tipoConteudo !== 'application/json') {
    resposta_erro('Envie matrícula e senha no formato JSON.', 415);
}

$tamanho = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($tamanho > 16384) {
    resposta_erro('Requisição muito grande.', 413);
}

$corpo = file_get_contents('php://input', false, null, 0, 16385);
if ($corpo === false || strlen($corpo) > 16384) {
    resposta_erro('Requisição muito grande.', 413);
}
$dados = json_decode($corpo, true);
unset($corpo);
if (!is_array($dados)) {
    resposta_erro('Requisição inválida.', 400);
}

$matricula = $dados['matricula'] ?? null;
$senha = $dados['senha'] ?? null;
unset($dados);
if (!is_string($matricula) || !is_string($senha)) {
    unset($senha);
    resposta_erro('Informe matrícula e senha do SUAP.', 422);
}
$matricula = trim($matricula);
if ($matricula === '' || strlen($matricula) > 64 || $senha === '' || strlen($senha) > 256) {
    unset($senha);
    resposta_erro('Informe matrícula e senha do SUAP.', 422);
}

require_once __DIR__ . '/../../src/env.php';
carregar_env(__DIR__ . '/../../.env');
require_once __DIR__ . '/../../src/suap.php';
require_once __DIR__ . '/../../src/integridade.php';
require_once __DIR__ . '/../../src/pdf.php';

try {
    $aluno = consultar_dados_estudante($matricula, $senha);
    unset($senha, $matricula);

    $emitidoEm = time();
    $codigo = criar_codigo_validacao($aluno, $emitidoEm);
    $pdf = gerar_pdf_declaracao($aluno, $codigo, $emitidoEm);
    unset($aluno, $codigo);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="declaracao-passe-estudantil.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    unset($pdf);
} catch (SuapException $erro) {
    unset($senha, $matricula, $aluno, $codigo, $pdf);
    $status = $erro->httpStatus;
    $mensagem = $status === 401
        ? 'Matrícula ou senha do SUAP inválida.'
        : ($status === 403
            ? $erro->getMessage()
            : ($status >= 500 ? 'Serviço temporariamente indisponível. Tente novamente.' : $erro->getMessage()));
    resposta_erro($mensagem, $status);
} catch (Throwable $erro) {
    unset($senha, $matricula, $aluno, $codigo, $pdf);
    error_log('Falha ao emitir declaração IFAC (' . get_class($erro) . ').');
    resposta_erro('Não foi possível gerar a declaração. Tente novamente mais tarde.', 500);
}

<?php
declare(strict_types=1);

final class SuapException extends RuntimeException
{
    public function __construct(string $message, public readonly int $httpStatus = 502)
    {
        parent::__construct($message);
    }
}

function suap_url(string $path): string
{
    $base = rtrim(getenv('SUAP_API_BASE') ?: 'https://suap.ifac.edu.br/api', '/');
    if (parse_url($base, PHP_URL_SCHEME) !== 'https') {
        throw new SuapException('A URL da API SUAP deve usar HTTPS.', 500);
    }

    return $base . '/' . ltrim($path, '/');
}

function suap_requisicao(string $path, array $dados, ?string $token = null, bool $post = true): array
{
    if (!function_exists('curl_init')) {
        error_log('SUAP request unavailable: PHP cURL extension is not loaded.');
        throw new SuapException('A extensão PHP cURL não está instalada.', 500);
    }

    $cabecalhos = ['Accept: application/json'];
    if ($post) {
        $cabecalhos[] = 'Content-Type: application/json';
    }
    if ($token !== null) {
        $cabecalhos[] = 'Authorization: Bearer ' . $token;
    }

    $opcoesCurl = [
        CURLOPT_HTTPHEADER => $cabecalhos,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ];
    $caBundle = getenv('SUAP_CA_BUNDLE') ?: '';
    if ($caBundle !== '') {
        if (!is_file($caBundle) || !is_readable($caBundle)) {
            throw new SuapException('O bundle de certificados CA configurado não foi encontrado ou não pode ser lido.', 500);
        }
        $opcoesCurl[CURLOPT_CAINFO] = $caBundle;
    }

    $corpo = null;
    if ($post) {
        $corpo = json_encode($dados, JSON_UNESCAPED_UNICODE);
        if ($corpo === false) {
            throw new SuapException('Não foi possível preparar a requisição ao SUAP.', 500);
        }
    }

    $curl = curl_init(suap_url($path));
    curl_setopt_array($curl, $opcoesCurl);
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $corpo);
    } else {
        curl_setopt($curl, CURLOPT_HTTPGET, true);
    }

    $resposta = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $erroCurl = curl_errno($curl);
    $mensagemCurl = curl_error($curl);
    curl_close($curl);
    unset($corpo, $cabecalhos, $opcoesCurl, $caBundle);
    $rotaLog = preg_replace('/[^A-Za-z0-9._~\/-]/', '?', $path) ?? '';

    if ($resposta === false || $erroCurl !== 0) {
        $mensagemCurl = preg_replace('/[\r\n\x00-\x1F]/', ' ', $mensagemCurl) ?? '';
        error_log('SUAP cURL failure for ' . $rotaLog . ' (' . $erroCurl . '): ' . $mensagemCurl);
        throw new SuapException('Não foi possível conectar à API do SUAP. Tente novamente.');
    }

    if ($status >= 400) {
        unset($resposta);
        error_log('SUAP API returned HTTP ' . $status . ' for ' . $rotaLog . '.');
        throw new SuapException('A API do SUAP recusou a requisição.', $status);
    }

    $dadosResposta = json_decode($resposta, true);
    unset($resposta);
    if (!is_array($dadosResposta)) {
        throw new SuapException('A API do SUAP retornou uma resposta inválida.');
    }

    return $dadosResposta;
}

function campo_suap(array $perfil, array $nomes): mixed
{
    foreach ($nomes as $nome) {
        if (array_key_exists($nome, $perfil)) {
            return $perfil[$nome];
        }
    }

    return null;
}

function texto_suap(mixed $valor): string
{
    if (is_array($valor)) {
        $valor = campo_suap($valor, [
            'nome', 'descricao', 'descricao_historico', 'descricao_situacao',
            'situacao', 'status', 'codigo', 'valor',
        ]);
    }

    if (!is_scalar($valor)) {
        return '';
    }

    $texto = preg_replace('/[\x00-\x1F\x7F]/', ' ', (string) $valor);
    return trim($texto ?? '');
}

function vinculo_suap(array $perfil): array
{
    $vinculo = $perfil['vinculo'] ?? null;
    if (is_array($vinculo)) {
        return $vinculo;
    }

    return [];
}

function classificar_situacao_matricula(string $situacao, mixed $matriculaAtiva = null): string
{
    $situacao = strtoupper(trim($situacao));
    $normalizada = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $situacao);
    if (is_string($normalizada)) {
        $situacao = strtoupper($normalizada);
    }
    $situacao = str_replace(["'", '"', '`'], '', $situacao);
    $situacao = preg_replace('/[^A-Z0-9]+/', ' ', $situacao) ?? '';
    $situacao = trim(preg_replace('/\s+/', ' ', $situacao) ?? '');

    if (in_array($matriculaAtiva, [false, 0, '0', 'false', 'False'], true)) {
        return 'inativa';
    }

    $situacoesInativas = [
        'INATIVO', 'INATIVA', 'TRANCADO', 'TRANCADA', 'CANCELADO', 'CANCELADA',
        'EVADIDO', 'EVADIDA', 'ABANDONADO', 'ABANDONADA', 'DESLIGADO', 'DESLIGADA',
        'CONCLUIDO', 'CONCLUIDA', 'TRANSFERIDO', 'TRANSFERIDA',
    ];
    foreach ($situacoesInativas as $inativa) {
        if (str_contains($situacao, $inativa)) {
            return 'inativa';
        }
    }

    $situacoesAtivas = [
        'ATIVO', 'ATIVA', 'MATRICULADO', 'MATRICULADA', 'MATRICULA ATIVA',
        'REGULAR', 'REGULARMENTE', 'CURSANDO', 'EM CURSO',
    ];
    foreach ($situacoesAtivas as $ativa) {
        if ($situacao === $ativa || str_starts_with($situacao, $ativa . ' ')) {
            return 'ativa';
        }
    }

    if ($situacao === '' && in_array($matriculaAtiva, [true, 1, '1', 'true', 'True'], true)) {
        return 'ativa';
    }

    return 'desconhecida';
}

function consultar_dados_estudante(string $matricula, string $senha): array
{
    $caminhoToken = getenv('SUAP_TOKEN_PATH') ?: '/token/pair';
    $caminhoPerfilPessoal = getenv('SUAP_PERSON_PATH') ?: '/rh/eu/';
    $caminhoPerfilAcademico = getenv('SUAP_PROFILE_PATH') ?: '/ensino/meus-dados-aluno/';

    try {
        $autenticacao = suap_requisicao($caminhoToken, [
            'username' => $matricula,
            'password' => $senha,
        ]);
    } finally {
        unset($senha);
    }

    $token = $autenticacao['access'] ?? null;
    unset($autenticacao);
    if (!is_string($token) || !preg_match('/^[A-Za-z0-9._~-]+$/D', $token)) {
        throw new SuapException('A API do SUAP não retornou um token de acesso.');
    }

    try {
        $perfilPessoal = suap_requisicao($caminhoPerfilPessoal, [], $token, false);
        $perfilAcademico = suap_requisicao($caminhoPerfilAcademico, [], $token, false);
    } catch (SuapException $erro) {
        unset($token);
        if ($erro->httpStatus === 401 || $erro->httpStatus === 403) {
            throw new SuapException(
                'O SUAP autenticou o acesso, mas recusou a consulta do perfil do estudante.',
                403
            );
        }
        throw $erro;
    }
    unset($token);

    if (isset($perfilPessoal['data']) && is_array($perfilPessoal['data'])) {
        $perfilPessoal = $perfilPessoal['data'];
    }
    if (isset($perfilAcademico['data']) && is_array($perfilAcademico['data'])) {
        $perfilAcademico = $perfilAcademico['data'];
    } elseif (isset($perfilAcademico['results'][0]) && is_array($perfilAcademico['results'][0])) {
        $perfilAcademico = $perfilAcademico['results'][0];
    }

    $vinculo = vinculo_suap($perfilPessoal);
    $situacao = texto_suap(campo_suap($perfilAcademico, [
        'situacao', 'situacao_matricula', 'situacao_aluno', 'situacao_vinculo',
        'descricao_situacao', 'status_matricula', 'status',
    ]));
    if ($situacao === '') {
        $situacao = texto_suap(campo_suap($vinculo, [
            'situacao', 'situacao_matricula', 'situacao_aluno', 'situacao_vinculo',
            'descricao_situacao', 'status_matricula', 'status',
        ]));
    }
    $ativo = campo_suap($perfilAcademico, ['matricula_ativa']);
    if ($ativo === null) {
        $ativo = campo_suap($vinculo, ['matricula_ativa', 'ativo']);
    }

    $classificacaoSituacao = classificar_situacao_matricula($situacao, $ativo);
    if ($classificacaoSituacao === 'inativa') {
        throw new SuapException('A situação da matrícula não está ativa. Procure a coordenação acadêmica.', 403);
    }
    if ($classificacaoSituacao === 'desconhecida') {
        if ($situacao === '') {
            throw new SuapException(
                'A API do SUAP não retornou a situação acadêmica do aluno. Confira o perfil no SUAP ou fale com a coordenação.',
                422
            );
        }

        throw new SuapException(
            'O SUAP retornou uma situação acadêmica não reconhecida ("' . $situacao .
            '"). Confira essa situação no perfil acadêmico ou fale com a coordenação.',
            422
        );
    }

    $aluno = [
        'nome' => texto_suap(campo_suap($perfilPessoal, [
            'nome', 'nome_completo', 'nome_usual', 'nome_social', 'nome_registro',
        ])),
        'matricula' => texto_suap(campo_suap($perfilPessoal, [
            'identificacao', 'matricula', 'registro_academico',
        ])),
        'cpf' => texto_suap(campo_suap($perfilPessoal, ['cpf']))
            ?: texto_suap(campo_suap($perfilAcademico, ['cpf'])),
        'curso' => texto_suap(campo_suap($perfilAcademico, ['curso', 'descricao_curso']))
            ?: texto_suap(campo_suap($vinculo, ['curso', 'descricao_curso'])),
        'situacao' => 'Ativo',
    ];

    unset($perfilPessoal, $perfilAcademico, $vinculo);
    foreach (['nome', 'matricula', 'cpf', 'curso'] as $campo) {
        if ($aluno[$campo] === '') {
            throw new SuapException(
                'A API do SUAP não retornou todos os dados necessários. Verifique SUAP_PERSON_PATH e SUAP_PROFILE_PATH.',
                422
            );
        }
    }
    foreach (['nome' => 240, 'matricula' => 64, 'cpf' => 24, 'curso' => 200] as $campo => $limite) {
        if (strlen($aluno[$campo]) > $limite) {
            throw new SuapException('A API do SUAP retornou um campo fora do tamanho esperado.', 422);
        }
    }

    return $aluno;
}

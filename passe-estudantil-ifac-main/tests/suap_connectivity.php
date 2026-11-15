<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/env.php';
carregar_env(__DIR__ . '/../.env');

if (!function_exists('curl_init')) {
    fwrite(STDERR, "Extensão PHP cURL não carregada.\n");
    exit(1);
}

$options = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
];

$caBundle = getenv('SUAP_CA_BUNDLE') ?: '';
if ($caBundle !== '') {
    if (!is_file($caBundle) || !is_readable($caBundle)) {
        fwrite(STDERR, "Bundle CA configurado não encontrado ou ilegível.\n");
        exit(1);
    }
    $options[CURLOPT_CAINFO] = $caBundle;
}

$curl = curl_init('https://suap.ifac.edu.br/api/token/pair');
curl_setopt_array($curl, $options);

$resposta = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
$erroCurl = curl_error($curl);
$codigoErro = curl_errno($curl);
curl_close($curl);
unset($resposta);

if ($codigoErro !== 0) {
    fwrite(STDERR, "Falha HTTPS/cURL ($codigoErro): $erroCurl\n");
    exit(1);
}

if ($status !== 405) {
    fwrite(STDERR, "A rota pública de token respondeu HTTP $status; esperado 405 para GET sem credenciais.\n");
    exit(1);
}

echo "Conexão HTTPS/cURL com o SUAP OK; HTTP 405 esperado para GET sem credenciais.\n";

<?php
/**
 * Leitor bem simples de arquivo .env (sem depender de nenhuma
 * biblioteca externa/composer). Carrega as variáveis em getenv() se
 * ainda não estiverem definidas (variáveis já existentes no ambiente
 * sempre têm prioridade).
 */
function carregar_env(string $caminho): void
{
    if (!file_exists($caminho)) {
        return;
    }

    foreach (file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");

        if (getenv($chave) === false) {
            putenv("$chave=$valor");
            $_ENV[$chave] = $valor;
        }
    }
}

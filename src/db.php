<?php
/**
 * Conexão com o banco de dados local.
 *
 * Usa SQLite em desenvolvimento (zero configuração, roda em qualquer
 * máquina) para vocês testarem o fluxo completo sem precisar subir
 * MySQL/Postgres. Quando for para o servidor da coordenação
 * (coordenacao.ifac.si), basta trocar esta função por uma conexão
 * PDO para o MySQL — o resto do código (que usa apenas PDO) não muda.
 */

function db(): PDO
{


    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    // Conecta no MySQL do XAMPP que criamos!
    $pdo = new PDO('mysql:host=localhost;dbname=passe_estudantil;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}



<?php
declare(strict_types=1);

// Define que qualquer erro interno vire um log no XAMPP e devolva um JSON 500 para o front
set_exception_handler(function (\Throwable $e) {
    error_log("Erro Fatal na API: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage() . ' na linha ' . $e->getLine()]);
    exit;
});


session_start();

require_once __DIR__ . '/env.php';
carregar_env(__DIR__ . '/../.env');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mailer.php';

// Endpoints só aceitam POST com corpo JSON.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_resposta(['erro' => 'Método não permitido.'], 405);
}

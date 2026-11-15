<?php
declare(strict_types=1);
http_response_code(410);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['erro' => 'Fluxo antigo desativado. Use a emissão autenticada pelo SUAP.'], JSON_UNESCAPED_UNICODE);

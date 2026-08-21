<?php
$_SERVER["REQUEST_METHOD"] = "POST";
$_SESSION["aluno_id"] = 1;
require "src/env.php";
require "src/db.php";
function corpo_json() { return ["email" => "teste@aluno.ifac.edu.br"]; }
function json_resposta($data, $code = 200) { echo json_encode($data); exit; }
function gerar_token() { return "123456"; }
function gerar_protocolo() { return "PROT-123"; }
function mascarar_email($e) { return $e; }
function smtp_configurado() { return false; }
function enviar_email_token($e, $n, $t) { return true; }

$email = "teste@aluno.ifac.edu.br";
$pdo = db();
$aluno = $pdo->prepare("SELECT nome FROM alunos WHERE id = :id");
$aluno->execute(["id" => $_SESSION["aluno_id"]]);
$nomeAluno = $aluno->fetchColumn() ?: "estudante";

$token = "123456";
$respostaDev = !smtp_configurado();
json_resposta([
    "email_mascarado" => $email,
    "expira_em_segundos" => 600,
    "token_dev" => $respostaDev ? $token : null,
]);


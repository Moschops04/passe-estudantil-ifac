<?php
$_SERVER["REQUEST_METHOD"] = "POST";
$dados = ["email" => "teste@aluno.ifac.edu.br"];
$json = json_encode($dados);
file_put_contents("php://input", $json); // This is not possible to mock php://input easily this way.


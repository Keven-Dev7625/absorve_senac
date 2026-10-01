<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "senac_absorve";

$conn = mysqli_connect(
    $servidor,
    $usuario,
    $senha,
    $banco
);

if (!$conn) {
    die("Erro na conexão: " . mysqli_connect_error());
}

?>
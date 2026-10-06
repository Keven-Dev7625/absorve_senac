<?php

session_start();

include_once("../conexao.php");

$login = $_POST['login'];
$senha = $_POST['senha'];

$sql = "SELECT * FROM admin 
        WHERE login = '$login' 
        AND senha = '$senha'";

$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) == 1) {

    $admin = mysqli_fetch_assoc($resultado);

    $_SESSION['admin'] = $admin['login'];
    $_SESSION['id_admin'] = $admin['id_admin'];

    header("Location: cons_abs.php");
    exit();

} else {

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>


<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Erro de Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/style.css">


</head>

<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center min-vh-100">


<div class="card shadow-lg border-0 rounded-4 text-center"
     style="max-width: 450px; width: 100%;">

    <div class="card-body p-5">

        <div class="display-3 mb-3">
            ❌
        </div>

        <h2 class="fw-bold text-danger mb-3">
            Usuário ou senha incorretos!
        </h2>

        <p class="text-muted mb-4">
            Verifique seus dados e tente novamente.
        </p>

        <a href="adm.html"
           class="btn btn-primary btn-lg rounded-3 px-4">
            ← Tentar novamente
        </a>

    </div>

</div>


</div>

</body>

</html>

<?php
}

?>
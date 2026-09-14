<?php

session_start();

include_once("../conexao.php");


// VERIFICA SE O ADMIN ESTÁ LOGADO
if (!isset($_SESSION['admin'])) {

    header("Location: adm.html");
    exit();

}


// ATUALIZAR QUANTIDADE
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $andar = $_POST["andar"];
    $quantidade = intval($_POST["quantidade"]);


    if ($quantidade < 0) {
        $quantidade = 0;
    }


    if ($andar == "terreo") {

        $sql = "INSERT INTO terreo (id_terreo, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";

    }


    elseif ($andar == "piso1") {

        $sql = "INSERT INTO piso1 (id_piso1, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";

    }


    elseif ($andar == "piso2") {

        $sql = "INSERT INTO piso2 (id_piso2, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";

    }


    elseif ($andar == "piso3") {

        $sql = "INSERT INTO piso3 (id_piso3, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";

    }


    if (isset($sql)) {

        mysqli_query($conn, $sql);

    }


    header("Location: cons_abs.php?atualizado=1");
    exit();

}



// BUSCAR QUANTIDADES


// TÉRREO
$sql = "SELECT quantidade FROM terreo WHERE id_terreo = 1";

$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) > 0) {

    $dados = mysqli_fetch_assoc($resultado);
    $terreo = $dados["quantidade"];

} else {

    $terreo = 0;

}



// PISO 1
$sql = "SELECT quantidade FROM piso1 WHERE id_piso1 = 1";

$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) > 0) {

    $dados = mysqli_fetch_assoc($resultado);
    $piso1 = $dados["quantidade"];

} else {

    $piso1 = 0;

}



// PISO 2
$sql = "SELECT quantidade FROM piso2 WHERE id_piso2 = 1";

$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) > 0) {

    $dados = mysqli_fetch_assoc($resultado);
    $piso2 = $dados["quantidade"];

} else {

    $piso2 = 0;

}



// PISO 3
$sql = "SELECT quantidade FROM piso3 WHERE id_piso3 = 1";

$resultado = mysqli_query($conn, $sql);

if (mysqli_num_rows($resultado) > 0) {

    $dados = mysqli_fetch_assoc($resultado);
    $piso3 = $dados["quantidade"];

} else {

    $piso3 = 0;

}

?>


<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Controle de Absorventes</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

          <link rel="stylesheet" href="../css/style.css">


</head>


<body class="bg-light">


<div class="container py-5">


    <div class="text-center mb-5">

        <h1 class="fw-bold text-primary">
            Controle de Absorventes
        </h1>

        <p class="text-muted">
            Visualize e atualize a quantidade disponível em cada andar
        </p>

    </div>



    <?php if (isset($_GET["atualizado"])) { ?>

        <div class="alert alert-success text-center">

            Quantidade atualizada com sucesso!

        </div>

    <?php } ?>



    <div class="row g-4">


        <!-- TÉRREO -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow border-0 rounded-4 text-center">

                <div class="card-body p-4">

                    <h3 class="fw-bold">
                        Térreo
                    </h3>


                    <div class="display-4 fw-bold text-primary my-3">

                        <?php echo $terreo; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form method="POST">

                        <input type="hidden"
                               name="andar"
                               value="terreo">


                        <input type="number"
                               name="quantidade"
                               value="<?php echo $terreo; ?>"
                               min="0"
                               required
                               class="form-control text-center mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar

                        </button>

                    </form>


                </div>

            </div>

        </div>



        <!-- PISO 1 -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow border-0 rounded-4 text-center">

                <div class="card-body p-4">


                    <h3 class="fw-bold">
                        1º Piso
                    </h3>


                    <div class="display-4 fw-bold text-primary my-3">

                        <?php echo $piso1; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form method="POST">

                        <input type="hidden"
                               name="andar"
                               value="piso1">


                        <input type="number"
                               name="quantidade"
                               value="<?php echo $piso1; ?>"
                               min="0"
                               required
                               class="form-control text-center mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar

                        </button>

                    </form>


                </div>

            </div>

        </div>



        <!-- PISO 2 -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow border-0 rounded-4 text-center">

                <div class="card-body p-4">


                    <h3 class="fw-bold">
                        2º Piso
                    </h3>


                    <div class="display-4 fw-bold text-primary my-3">

                        <?php echo $piso2; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form method="POST">

                        <input type="hidden"
                               name="andar"
                               value="piso2">


                        <input type="number"
                               name="quantidade"
                               value="<?php echo $piso2; ?>"
                               min="0"
                               required
                               class="form-control text-center mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar

                        </button>

                    </form>


                </div>

            </div>

        </div>



        <!-- PISO 3 -->

        <div class="col-md-6 col-lg-3">

            <div class="card shadow border-0 rounded-4 text-center">

                <div class="card-body p-4">


                    <h3 class="fw-bold">
                        3º Piso
                    </h3>


                    <div class="display-4 fw-bold text-primary my-3">

                        <?php echo $piso3; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form method="POST">

                        <input type="hidden"
                               name="andar"
                               value="piso3">


                        <input type="number"
                               name="quantidade"
                               value="<?php echo $piso3; ?>"
                               min="0"
                               required
                               class="form-control text-center mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar

                        </button>

                    </form>
                

                </div>

            </div>

        </div>


    </div>


</div>

<div class="text-center mt-4">

<a href="../index.html"
class="btn btn-outline-secondary rounded-3">
 ← VOLTAR
</a>

</body>

</html>
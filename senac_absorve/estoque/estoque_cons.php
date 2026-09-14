<?php

include_once("../conexao.php");


// ==========================================
// RETIRAR 1 ABSORVENTE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["retirar"])) {

    $andar = $_POST["andar"] ?? "";

    $sql_retirar = null;


    if ($andar == "terreo") {

        $sql_retirar = "
            UPDATE terreo
            SET quantidade = quantidade - 1
            WHERE quantidade > 0
            LIMIT 1
        ";

    } elseif ($andar == "piso1") {

        $sql_retirar = "
            UPDATE piso1
            SET quantidade = quantidade - 1
            WHERE quantidade > 0
            LIMIT 1
        ";

    } elseif ($andar == "piso2") {

        $sql_retirar = "
            UPDATE piso2
            SET quantidade = quantidade - 1
            WHERE quantidade > 0
            LIMIT 1
        ";

    } elseif ($andar == "piso3") {

        $sql_retirar = "
            UPDATE piso3
            SET quantidade = quantidade - 1
            WHERE quantidade > 0
            LIMIT 1
        ";

    }


    if ($sql_retirar != null) {

        mysqli_query($conn, $sql_retirar);

    }


    header("Location: estoque_cons.php?retirado=1");
    exit();

}



// ==========================================
// CONSULTAR TÉRREO
// ==========================================

$sql_terreo = "
    SELECT COALESCE(SUM(quantidade), 0) AS total
    FROM terreo
";

$resultado_terreo = mysqli_query($conn, $sql_terreo);

$terreo = mysqli_fetch_assoc($resultado_terreo);



// ==========================================
// CONSULTAR PISO 1
// ==========================================

$sql_piso1 = "
    SELECT COALESCE(SUM(quantidade), 0) AS total
    FROM piso1
";

$resultado_piso1 = mysqli_query($conn, $sql_piso1);

$piso1 = mysqli_fetch_assoc($resultado_piso1);



// ==========================================
// CONSULTAR PISO 2
// ==========================================

$sql_piso2 = "
    SELECT COALESCE(SUM(quantidade), 0) AS total
    FROM piso2
";

$resultado_piso2 = mysqli_query($conn, $sql_piso2);

$piso2 = mysqli_fetch_assoc($resultado_piso2);



// ==========================================
// CONSULTAR PISO 3
// ==========================================

$sql_piso3 = "
    SELECT COALESCE(SUM(quantidade), 0) AS total
    FROM piso3
";

$resultado_piso3 = mysqli_query($conn, $sql_piso3);

$piso3 = mysqli_fetch_assoc($resultado_piso3);

?>


<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Estoque de Absorventes</title>


    <!-- BOOTSTRAP -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">


    <!-- CSS DO PROJETO -->

    <link rel="stylesheet"
          href="../css/style.css">

</head>


<body>


<div class="container py-5">


    <!-- TÍTULO -->

    <div class="text-center mb-5">

        <h1 class="fw-bold text-primary">
            Estoque de Absorventes
        </h1>

        <p class="text-muted">
            Consulte a quantidade disponível em cada andar
        </p>

    </div>



    <!-- MENSAGEM -->

    <?php if (isset($_GET["retirado"])) { ?>

        <div class="alert alert-success text-center">

            Absorvente retirado com sucesso! 💗

        </div>

    <?php } ?>



    <div class="row g-4">


        <!-- ====================================
             TÉRREO
        ===================================== -->

        <div class="col-md-6 col-lg-3">

            <div class="card estoque-card text-center h-100">

                <div class="card-body p-4">


                    <div class="andar-icon mb-3">
                        🏢
                    </div>


                    <h3 class="fw-bold">
                        Térreo
                    </h3>


                    <div class="quantidade my-3">

                        <?php echo $terreo["total"]; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form action="estoque_cons.php"
                          method="POST">

                        <input type="hidden"
                               name="andar"
                               value="terreo">


                        <button type="submit"
                                name="retirar"
                                class="btn btn-primary w-100"

                            <?php

                            if ($terreo["total"] <= 0) {
                                echo "disabled";
                            }

                            ?>

                        >

                            Retirar 1 absorvente

                        </button>

                    </form>


                    <?php if ($terreo["total"] <= 0) { ?>

                        <small class="text-danger d-block mt-2">

                            Estoque esgotado

                        </small>

                    <?php } ?>


                </div>

            </div>

        </div>



        <!-- ====================================
             PISO 1
        ===================================== -->

        <div class="col-md-6 col-lg-3">

            <div class="card estoque-card text-center h-100">

                <div class="card-body p-4">


                    <div class="andar-icon mb-3">
                        1
                    </div>


                    <h3 class="fw-bold">
                        1º Piso
                    </h3>


                    <div class="quantidade my-3">

                        <?php echo $piso1["total"]; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form action="estoque_cons.php"
                          method="POST">

                        <input type="hidden"
                               name="andar"
                               value="piso1">


                        <button type="submit"
                                name="retirar"
                                class="btn btn-primary w-100"

                            <?php

                            if ($piso1["total"] <= 0) {
                                echo "disabled";
                            }

                            ?>

                        >

                            Retirar 1 absorvente

                        </button>

                    </form>


                    <?php if ($piso1["total"] <= 0) { ?>

                        <small class="text-danger d-block mt-2">

                            Estoque esgotado

                        </small>

                    <?php } ?>


                </div>

            </div>

        </div>



        <!-- ====================================
             PISO 2
        ===================================== -->

        <div class="col-md-6 col-lg-3">

            <div class="card estoque-card text-center h-100">

                <div class="card-body p-4">


                    <div class="andar-icon mb-3">
                        2
                    </div>


                    <h3 class="fw-bold">
                        2º Piso
                    </h3>


                    <div class="quantidade my-3">

                        <?php echo $piso2["total"]; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form action="estoque_cons.php"
                          method="POST">

                        <input type="hidden"
                               name="andar"
                               value="piso2">


                        <button type="submit"
                                name="retirar"
                                class="btn btn-primary w-100"

                            <?php

                            if ($piso2["total"] <= 0) {
                                echo "disabled";
                            }

                            ?>

                        >

                            Retirar 1 absorvente

                        </button>

                    </form>


                    <?php if ($piso2["total"] <= 0) { ?>

                        <small class="text-danger d-block mt-2">

                            Estoque esgotado

                        </small>

                    <?php } ?>


                </div>

            </div>

        </div>



        <!-- ====================================
             PISO 3
        ===================================== -->

        <div class="col-md-6 col-lg-3">

            <div class="card estoque-card text-center h-100">

                <div class="card-body p-4">


                    <div class="andar-icon mb-3">
                        3
                    </div>


                    <h3 class="fw-bold">
                        3º Piso
                    </h3>


                    <div class="quantidade my-3">

                        <?php echo $piso3["total"]; ?>

                    </div>


                    <p class="text-muted">
                        absorventes disponíveis
                    </p>


                    <form action="estoque_cons.php"
                          method="POST">

                        <input type="hidden"
                               name="andar"
                               value="piso3">


                        <button type="submit"
                                name="retirar"
                                class="btn btn-primary w-100"

                            <?php

                            if ($piso3["total"] <= 0) {
                                echo "disabled";
                            }

                            ?>

                        >

                            Retirar 1 absorvente

                        </button>

                    </form>


                    <?php if ($piso3["total"] <= 0) { ?>

                        <small class="text-danger d-block mt-2">

                            Estoque esgotado

                        </small>

                    <?php } ?>


                </div>

            </div>

        </div>


    </div>



    <!-- VOLTAR -->

    <div class="text-center mt-5">

        <a href="../index1.html"
           class="btn btn-outline-secondary px-4">

            ← Voltar

        </a>

    </div>


</div>


</body>

</html>
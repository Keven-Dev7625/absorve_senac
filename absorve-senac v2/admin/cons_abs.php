<?php

session_start();

include_once("../conexao.php");

// ==========================================
// VERIFICA SE O ADMIN ESTÁ LOGADO
// ==========================================

if (!isset($_SESSION['admin'])) {

    header("Location: adm.html");
    exit();

}


// ==========================================
// ATUALIZAR QUANTIDADE
// ==========================================

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

        // REGISTRAR ADIÇÃO NO HISTÓRICO

        mysqli_query($conn, "
            INSERT INTO historico (andar, quantidade, tipo)
            VALUES ('$andar', $quantidade, 'adicao')
        ");

    }


    header("Location: cons_abs.php?atualizado=1");
    exit();

}


// ==========================================
// BUSCAR QUANTIDADES
// ==========================================


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


// ==========================================
// FILTRO DO HISTÓRICO
// ==========================================

// Se nenhum piso foi escolhido,
// mostra todos.

$filtro = $_GET["andar"] ?? "todos";


// ==========================================
// BUSCAR HISTÓRICO
// ==========================================

if ($filtro == "todos") {

    $sql = "
        SELECT *
        FROM historico
        ORDER BY data_hora DESC
    ";

} else {

    $sql = "
        SELECT *
        FROM historico
        WHERE andar = '$filtro'
        ORDER BY data_hora DESC
    ";

}

$resultado = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Consulta de Absorventes</title>


    <!-- BOOTSTRAP -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">


    <!-- CSS DO PROJETO -->

    <link rel="stylesheet"
          href="../css/style.css">


    <!-- CSS DO HISTÓRICO -->

    <style>

        /* ==========================================
           ÁREA DO HISTÓRICO
        ========================================== */

        .historico-scroll {

            max-height: 450px;

            overflow-y: auto;

            overflow-x: hidden;

            border-radius: 15px;

        }


        /* ==========================================
           CABEÇALHO FIXO
        ========================================== */

        .historico-scroll thead th {

            position: sticky;

            top: 0;

            z-index: 2;

        }


        /* ==========================================
           SCROLLBAR
        ========================================== */

        .historico-scroll::-webkit-scrollbar {

            width: 8px;

        }


        .historico-scroll::-webkit-scrollbar-track {

            background: #f8f0fa;

            border-radius: 10px;

        }


        .historico-scroll::-webkit-scrollbar-thumb {

            background: #d59ac7;

            border-radius: 10px;

        }


        .historico-scroll::-webkit-scrollbar-thumb:hover {

            background: #c47ab5;

        }


        /* ==========================================
           FILTRO
        ========================================== */

        .filtro-historico {

            max-width: 250px;

            margin: 0 auto 20px auto;

        }

    </style>

</head>


<body>


<div class="container py-5">


    <!-- ==========================================
         TÍTULO
    ========================================== -->

    <div class="text-center mb-5">

        <h1 class="fw-bold text-primary">

            Controle de Absorventes

        </h1>

        <p class="text-muted">

            Consulte e atualize o estoque

        </p>

    </div>


    <!-- ==========================================
         CARDS DOS ANDARES
    ========================================== -->

    <div class="row g-4">


        <!-- TÉRREO -->

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
                               min="0"
                               value="<?php echo $terreo; ?>"
                               class="form-control mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar estoque

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- PISO 1 -->

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
                               min="0"
                               value="<?php echo $piso1; ?>"
                               class="form-control mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar estoque

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- PISO 2 -->

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
                               min="0"
                               value="<?php echo $piso2; ?>"
                               class="form-control mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar estoque

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- PISO 3 -->

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
                               min="0"
                               value="<?php echo $piso3; ?>"
                               class="form-control mb-3">


                        <button type="submit"
                                class="btn btn-primary w-100">

                            Atualizar estoque

                        </button>

                    </form>

                </div>

            </div>

        </div>


    </div>


    <!-- ==========================================
         HISTÓRICO
    ========================================== -->

    <div class="mt-5">


        <div class="card shadow border-0 rounded-4 historico-card">


            <div class="card-body p-4">


                <h2 class="text-center fw-bold mb-4">

                    Histórico de Atualizações

                </h2>


                <!-- ==========================================
                     FILTRO POR PISO
                ========================================== -->

                <div class="filtro-historico">

                    <form method="GET">

                        <select name="andar"
                                class="form-select"
                                onchange="this.form.submit()">

                            <option value="todos"
                                <?php
                                if ($filtro == "todos") {
                                    echo "selected";
                                }
                                ?>>
                                Todos os pisos
                            </option>


                            <option value="terreo"
                                <?php
                                if ($filtro == "terreo") {
                                    echo "selected";
                                }
                                ?>>
                                Térreo
                            </option>


                            <option value="piso1"
                                <?php
                                if ($filtro == "piso1") {
                                    echo "selected";
                                }
                                ?>>
                                1º Piso
                            </option>


                            <option value="piso2"
                                <?php
                                if ($filtro == "piso2") {
                                    echo "selected";
                                }
                                ?>>
                                2º Piso
                            </option>


                            <option value="piso3"
                                <?php
                                if ($filtro == "piso3") {
                                    echo "selected";
                                }
                                ?>>
                                3º Piso
                            </option>

                        </select>

                    </form>

                </div>


                <!-- ==========================================
                     HISTÓRICO COM SCROLL
                ========================================== -->

                <div class="historico-scroll">


                    <div class="table-responsive">


                        <table class="table table-bordered table-striped text-center">


                            <thead class="table-primary">

                                <tr>

                                    <th>
                                        Data
                                    </th>

                                    <th>
                                        Horário
                                    </th>

                                    <th>
                                        Andar
                                    </th>

                                    <th>
                                        Quantidade
                                    </th>

                                    <th>
                                        Tipo
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while ($historico = mysqli_fetch_assoc($resultado)) { ?>


                                    <tr>

                                        <td>

                                            <?php

                                            echo date(
                                                "d/m/Y",
                                                strtotime($historico["data_hora"])
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            echo date(
                                                "H:i",
                                                strtotime($historico["data_hora"])
                                            );

                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            if ($historico["andar"] == "terreo") {

                                                echo "Térreo";

                                            }

                                            elseif ($historico["andar"] == "piso1") {

                                                echo "1º Piso";

                                            }

                                            elseif ($historico["andar"] == "piso2") {

                                                echo "2º Piso";

                                            }

                                            elseif ($historico["andar"] == "piso3") {

                                                echo "3º Piso";

                                            }

                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            echo $historico["quantidade"];

                                            ?>

                                        </td>


                                        <td>

                                            <?php

                                            if ($historico["tipo"] == "retirada") {

                                                echo "Retirado";

                                            }

                                            elseif ($historico["tipo"] == "adicao") {

                                                echo "Adicionado";

                                            }

                                            ?>

                                        </td>


                                    </tr>


                                <?php } ?>


                            </tbody>


                        </table>


                    </div>


                </div>


            </div>

        </div>

    </div>


    <!-- ==========================================
         VOLTAR
    ========================================== -->

    <div class="text-center mt-4">

        <a href="adm.html"
           class="btn btn-outline-secondary px-4">

            ← Voltar

        </a>

    </div>


</div>


</body>

</html>
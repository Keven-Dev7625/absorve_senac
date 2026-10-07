<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();
include_once("../conexao.php");

// ==========================================
// EXCLUIR OBSERVAÇÃO
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["excluir_observacao"])) {
    $id_observacoes = intval($_POST["id_observacoes"]);

    mysqli_query($conn, "
        DELETE FROM observacoes
        WHERE id_observacoes = $id_observacoes
    ");

    header("Location: cons_abs.php");
    exit();
}

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
    } elseif ($andar == "piso1") {
        $sql = "INSERT INTO piso1 (id_piso1, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";
    } elseif ($andar == "piso2") {
        $sql = "INSERT INTO piso2 (id_piso2, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";
    } elseif ($andar == "piso3") {
        $sql = "INSERT INTO piso3 (id_piso3, quantidade)
                VALUES (1, $quantidade)
                ON DUPLICATE KEY UPDATE quantidade = $quantidade";
    }

    if (isset($sql)) {
        mysqli_query($conn, $sql);

        $data_hora = date("Y-m-d H:i:s");

        mysqli_query($conn, "
            INSERT INTO historico (andar, quantidade, tipo, data_hora)
            VALUES ('$andar', $quantidade, 'adicao', '$data_hora')
        ");
    }

    header("Location: cons_abs.php?atualizado=1");
    exit();
}

// ==========================================
// BUSCAR QUANTIDADES
// ==========================================
$sql = "SELECT quantidade FROM terreo WHERE id_terreo = 1";
$resultado = mysqli_query($conn, $sql);
$terreo = mysqli_num_rows($resultado) > 0 ? mysqli_fetch_assoc($resultado)["quantidade"] : 0;

$sql = "SELECT quantidade FROM piso1 WHERE id_piso1 = 1";
$resultado = mysqli_query($conn, $sql);
$piso1 = mysqli_num_rows($resultado) > 0 ? mysqli_fetch_assoc($resultado)["quantidade"] : 0;

$sql = "SELECT quantidade FROM piso2 WHERE id_piso2 = 1";
$resultado = mysqli_query($conn, $sql);
$piso2 = mysqli_num_rows($resultado) > 0 ? mysqli_fetch_assoc($resultado)["quantidade"] : 0;

$sql = "SELECT quantidade FROM piso3 WHERE id_piso3 = 1";
$resultado = mysqli_query($conn, $sql);
$piso3 = mysqli_num_rows($resultado) > 0 ? mysqli_fetch_assoc($resultado)["quantidade"] : 0;

// ==========================================
// FILTRO DO HISTÓRICO
// ==========================================
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
    $filtro_seguro = mysqli_real_escape_string($conn, $filtro);

    $sql = "
        SELECT *
        FROM historico
        WHERE andar = '$filtro_seguro'
        ORDER BY data_hora DESC
    ";
}

$resultado = mysqli_query($conn, $sql);

// ==========================================
// DADOS DO HISTÓRICO PARA O PDF
// ==========================================
$historico_pdf = [];

if ($filtro == "todos") {
    $sql_pdf = "
        SELECT *
        FROM historico
        ORDER BY data_hora DESC
    ";
} else {
    $sql_pdf = "
        SELECT *
        FROM historico
        WHERE andar = '$filtro_seguro'
        ORDER BY data_hora DESC
    ";
}

$resultado_pdf = mysqli_query($conn, $sql_pdf);

while ($item = mysqli_fetch_assoc($resultado_pdf)) {
    if ($item["andar"] == "terreo") {
        $nome_andar = "Térreo";
    } elseif ($item["andar"] == "piso1") {
        $nome_andar = "1º Piso";
    } elseif ($item["andar"] == "piso2") {
        $nome_andar = "2º Piso";
    } else {
        $nome_andar = "3º Piso";
    }

    $historico_pdf[] = [
        "data" => date("d/m/Y", strtotime($item["data_hora"])),
        "hora" => date("H:i", strtotime($item["data_hora"])),
        "andar" => $nome_andar,
        "quantidade" => $item["quantidade"],
        "tipo" => $item["tipo"] == "retirada" ? "Retirado" : "Adicionado"
    ];
}

// ==========================================
// BUSCAR OBSERVAÇÕES
// ==========================================
$sql_observacoes = "
    SELECT *
    FROM observacoes
    ORDER BY data_envio DESC
";
$resultado_observacoes = mysqli_query($conn, $sql_observacoes);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Absorventes</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin="">
    <link rel="stylesheet" href="../css/style.css">

    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        .historico-scroll {
            max-height: 450px;
            overflow-y: auto;
            overflow-x: hidden;
            border-radius: 15px;
        }

        .historico-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

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

        .filtro-historico {
            max-width: 250px;
            margin: 0 auto 20px auto;
        }

        .farmacias-conteudo {
            display: none;
        }

        .mapa-farmacias {
            height: 320px;
            border-radius: 12px;
            overflow: hidden;
        }

        .farmacias-lista {
            max-height: 320px;
            overflow-y: auto;
        }

        .farmacia-item {
            border: 1px solid #ead8e8;
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 8px;
            background: #fff;
        }

        .farmacia-item:last-child {
            margin-bottom: 0;
        }

        .farmacia-distancia {
            font-size: 0.8rem;
            color: #8d5a83;
            font-weight: 600;
        }

        .farmacia-status {
            color: #6c757d;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary">Controle de Absorventes</h1>
        <p class="text-muted">Consulte e atualize o estoque</p>
    </div>

    <!-- CARDS DOS ANDARES -->
    <div class="row g-4">
        <!-- TÉRREO -->
        <div class="col-md-6 col-lg-3">
            <div class="card estoque-card text-center h-100">
                <div class="card-body p-4">
                    <div class="andar-icon mb-3">🏢</div>
                    <h3 class="fw-bold">Térreo</h3>
                    <div class="quantidade my-3">
                        <?php echo $terreo; ?>
                    </div>
                    <p class="text-muted">absorventes disponíveis</p>

                    <form method="POST">
                        <input type="hidden" name="andar" value="terreo">
                        <input type="number" name="quantidade" min="0"
                            value="<?php echo $terreo; ?>" class="form-control mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            Atualizar estoque
                        </button>
                    </form>

                    <br>

                    <?php
                    if ($terreo <= 5) {
                        echo '<p id="aviso">ESTOQUE CRÍTICO</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- PISO 1 -->
        <div class="col-md-6 col-lg-3">
            <div class="card estoque-card text-center h-100">
                <div class="card-body p-4">
                    <div class="andar-icon mb-3">1</div>
                    <h3 class="fw-bold">1º Piso</h3>
                    <div class="quantidade my-3">
                        <?php echo $piso1; ?>
                    </div>
                    <p class="text-muted">absorventes disponíveis</p>

                    <form method="POST">
                        <input type="hidden" name="andar" value="piso1">
                        <input type="number" name="quantidade" min="0"
                            value="<?php echo $piso1; ?>" class="form-control mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            Atualizar estoque
                        </button>
                    </form>

                    <br>

                    <?php
                    if ($piso1 <= 5) {
                        echo '<p id="aviso">ESTOQUE CRÍTICO</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- PISO 2 -->
        <div class="col-md-6 col-lg-3">
            <div class="card estoque-card text-center h-100">
                <div class="card-body p-4">
                    <div class="andar-icon mb-3">2</div>
                    <h3 class="fw-bold">2º Piso</h3>
                    <div class="quantidade my-3">
                        <?php echo $piso2; ?>
                    </div>
                    <p class="text-muted">absorventes disponíveis</p>

                    <form method="POST">
                        <input type="hidden" name="andar" value="piso2">
                        <input type="number" name="quantidade" min="0"
                            value="<?php echo $piso2; ?>" class="form-control mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            Atualizar estoque
                        </button>
                    </form>

                    <br>

                    <?php
                    if ($piso2 <= 5) {
                        echo '<p id="aviso">ESTOQUE CRÍTICO</p>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- PISO 3 -->
        <div class="col-md-6 col-lg-3">
            <div class="card estoque-card text-center h-100">
                <div class="card-body p-4">
                    <div class="andar-icon mb-3">3</div>
                    <h3 class="fw-bold">3º Piso</h3>
                    <div class="quantidade my-3">
                        <?php echo $piso3; ?>
                    </div>
                    <p class="text-muted">absorventes disponíveis</p>

                    <form method="POST">
                        <input type="hidden" name="andar" value="piso3">
                        <input type="number" name="quantidade" min="0"
                            value="<?php echo $piso3; ?>" class="form-control mb-3">

                        <button type="submit" class="btn btn-primary w-100">
                            Atualizar estoque
                        </button>
                    </form>

                    <br>

                    <?php
                    if ($piso3 <= 5) {
                        echo '<p id="aviso">ESTOQUE CRÍTICO</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- OBSERVAÇÕES -->
    <div class="mt-5">
        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4">
                <h2 class="text-center fw-bold mb-4">Observações</h2>

                <div style="max-height: 300px; overflow-y: auto;">
                    <?php if (mysqli_num_rows($resultado_observacoes) > 0) { ?>
                        <?php while ($observacao = mysqli_fetch_assoc($resultado_observacoes)) { ?>
                            <div class="border rounded-3 p-3 mb-3">
                                <small class="text-muted">
                                    <?php echo date("d/m/Y H:i", strtotime($observacao["data_envio"])); ?>
                                </small>

                                <p class="mb-0 mt-2">
                                    <?php echo htmlspecialchars($observacao["mensagem"]); ?>
                                </p>

                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="id_observacoes"
                                        value="<?php echo $observacao["id_observacoes"]; ?>">

                                    <div class="text-end">
                                        <button type="submit"
                                            name="excluir_observacao"
                                            class="btn btn-sm"
                                            style="background-color: #DDA0DD; color: white; border: none;"
                                            onclick="return confirm('Tem certeza que deseja excluir esta observação?');">
                                            Excluir
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p class="text-muted text-center mb-0">
                            Nenhuma observação recebida ainda.
                        </p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- HISTÓRICO -->
    <div class="mt-5">
        <div class="card shadow border-0 rounded-4 historico-card">
            <div class="card-body p-4">
                <h2 class="text-center fw-bold mb-4">
                    Histórico de Atualizações
                </h2>

                <div class="filtro-historico">
                    <form method="GET">
                        <select name="andar" class="form-select" onchange="this.form.submit()">
                            <option value="todos" <?php if ($filtro == "todos") echo "selected"; ?>>
                                Todos os pisos
                            </option>

                            <option value="terreo" <?php if ($filtro == "terreo") echo "selected"; ?>>
                                Térreo
                            </option>

                            <option value="piso1" <?php if ($filtro == "piso1") echo "selected"; ?>>
                                1º Piso
                            </option>

                            <option value="piso2" <?php if ($filtro == "piso2") echo "selected"; ?>>
                                2º Piso
                            </option>

                            <option value="piso3" <?php if ($filtro == "piso3") echo "selected"; ?>>
                                3º Piso
                            </option>
                        </select>
                    </form>
                </div>

                <div class="text-center mb-3">
                    <button type="button" class="btn btn-primary" onclick="gerarPDF()">
                        📄 Gerar PDF
                    </button>
                </div>

                <div class="historico-scroll">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th>Data</th>
                                    <th>Horário</th>
                                    <th>Andar</th>
                                    <th>Quantidade</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($historico = mysqli_fetch_assoc($resultado)) { ?>
                                    <tr>
                                        <td>
                                            <?php echo date("d/m/Y", strtotime($historico["data_hora"])); ?>
                                        </td>

                                        <td>
                                            <?php echo date("H:i", strtotime($historico["data_hora"])); ?>
                                        </td>

                                        <td>
                                            <?php
                                            if ($historico["andar"] == "terreo") {
                                                echo "Térreo";
                                            } elseif ($historico["andar"] == "piso1") {
                                                echo "1º Piso";
                                            } elseif ($historico["andar"] == "piso2") {
                                                echo "2º Piso";
                                            } elseif ($historico["andar"] == "piso3") {
                                                echo "3º Piso";
                                            }
                                            ?>
                                        </td>

                                        <td><?php echo $historico["quantidade"]; ?></td>

                                        <td>
                                            <?php
                                            if ($historico["tipo"] == "retirada") {
                                                echo "Retirado";
                                            } elseif ($historico["tipo"] == "adicao") {
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

    <!-- FARMÁCIAS PRÓXIMAS -->
    <div class="mt-4">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-3">
                <h2 class="text-center fw-semibold fs-5 mb-1">
                    💊 Farmácias próximas
                </h2>

                <p class="text-center text-muted small mb-3">
                    Use sua localização para encontrar farmácias por perto.
                </p>

                <div class="text-center mb-2">
                    <button type="button"
                        id="btnLocalizacao"
                        class="btn btn-primary btn-sm px-3">
                        📍 Encontrar farmácias
                    </button>
                </div>

                <div id="mensagemMapa" class="text-center text-muted small"></div>

                <div id="conteudoFarmacias" class="farmacias-conteudo mt-3">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div id="mapaFarmacias" class="mapa-farmacias"></div>
                        </div>

                        <div class="col-lg-4">
                            <div class="farmacias-lista" id="listaFarmacias"></div>
                        </div>
                    </div>

                    <p class="text-muted text-center mt-2 mb-0"
                        style="font-size: 0.75rem;">
                        Mapa: OpenStreetMap.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- VOLTAR -->
    <div class="text-center mt-4">
        <a href="adm.html" class="btn btn-outline-secondary px-4">
            ← Voltar
        </a>
    </div>
</div>

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin="">
</script>

<script>
    // ==========================================
    // GERAR PDF DO HISTÓRICO
    // ==========================================
    const historicoPDF = <?php echo json_encode($historico_pdf, JSON_UNESCAPED_UNICODE); ?>;

    function gerarPDF() {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();

        pdf.setFont("helvetica", "bold");
        pdf.setFontSize(18);
        pdf.text("Senac Absorve", 105, 20, {
            align: "center"
        });

        pdf.setFontSize(14);
        pdf.text("Histórico de Atualizações", 105, 29, {
            align: "center"
        });

        let filtroPDF = "<?php echo $filtro; ?>";

        let nomeFiltro = "Todos os pisos";

        if (filtroPDF === "terreo") {
            nomeFiltro = "Térreo";
        } else if (filtroPDF === "piso1") {
            nomeFiltro = "1º Piso";
        } else if (filtroPDF === "piso2") {
            nomeFiltro = "2º Piso";
        } else if (filtroPDF === "piso3") {
            nomeFiltro = "3º Piso";
        }

        pdf.setFont("helvetica", "normal");
        pdf.setFontSize(10);
        pdf.text("Filtro: " + nomeFiltro, 15, 40);

        let y = 50;

        function cabecalho() {
            pdf.setFillColor(230, 230, 250);
            pdf.rect(10, y - 6, 190, 8, "F");

            pdf.setFont("helvetica", "bold");
            pdf.setFontSize(10);

            pdf.text("Data", 15, y);
            pdf.text("Horário", 45, y);
            pdf.text("Andar", 70, y);
            pdf.text("Quantidade", 120, y);
            pdf.text("Tipo", 160, y);

            y += 9;
            pdf.setFont("helvetica", "normal");
        }

        cabecalho();

        if (historicoPDF.length === 0) {
            pdf.text("Nenhum registro encontrado.", 15, y);
        } else {
            historicoPDF.forEach((item) => {
                if (y > 280) {
                    pdf.addPage();
                    y = 20;
                    cabecalho();
                }

                pdf.text(String(item.data), 15, y);
                pdf.text(String(item.hora), 45, y);
                pdf.text(String(item.andar), 70, y);
                pdf.text(String(item.quantidade), 120, y);
                pdf.text(String(item.tipo), 160, y);

                y += 7;
            });
        }

        pdf.setFontSize(8);
        pdf.text(
            "Gerado pelo sistema Senac Absorve",
            105,
            290,
            { align: "center" }
        );

        pdf.save("historico_senac_absorve.pdf");
    }

    // ==========================================
    // MAPA DE FARMÁCIAS
    // ==========================================
    let mapa = null;
    let marcadorUsuario = null;
    let circuloPrecisao = null;
    let camadaFarmacias = null;

    const btnLocalizacao = document.getElementById("btnLocalizacao");
    const mensagemMapa = document.getElementById("mensagemMapa");
    const listaFarmacias = document.getElementById("listaFarmacias");
    const conteudoFarmacias = document.getElementById("conteudoFarmacias");

    function escaparHtml(texto) {
        const div = document.createElement("div");
        div.textContent = texto ?? "";
        return div.innerHTML;
    }

    function calcularDistancia(lat1, lon1, lat2, lon2) {
        const raioTerra = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;

        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) *
            Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);

        const c = 2 * Math.atan2(
            Math.sqrt(a),
            Math.sqrt(1 - a)
        );

        return raioTerra * c;
    }

    function formatarDistancia(distancia) {
        if (distancia < 1) {
            return Math.round(distancia * 1000) + " m";
        }

        return distancia.toFixed(1).replace(".", ",") + " km";
    }

    function iniciarMapa(lat, lon) {
        if (!mapa) {
            mapa = L.map("mapaFarmacias").setView([lat, lon], 15);

            L.tileLayer(
                "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
                {
                    maxZoom: 19,
                    attribution:
                        '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
                }
            ).addTo(mapa);
        } else {
            mapa.setView([lat, lon], 15);
        }

        if (marcadorUsuario) {
            mapa.removeLayer(marcadorUsuario);
        }

        if (circuloPrecisao) {
            mapa.removeLayer(circuloPrecisao);
        }

        marcadorUsuario = L.marker([lat, lon])
            .addTo(mapa)
            .bindPopup("📍 Você está aqui")
            .openPopup();

        circuloPrecisao = L.circle([lat, lon], {
            radius: 80,
            color: "#8d5a83",
            fillColor: "#d59ac7",
            fillOpacity: 0.18
        }).addTo(mapa);

        setTimeout(() => mapa.invalidateSize(), 150);
    }

    function obterCoordenadasFarmacia(elemento) {
        if (elemento.lat !== undefined && elemento.lon !== undefined) {
            return {
                lat: Number(elemento.lat),
                lon: Number(elemento.lon)
            };
        }

        if (elemento.center) {
            return {
                lat: Number(elemento.center.lat),
                lon: Number(elemento.center.lon)
            };
        }

        return null;
    }

    async function buscarFarmacias(lat, lon) {
        const query =
            `[out:json][timeout:25];` +
            `(nwr(around:3000,${lat},${lon})["amenity"="pharmacy"];` +
            `nwr(around:3000,${lat},${lon})["healthcare"="pharmacy"];);` +
            `out center tags;`;

        const endpoints = [
            "https://overpass-api.de/api/interpreter?data=",
            "https://overpass.kumi.systems/api/interpreter?data="
        ];

        let resposta = null;

        for (const endpoint of endpoints) {
            try {
                const retorno = await fetch(
                    endpoint + encodeURIComponent(query)
                );

                if (retorno.ok) {
                    resposta = await retorno.json();
                    break;
                }
            } catch (erro) {
                console.warn("Falha ao consultar Overpass:", erro);
            }
        }

        if (!resposta) {
            throw new Error("Não foi possível consultar as farmácias agora.");
        }

        const farmaciasMap = new Map();

        for (const elemento of resposta.elements || []) {
            const coordenadas = obterCoordenadasFarmacia(elemento);

            if (!coordenadas) {
                continue;
            }

            const chave = elemento.type + "-" + elemento.id;

            if (!farmaciasMap.has(chave)) {
                const tags = elemento.tags || {};

                farmaciasMap.set(chave, {
                    nome: tags.name || "Farmácia sem nome",
                    lat: coordenadas.lat,
                    lon: coordenadas.lon,
                    distancia: calcularDistancia(
                        lat,
                        lon,
                        coordenadas.lat,
                        coordenadas.lon
                    ),
                    rua: tags["addr:street"] || "",
                    numero: tags["addr:housenumber"] || "",
                    bairro: tags["addr:suburb"] || "",
                    horario: tags.opening_hours || ""
                });
            }
        }

        return Array.from(farmaciasMap.values())
            .sort((a, b) => a.distancia - b.distancia)
            .slice(0, 10);
    }

    function mostrarFarmacias(farmacias) {
        if (camadaFarmacias) {
            mapa.removeLayer(camadaFarmacias);
        }

        camadaFarmacias = L.layerGroup().addTo(mapa);

        if (farmacias.length === 0) {
            listaFarmacias.innerHTML = `
                <p class="text-center text-muted mt-4">
                    Nenhuma farmácia cadastrada no OpenStreetMap
                    foi encontrada em um raio de 3 km.
                </p>
            `;
            return;
        }

        listaFarmacias.innerHTML = "";

        farmacias.forEach((farmacia) => {
            const marcador = L.marker([
                farmacia.lat,
                farmacia.lon
            ])
                .addTo(camadaFarmacias)
                .bindPopup(`
                    <strong>${escaparHtml(farmacia.nome)}</strong><br>
                    ${formatarDistancia(farmacia.distancia)}
                `);

            const endereco = [
                farmacia.rua,
                farmacia.numero,
                farmacia.bairro
            ]
                .filter(Boolean)
                .join(", ");

            const mapaUrl =
                "https://www.google.com/maps/dir/?api=1&destination=" +
                farmacia.lat + "," + farmacia.lon;

            const item = document.createElement("div");
            item.className = "farmacia-item";

            item.innerHTML = `
                <div class="d-flex justify-content-between gap-2">
                    <strong>💊 ${escaparHtml(farmacia.nome)}</strong>
                    <span class="farmacia-distancia">
                        ${formatarDistancia(farmacia.distancia)}
                    </span>
                </div>

                ${
                    endereco
                        ? `<div class="farmacia-status mt-2">
                            ${escaparHtml(endereco)}
                           </div>`
                        : ""
                }

                ${
                    farmacia.horario
                        ? `<div class="farmacia-status mt-1">
                            🕒 ${escaparHtml(farmacia.horario)}
                           </div>`
                        : ""
                }

                <div class="mt-3">
                    <button type="button"
                        class="btn btn-sm btn-outline-primary me-2 btn-ver-mapa">
                        Ver no mapa
                    </button>

                    <a href="${mapaUrl}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-sm btn-primary">
                        Como chegar
                    </a>
                </div>
            `;

            item.querySelector(".btn-ver-mapa")
                .addEventListener("click", () => {
                    mapa.setView(
                        [farmacia.lat, farmacia.lon],
                        17
                    );
                    marcador.openPopup();
                });

            listaFarmacias.appendChild(item);
        });
    }

    async function localizarFarmacias() {
        if (!navigator.geolocation) {
            mensagemMapa.textContent =
                "Este navegador não oferece suporte à localização.";
            return;
        }

        btnLocalizacao.disabled = true;
        btnLocalizacao.textContent = "🔄 Localizando...";
        mensagemMapa.textContent = "Solicitando sua localização...";
        listaFarmacias.innerHTML = "";

        navigator.geolocation.getCurrentPosition(
            async (posicao) => {
                const lat = posicao.coords.latitude;
                const lon = posicao.coords.longitude;

                try {
                    conteudoFarmacias.style.display = "block";
                    iniciarMapa(lat, lon);

                    setTimeout(() => mapa.invalidateSize(), 100);

                    mensagemMapa.textContent =
                        "Localização encontrada. Buscando farmácias próximas...";

                    const farmacias = await buscarFarmacias(lat, lon);

                    mostrarFarmacias(farmacias);

                    mensagemMapa.textContent =
                        farmacias.length > 0
                            ? `${farmacias.length} farmácia(s) encontrada(s) nos arredores.`
                            : "Nenhuma farmácia encontrada nos arredores.";
                } catch (erro) {
                    console.error(erro);

                    mensagemMapa.textContent =
                        "Não foi possível carregar as farmácias agora. Tente novamente.";

                    listaFarmacias.innerHTML = `
                        <p class="text-center text-muted mt-4">
                            Verifique sua conexão com a internet
                            e tente novamente.
                        </p>
                    `;
                } finally {
                    btnLocalizacao.disabled = false;
                    btnLocalizacao.textContent = "📍 Encontrar farmácias";
                }
            },
            (erro) => {
                btnLocalizacao.disabled = false;
                btnLocalizacao.textContent =
                    "📍 Encontrar farmácias próximas";

                if (erro.code === 1) {
                    mensagemMapa.textContent =
                        "Permissão de localização negada. " +
                        "Libere a localização do navegador e tente novamente.";
                } else {
                    mensagemMapa.textContent =
                        "Não foi possível obter sua localização. " +
                        "Tente novamente.";
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    }

    btnLocalizacao.addEventListener(
        "click",
        localizarFarmacias
    );
</script>
</body>
</html>

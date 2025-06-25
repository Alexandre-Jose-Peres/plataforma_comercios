<?php
include('../backend/database/conexao.php'); // Arquivo de conexão com o banco de dados
session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Paginação
$limite = 50;
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$inicio = ($pagina - 1) * $limite;
$totalProdutos = 0;

if ($q !== '') {
    // Verifica se a pesquisa corresponde a um comércio
    $sqlComercio = "SELECT id, nome, telefone FROM comercios WHERE nome LIKE ?";
    $stmtComercio = $conn->prepare($sqlComercio);
    $searchParam = "%$q%";
    $stmtComercio->bind_param("s", $searchParam);
    $stmtComercio->execute();
    $resultComercio = $stmtComercio->get_result();

    if ($resultComercio->num_rows > 0) {
        // É um comércio
        $comercio = $resultComercio->fetch_assoc();
        $comercio_id = $comercio['id'];

        // Contar total de produtos desse comércio
        $sqlCount = "SELECT COUNT(*) as total FROM produtos WHERE comercio_id = ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("i", $comercio_id);
        $stmtCount->execute();
        $totalProdutos = $stmtCount->get_result()->fetch_assoc()['total'];

        // Pegar produtos com dados do comércio incluídos
        $sqlProdutos = "SELECT p.*, c.nome as comercio_nome, c.telefone as comercio_telefone 
                        FROM produtos p
                        INNER JOIN comercios c ON p.comercio_id = c.id
                        WHERE p.comercio_id = ? 
                        ORDER BY p.id DESC LIMIT ?, ?";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("iii", $comercio_id, $inicio, $limite);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    } else {
        // Buscar produtos pelo nome
        $sqlCount = "SELECT COUNT(*) as total 
                     FROM produtos p 
                     INNER JOIN comercios c ON p.comercio_id = c.id 
                     WHERE p.nome LIKE ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bind_param("s", $searchParam);
        $stmtCount->execute();
        $totalProdutos = $stmtCount->get_result()->fetch_assoc()['total'];

        $sqlProdutos = "SELECT p.*, c.nome as comercio_nome, c.telefone as comercio_telefone 
                        FROM produtos p 
                        INNER JOIN comercios c ON p.comercio_id = c.id 
                        WHERE p.nome LIKE ? 
                        ORDER BY p.id DESC LIMIT ?, ?";
        $stmtProdutos = $conn->prepare($sqlProdutos);
        $stmtProdutos->bind_param("sii", $searchParam, $inicio, $limite);
        $stmtProdutos->execute();
        $resultProdutos = $stmtProdutos->get_result();
    }
} else {
    // Sem pesquisa: exibir todos os produtos
    $sqlCount = "SELECT COUNT(*) as total FROM produtos";
    $stmtCount = $conn->prepare($sqlCount);
    $stmtCount->execute();
    $totalProdutos = $stmtCount->get_result()->fetch_assoc()['total'];

    $sqlProdutos = "SELECT p.*, c.nome as comercio_nome, c.telefone as comercio_telefone 
                    FROM produtos p 
                    INNER JOIN comercios c ON p.comercio_id = c.id 
                    ORDER BY p.id DESC LIMIT ?, ?";
    $stmtProdutos = $conn->prepare($sqlProdutos);
    $stmtProdutos->bind_param("ii", $inicio, $limite);
    $stmtProdutos->execute();
    $resultProdutos = $stmtProdutos->get_result();
}

// Total de páginas para paginação
$totalPaginas = ceil($totalProdutos / $limite);
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../uploads/logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">


    <title>ShopSpace</title>
</head>


<body>
    <!-- Cabeçalho -->
    <header>
        <div class="header-nav">
            <a href="../index.php">
                <div class="logo">
                    <img src="../uploads/logo1.png" alt="Logo">
                </div>
            </a>



            <div class="nome-perfil">
                <?php if ($usuario_logado): ?>
                    <p>Bem Vindo, <?= htmlspecialchars($usuario_nome) ?>! </p>
                    <button class="btn" id="btnLogout">Sair</button>

                <?php else: ?>
                    <div class="acesso">
                        <a class="btn-cadastro" href="../backend/auth/registrar.php">Cadastrar</a>
                        <a class="btn-login" href="../backend/auth/login.php">Login</a>

                    </div>
                <?php endif; ?>
            </div>


        </div>
        <form action="pages/produtos.php" method="GET" id="searchForm">
            <input type="text" name="q" id="searchInput" placeholder="Buscar produtos ou comércios...">
            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="50" height="50">
                    <path d="M11 2C5.48 2 1 6.48 1 12s4.48 10 10 10 10-4.48 10-10S16.52 2 11 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.24-7.24l3.7 3.7-1.42 1.42-3.7-3.7c-1.12.9-2.54 1.44-4.02 1.44-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6c0 1.48-.55 2.9-1.46 4.02z" />
                </svg>
            </button>
        </form>

        <script>
            document.getElementById("searchForm").addEventListener("submit", function(event) {
                let searchInput = document.getElementById("searchInput").value.trim().toLowerCase();
                let form = document.getElementById("searchForm");

                // Lista de palavras-chave para diferenciar produtos e comércios
                let comerciosKeywords = ["restaurante", "bar", "lanchonete", "padaria", "mercado", "loja"];

                // Verifica se a pesquisa contém palavras relacionadas a comércios
                if (comerciosKeywords.some(keyword => searchInput.includes(keyword))) {
                    form.action = "comercios.php"; // Direciona para comércios.php
                } else {
                    form.action = "produtos.php"; // Direciona para produtos.php
                }

                // Limpa o campo de busca após o envio
                setTimeout(() => {
                    document.getElementById("searchInput").value = "";
                }, 100);
            });
        </script>
        <button class="menu-toggle">☰</button>
        <nav class="navbar">
            <ul class="nav-links">
                <li class="link"><a href="../index.php">INÍCIO</a></li>
                <li class="link"><a href="#sobre">QUEM SOMOS</a></li>
                <li class="link"><a href="produtos.php">PRODUTOS</a></li>
                <li class="link"><a href="comercios.php">COMÉRCIOS</a></li>
            </ul>
        </nav>
        
    </header>

    <div class="main-container">

        <section class="product-list">
            <?php
            if ($resultProdutos->num_rows > 0) {
                while ($produto = $resultProdutos->fetch_assoc()) {
                    echo "<div class='product-card'>";

                    echo "<div class='product-img'>";
                    // Verifica se há fotos no banco de dados
                    if (!empty($produto['foto'])) :
                        $fotos = explode(',', $produto['foto']);
                        foreach ($fotos as $foto) :
            ?>
                            <img src="http://localhost/plataforma_comercios/<?php echo htmlspecialchars(trim($foto)); ?>" class="foto-produto" alt="Foto do Produto">
                        <?php
                        endforeach;
                    else :
                        ?>
                        <i class="fas fa-image" style="font-size: 80px; color: #F39C12;"></i>
            <?php
                    endif;
                    echo "</div>";

                    echo "<div class='product-desc'>";
                    echo "<h4>" . htmlspecialchars($produto['nome']) . "</h4>";
                    echo "<p>" . htmlspecialchars($produto['descricao']) . "</p>";
                    echo "<div class='price'>R$ " . number_format($produto['preco'], 2, ',', '.') . "</div>";
                    echo "<div class='avaliacao'>";

                    $sqlAvaliacao = "SELECT AVG(nota) as media FROM avaliacoes WHERE produto_id = ?";
                    $stmtAvaliacao = $conn->prepare($sqlAvaliacao);
                    $stmtAvaliacao->bind_param("i", $produto['id']);
                    $stmtAvaliacao->execute();
                    $resultAvaliacao = $stmtAvaliacao->get_result();
                    $media = 0;
                    if ($resultAvaliacao && $rowAvaliacao = $resultAvaliacao->fetch_assoc()) {
                        $media = round($rowAvaliacao['media'], 1);
                    }

                    // Contar total de avaliações e dividir por nota
                    $sqlCountAvaliacoes = "SELECT nota, COUNT(*) as total FROM avaliacoes WHERE produto_id = ? GROUP BY nota";
                    $stmtCountAvaliacoes = $conn->prepare($sqlCountAvaliacoes);
                    $stmtCountAvaliacoes->bind_param("i", $produto['id']);
                    $stmtCountAvaliacoes->execute();
                    $resultCount = $stmtCountAvaliacoes->get_result();

                    $totalAvaliacoes = 0;
                    $notas = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

                    while ($row = $resultCount->fetch_assoc()) {
                        $nota = $row['nota'];
                        $qtd = $row['total'];
                        $notas[$nota] = $qtd;
                        $totalAvaliacoes += $qtd;
                    }

                    // Verifica se o usuário já avaliou
                    $usuario_id = $_SESSION['user_id'] ?? 0;
                    $jaAvaliou = false;

                    if ($usuario_id) {
                        $sqlJaAvaliou = "SELECT id FROM avaliacoes WHERE produto_id = ? AND usuario_id = ?";
                        $stmtJaAvaliou = $conn->prepare($sqlJaAvaliou);
                        $stmtJaAvaliou->bind_param("ii", $produto['id'], $usuario_id);
                        $stmtJaAvaliou->execute();
                        $resultJaAvaliou = $stmtJaAvaliou->get_result();
                        $jaAvaliou = $resultJaAvaliou->num_rows > 0;
                    }




                    echo "<div class='estrelas' data-id='" . $produto['id'] . "'>";
                    for ($i = 1; $i <= 5; $i++) {
                        $cor = ($i <= floor($media)) ? 'gold' : '#ccc';

                        if ($i == floor($media) + 1 && ($media - floor($media)) >= 0.25) {
                            echo "<span class='estrela' data-value='$i' style='background: linear-gradient(to right, gold 50%, #ccc 50%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;'>&#9733;</span>";
                        } else {
                            echo "<span class='estrela' data-value='$i' style='color: $cor;'>&#9733;</span>";
                        }
                    }

                    // GRAFICO FORA do loop das estrelas
                    echo "<div class='grafico-avaliacoes'>";

                    echo "<div class='media-avaliacao'>Média: <strong>$media</strong> ★ de <strong>5</strong> </div>";
                    echo "<div class='quantidade-avaliacoes'>Total de avaliações: <strong>$totalAvaliacoes</strong></div>";

                    foreach (array_reverse($notas, true) as $nota => $qtd) {
                        $porcentagem = $totalAvaliacoes > 0 ? round(($qtd / $totalAvaliacoes) * 100) : 0;

                        echo "<div class='barra-avaliacao-horizontal'>";
                        echo "<div class='label-nota'>$nota ★</div>";
                        echo "<div class='barra-container-horizontal'>";
                        echo "<div class='barra-horizontal' style='width: {$porcentagem}%;' title='$qtd avaliação(s) – $porcentagem%'></div>";
                        echo "</div>";
                        echo "<div class='porcentagem'>$porcentagem%</div>";
                        echo "</div>";
                    }
                    echo "</div>"; // fim grafico-avaliacoes
                    echo "<div class='quantidade-avaliacoes'> <strong>$totalAvaliacoes</strong></div>";
                    echo "</div>"; // fim .estrelas





                    echo "<span class='mensagem-avaliacao' id='mensagem-" . $produto['id'] . "'></span>";

                    echo "<div class='media-avaliacao-wrapper'>";

                    echo "<div class='media-avaliacao'>Média: <strong>$media</strong> ★</div>";


                    echo "</div>"; // fim media-avaliacao-wrapper


                    foreach ($notas as $nota => $qtd) {
                        if ($qtd > 0) {
                            $porcentagem = round(($qtd / $totalAvaliacoes) * 100);
                        }
                    }

                    echo "</div>";

                    echo "<a href='descricao.php?id=" . htmlspecialchars($produto['id']) . "' class='btn-detales'>Ver Detalhes</a>";

                    if (!empty($produto['comercio_telefone'])) {
                        $numeroWhatsApp = preg_replace('/\D/', '', $produto['comercio_telefone']);

                        if (strlen($numeroWhatsApp) >= 10) {
                            $mensagem = "Olá, tudo bem? Tenho interesse neste produto " . $produto['nome'] . " no site SHOPSPACE e gostaria de saber mais detalhes. Poderia me ajudar? ";
                            $linkWhatsApp = "https://wa.me/55{$numeroWhatsApp}?text=" . urlencode($mensagem);
                            echo "<a href='" . htmlspecialchars($linkWhatsApp) . "' class='buy-button' target='_blank'>Fazer Pedido</a><br><br>";
                        } else {
                            echo "<p>Telefone do vendedor inválido.</p>";
                        }
                    } else {
                        echo "<p>WhatsApp do vendedor não disponível.</p>";
                    }

                    echo "</div>";


                    echo "</div>";
                }
            } else {
                echo "<p>Nenhum resultado encontrado.</p>";
            }

            ?>

            <script>
                function mostrarModal(mensagem, callback = null) {
                    const modal = document.getElementById("mensagemModal");
                    const texto = document.getElementById("mensagemModalTexto");
                    const fechar = modal.querySelector(".fechar");

                    texto.textContent = mensagem;
                    modal.style.display = "flex";

                    fechar.onclick = () => {
                        modal.style.display = "none";
                        if (callback) callback();
                    };

                    window.onclick = (e) => {
                        if (e.target === modal) {
                            modal.style.display = "none";
                            if (callback) callback();
                        }
                    };
                }


                const usuarioLogado = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

                document.addEventListener("DOMContentLoaded", () => {
                    document.querySelectorAll('.estrelas').forEach(estrelasDiv => {
                        const produtoId = estrelasDiv.getAttribute('data-id');
                        const estrelas = estrelasDiv.querySelectorAll('.estrela');

                        estrelas.forEach(estrela => {
                            estrela.addEventListener('mouseenter', () => {
                                const valor = estrela.getAttribute('data-value');
                                estrelas.forEach(e => {
                                    e.classList.remove('dourada');
                                    if (e.getAttribute('data-value') <= valor) {
                                        e.classList.add('dourada');
                                    }
                                });
                            });

                            estrela.addEventListener('mouseleave', () => {
                                estrelas.forEach(e => e.classList.remove('dourada'));
                            });

                            estrela.addEventListener('click', () => {
                                if (!usuarioLogado) {

                                    window.location.href = '../backend/auth/login.php';

                                    return;
                                }

                                const nota = estrela.getAttribute('data-value');

                                fetch('avaliar.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: `produto_id=${produtoId}&nota=${nota}`
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.sucesso) {
                                            mostrarModal("Avaliação registrada com sucesso!", () => {
                                                location.reload();
                                            });
                                        } else {
                                            mostrarModal(data.mensagem || "Erro ao avaliar.");
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Erro na requisição:", error);
                                        mostrarModal("Erro ao avaliar.");
                                    });
                            });
                        });
                    });
                });


                document.addEventListener("DOMContentLoaded", () => {
                    document.querySelectorAll('.btn-toggle-avaliacoes').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const id = btn.getAttribute('data-id');
                            const container = document.getElementById('avaliacoes-' + id);

                            if (container.style.display === "none" || container.style.display === "") {
                                container.style.display = "block";
                                btn.textContent = "Ocultar Avaliações";
                            } else {
                                container.style.display = "none";
                                btn.textContent = "Mostrar Avaliações";
                            }
                        });
                    });
                });
            </script>

        </section>

        <?php
        // Paginação
        $totalPaginas = ceil($totalProdutos / $limite);

        if ($totalPaginas > 1) {
            echo "<div class='pagination'>";
            for ($i = 1; $i <= $totalPaginas; $i++) {
                $classe = ($i == $pagina) ? 'active' : '';
                echo "<a class='$classe' href='?page=$i'>$i</a> ";
            }
            echo "</div>";
        }
        ?>
    </div>
    <script>
        document.getElementById("searchInput").addEventListener("input", function() {
            if (this.value.trim() === "") {
                window.location.href = "produtos.php"; // Recarrega a página para exibir todos os produtos
            }
        });

        document.getElementById("searchForm").addEventListener("submit", function(event) {
            setTimeout(() => {
                document.getElementById("searchInput").value = "";
            }, 100);
        });
    </script>


    <footer style="background-color: #071a3f; color: #ffd700; padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 1200px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between;">
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Contato</h5>
                <p>📞 Telefone: (35) 99702-9569</p>
                <div class="rede-sociais">
                    <a href="#"><img src="uploads/facebook.png">Facebook</a>
                    <a href="#"><img src="uploads/instagram.png">Instagram</a>
                    <a href="#"><img src="uploads/whatsapp.png">Whatsapp</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="../assets/js/index.js"></script>
</body>
<!-- Modal de alerta -->
<div id="mensagemModal" class="modal" style="display: none;">
    <div class="modal-conteudo">
        <span class="fechar">&times;</span>
        <p id="mensagemModalTexto"></p>
    </div>
</div>
<!-- Modal de confirmação de logout -->
<div id="logoutModal" class="modal" style="display: none;">
    <div class="modal-conteudo">
        <span class="fechar" id="fecharLogout">&times;</span>
        <p>Tem certeza que deseja sair?</p>
        <div style="margin-top: 20px;">
            <button id="confirmarLogout" style="margin-right: 10px;">Sim, sair</button>
            <button id="cancelarLogout">Cancelar</button>
        </div>
    </div>
</div>

<script>
    // Certifique-se de que todos os elementos já estão carregados
    document.addEventListener("DOMContentLoaded", function() {
        const btnLogout = document.getElementById('btnLogout');
        const logoutModal = document.getElementById('logoutModal');
        const fecharLogout = document.getElementById('fecharLogout');
        const cancelarLogout = document.getElementById('cancelarLogout');
        const confirmarLogout = document.getElementById('confirmarLogout');

        if (btnLogout && logoutModal && fecharLogout && cancelarLogout && confirmarLogout) {
            btnLogout.onclick = () => {
                logoutModal.style.display = "flex";
            };

            fecharLogout.onclick = cancelarLogout.onclick = () => {
                logoutModal.style.display = "none";
            };

            window.onclick = function(event) {
                if (event.target === logoutModal) {
                    logoutModal.style.display = "none";
                }
            };

            confirmarLogout.onclick = () => {
                window.location.href = "../backend/auth/logout.php";
            };
        } else {
            console.warn("Alguns elementos do modal de logout não foram encontrados.");
        }
    });
</script>


</html>
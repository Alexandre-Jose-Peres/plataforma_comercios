<?php

ob_start();
include('../backend/database/conexao.php');
session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;

$id = $_GET['id'] ?? null;

if (!empty($id)) {
    // Consulta para obter os dados do produto e do comércio
    $query_produto = "SELECT p.id, p.nome, p.descricao, p.preco, p.foto, 
                             c.nome AS comercio_nome, c.telefone 
                      FROM produtos p
                      JOIN comercios c ON p.comercio_id = c.id
                      WHERE p.id = ?";

    $stmt = $conn->prepare($query_produto);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $produto = $result->fetch_assoc();
    } else {
        echo "<p>Produto não encontrado.</p>";
        exit;
    }
} else {
    echo "<p>ID do produto não informado.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt_BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../uploads/logo1.png" type="image/x-icon">
    <title>ShopSpace</title>

    <!-- Estilos -->
    <link href="../vendor2/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>

    </style>

</head>

<body>
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

    <div id="container">
        <!-- Imagem do Produto -->
        <div class="image-section">
            <img src="http://localhost/plataforma_comercios/<?php echo htmlspecialchars($produto['foto']); ?>" alt="Produto">
        </div>

        <div class="details">



            <!-- Detalhes do Produto -->
            <div class="details-section">
                <h1><?php echo htmlspecialchars($produto['nome']); ?></h1>
                <p><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p>
            </div>

            <!-- Preço e Botão de Compra -->
            <div class="price-section">
                <h3>Vendido por: <strong><?php echo htmlspecialchars($produto['comercio_nome']); ?></strong></h3>

                <?php
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

                echo "</div>"; // fim .estrelas





                echo "<span class='mensagem-avaliacao' id='mensagem-" . $produto['id'] . "'></span>";

                echo "<div class='media-avaliacao-wrapper'>";

                echo "<div class='media-avaliacao'>Média: <strong>$media</strong> ★</div>";

                echo "<div class='grafico-avaliacoes'>";
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
                echo "</div>"; // fim media-avaliacao-wrapper


                foreach ($notas as $nota => $qtd) {
                    if ($qtd > 0) {
                        $porcentagem = round(($qtd / $totalAvaliacoes) * 100);
                    }
                }

                echo "</div>";
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
                            const jaAvaliou = estrelasDiv.getAttribute('data-avaliado') === "sim";
                            const estrelas = estrelasDiv.querySelectorAll('.estrela');

                            // Se já avaliou, desativa interação
                            if (jaAvaliou) {
                                estrelas.forEach(e => e.style.cursor = "default");
                                return;
                            }

                            // Hover (dourar estrelas ao passar o mouse)
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

                                // Remove destaque ao sair do mouse
                                estrela.addEventListener('mouseleave', () => {
                                    estrelas.forEach(e => e.classList.remove('dourada'));
                                });

                                // Clique na estrela para avaliar
                                estrela.addEventListener('click', () => {
                                    if (!usuarioLogado) {
                                        mostrarModal("Você precisa estar logado para avaliar!", () => {
                                            window.location.href = '../backend/auth/login.php'; // ou o caminho real da sua página de login
                                        });
                                        return;
                                    }
                                    //if (!usuarioLogado) {
                                    // window.location.href = '../backend/auth/login.php"'; // substitua por sua URL de login real, se necessário
                                    //return;
                                    //}


                                    const nota = estrela.getAttribute('data-value');

                                    fetch('avaliar.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded'
                                            },
                                            body: `produto_id=${produtoId}&nota=${nota}`
                                        })
                                        .then(response => response.json()) // Espera JSON vindo do PHP
                                        .then(data => {
                                            if (data.sucesso) {
                                                // Recarrega a página para mostrar avaliação atualizada
                                                location.reload();
                                            } else {
                                                mostrarModal(data.mensagem || "Erro ao avaliar.");
                                            }
                                        })
                                        .catch(error => {
                                            console.error("Erro na requisição:", error);
                                            mostrarModal(data.mensagem || "Erro ao avaliar.");

                                        });
                                });
                            });
                        });
                    });
                </script>

                <h2 class='price'>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></h2>



                <?php if (!empty($produto['telefone'])) : ?>
                    <?php
                    // Remove caracteres não numéricos do telefone
                    $numeroWhatsApp = preg_replace('/\D/', '', $produto['telefone']);
                    $mensagem = urlencode("Olá, tudo bem? Tenho interesse neste produto " . $produto['nome'] . " no site SHOPSPACE e gostaria de saber mais detalhes. Poderia me ajudar? ");
                    $linkWhatsApp = "https://wa.me/55{$numeroWhatsApp}?text={$mensagem}";
                    ?>
                    <a href="<?php echo $linkWhatsApp; ?>" class="buy-button" target="_blank">Comprar Agora</a>
                <?php else : ?>
                    <p>WhatsApp do vendedor não disponível.</p>
                <?php endif; ?>


            </div>
        </div>
    </div>

    <footer style="background-color: #071a3f; color: #ffd700;padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 1200px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between;">

            <!-- Informações de Contato -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Contato</h5>

                <p>📞 Telefone: (35) 99702-9569</p>
                <div class="rede-sociais">
                    <a href="#" alt=""><img src="uploads/facebook.png">Facebook</a>
                    <a href="#" alt=""><img src="uploads/instagram.png">Instagram</a>
                    <a href="" alt=""><img src="uploads/whatsapp.png">Whatsapp</a>
                    <a href="#"><a href="https://www.flaticon.com/br/icones-gratis/rede-social" title="rede social ícones"></a>
                        Youtube</a>
                </div>
            </div>

            <!-- Navegação Rápida -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Links Úteis</h5>
                <p><a href="#">Sobre Nós</a></p>
                <p><a href="#">Serviços</a></p>
                <p><a href="#">Blog</a></p>
                <p><a href="#">Contato</a></p>
            </div>

            <!-- Informações Legais -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Legal</h5>
                <p><a href="#">Política de Privacidade</a></p>
                <p><a href="#">Termos de Uso</a></p>
                <p><a href="#">Política de Cookies</a></p>
            </div>

            <!-- Newsletter -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Newsletter</h5>
                <p>Inscreva-se para receber novidades e promoções.</p>
                <form>
                    <input type="email" placeholder="Seu e-mail" required
                        style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <button type="submit"
                        style="width: 100%; padding: 10px; background-color: #ff6600; color: #fff; border: none; cursor: pointer;">Enviar</button>
                </form>
            </div>
        </div>


    </footer>
    <!-- Direitos Autorais -->
    <div style="text-align: center; padding: 15px 0; background-color: #111;color:#ffd700">
        © 2025 Empresa. Todos os direitos reservados.
    </div>

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
<?php
include('backend/database/conexao.php');
session_start();

// Verifica se o usuário está logado
$usuario_logado = isset($_SESSION['user_id']);
$usuario_nome = $usuario_logado ? $_SESSION['user_name'] : null;
// Definir as categorias disponíveis no banco de dados (array associativo)
$categorias = [
    '0' => 'Selecione uma Categoria',
    '1' => 'Roupas e Acessórios',
    '2' => 'Tecnologia',
    '3' => 'Casa e Decoração',
    '4' => 'Beleza e Cuidados Pessoais',
    '5' => 'Alimentos e Bebidas',
    '6' => 'Esportes e Lazer',
    '7' => 'Brinquedos e Jogos',
    '8' => 'Livros e Papelaria',
    '9' => 'Saúde',
    '10' => 'Automotivo',
];

// Obter a categoria via GET ou usar 'todos' como padrão
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'todos';

if ($categoria === 'todos') {
    $sql = "SELECT * FROM produtos ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM produtos WHERE categoria = ? ORDER BY id DESC";
}


// Preparar a consulta
$stmt = $conn->prepare($sql);

// Se houver uma categoria específica, vincula o parâmetro
if ($categoria !== 'todos') {
    $stmt->bind_param("i", $categoria); // 'i' porque a categoria é um inteiro
}

// Executar a consulta
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="uploads/logo1.png" type="image/x-icon">
    <title>ShopSpace</title>

    <!-- Fontes do Google Fonts (Roboto, Montserrat, Open Sans, etc.) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/carrossel.css">

</head>

<body>

    <!-- Cabeçalho -->
    <header>
        <div class="header-nav">
            <a href="index.php">
                <div class="logo">
                    <img src="<?php echo $icone_index ?>" alt="<?php echo $nome_sistema; ?>">
                </div>
            </a>



            <div class="nome-perfil">
                <?php if ($usuario_logado): ?>
                    <p>Bem Vindo, <?= htmlspecialchars($usuario_nome) ?>! </p>
                    <button class="btn" id="btnLogout">Sair</button>
                    <script>
                        document.getElementById("btnLogout").addEventListener("click", function() {
                            window.location.href = "backend/auth/logout.php";
                        });
                    </script>


                <?php else: ?>
                    <div class="acesso">
                        <a class="btn-cadastro" href="pages/registrar.php">Cadastrar</a>
                        <a class="btn-login" href="pages/login.php">Login</a>

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
                    form.action = "pages/comercios.php"; // Direciona para comércios.php
                } else {
                    form.action = "pages/produtos.php"; // Direciona para produtos.php
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
                <li class="link"><a href="#">INÍCIO</a></li>
                <li class="link"><a href="#sobre">QUEM SOMOS</a></li>
                <li class="link"><a href="pages/produtos.php">PRODUTOS</a></li>
                <li class="link"><a href="pages/comercios.php">COMÉRCIOS</a></li>
            </ul>
        </nav>

    </header>

    <section class="wrapper ">

        <section id="carrossel">
            <div class="carousel-container">
                <div class="carousel-item active">
                    <img src="" alt="Promoção 1">
                    <div class="caption">
                        1
                        <H2>1 Aumente sua visibilidade online e alcance mais clientes!</H2>
                        <a class="button-b" href="pages/registrar.php">Cadastre Agora</a>
                    </div>
                </div>
                <div class="carousel-item">
                    2
                    <img src="uploads/3.jpg" alt="Promoção 2">
                    <div class="caption">
                        <h2> 2 Mais do que vender, vamos ajudar você a conectar seu comércio com quem mais importa!</h2>
                        <a class="button-b" href="pages/registrar.php">Cadastre Agora</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="uploads/comerciante.jpg" alt="Promoção 3">
                    <div class="caption">
                        3
                        <h2>3 Mais do que vender, vamos ajudar você a conectar seu comércio com quem mais importa!</h2>
                        <a class="button-b" href="pages/registrar.php">Cadastre Agora</a>
                    </div>
                </div>
            </div>


        </section>

    </section>

    <script>
        let currentIndex = 0;
        const items = document.querySelectorAll('.carousel-item');
        const totalItems = items.length;
        const carouselContainer = document.querySelector('.carousel-container');

        // Função para mover para o próximo slide
        function moveToNextSlide() {
            currentIndex = (currentIndex + 1) % totalItems; // Incrementa e volta ao primeiro quando atinge o último
            updateCarousel();
        }

        // Função para mover para o slide anterior
        function moveToPrevSlide() {
            currentIndex = (currentIndex - 1 + totalItems) % totalItems; // Decrementa e volta ao último quando atinge o primeiro
            updateCarousel();
        }

        // Atualiza o carrossel para a posição correta
        function updateCarousel() {
            const newTransformValue = `-${currentIndex * 100}%`;
            carouselContainer.style.transform = `translateX(${newTransformValue})`;

            // Atualiza a classe 'active' no item atual
            items.forEach((item, index) => {
                if (index === currentIndex) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Função para iniciar o carrossel automático
        function startCarousel() {
            setInterval(moveToNextSlide, 5000); // Muda de slide a cada 3 segundos (3000ms)
        }

        // Iniciar o carrossel
        startCarousel();
    </script>
    <!-- Banner Principal -->
    <!--<div class="container-banner">
        <section class="banner">
            <img src="uploads/foto-comerciante/comercio4.jpg" alt="Banner">
            <div class="banner-content">
                <h2>Conecte seu comércio com mais clientes!</h2>
                <p>Divulgue seus produtos e alcance novas oportunidades de venda.</p>
                <div class="cta-buttons">
                    <a href="backend/auth/registrar.php" class="cta-btn">Seja um Comerciantes</a>
                    <a href="backend/auth/registrar.php" class="cta-btn">Descubra Produtos</a>
                </div>
            </div>
        </section>
    </div>-->

    <section class="produtos-carrossel">
        <div class="carrossel-container">
            <button class="promo-prev">&#10094;</button>
            <div class="carrossel" id="carrosselProdutos">
                <?php while ($produto = $result->fetch_assoc()) { ?>
                    <a href="pages/descricao.php? id=<?php echo htmlspecialchars($produto['id']); ?>">

                        <div class="item-produto">
                            <div class="image">
                                <img src="http://localhost/plataforma_comercios/<?php echo htmlspecialchars($produto['foto']); ?>">
                            </div>
                            <h4><?php echo htmlspecialchars($produto['nome']); ?></h4>

                        </div>
                    </a>
                <?php } ?>
            </div>
            <button class="promo-next">&#10095;</button>
        </div>
    </section>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let carrossel = document.getElementById("carrosselProdutos");
            let prev = document.querySelector(".promo-prev");
            let next = document.querySelector(".promo-next");
            let scrollAmount = 0;
            let step = 200;

            next.addEventListener("click", function() {
                carrossel.scrollTo({
                    left: (scrollAmount += step),
                    behavior: "smooth"
                });
            });

            prev.addEventListener("click", function() {
                carrossel.scrollTo({
                    left: (scrollAmount -= step),
                    behavior: "smooth"
                });
            });
        });
    </script>

    <div class="produtos-dest">
        <h3>Produtos em Destaques</h3>
    </div>
    <section class="vitrine-produtos">
        <!-- Filtro de Categorias -->
        <div class="filtro-categorias">
            <button onclick="filterProducts('todos')">Selecionar Todos</button> <!-- Botão para selecionar todos -->
            <?php
            foreach ($categorias as $key => $value) {
                echo "<button onclick=\"filterProducts('$key')\">$value</button>";
            }
            ?>
        </div>

        <div class="carrossel-produtos">
            <!-- Produtos filtrados via AJAX -->
            <div class="produtos" id="Produtos"></div>
            <!-- Paginação -->
            <div id="pagination" class="paginacao-produtos"></div>
        </div>
    </section>

    <script>
        let currentCategory = 'todos';
        let currentPage = 1;

        function filterProducts(category = 'todos', page = 1) {
            currentCategory = category;
            currentPage = page;

            fetch(`pages/filter_products.php?categoria=${category}&page=${page}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('Produtos').innerHTML = data.html;
                    generatePagination(data.total, data.limit, currentPage);
                })
                .catch(error => {
                    console.error('Erro ao carregar produtos:', error);
                    document.getElementById('Produtos').innerHTML = '<p>Erro ao carregar produtos.</p>';
                    document.getElementById('pagination').innerHTML = '';
                });
        }

        function generatePagination(total, limit, currentPage) {
            const totalPages = Math.ceil(total / limit);
            let paginationHTML = '';

            if (totalPages <= 1) {
                document.getElementById('pagination').innerHTML = '';
                return;
            }

            for (let i = 1; i <= totalPages; i++) {
                paginationHTML += `
                <button onclick="filterProducts('${currentCategory}', ${i})"
                        ${i === currentPage ? 'style="font-weight:bold;"' : ''}>
                    ${i}
                </button>
            `;
            }

            document.getElementById('pagination').innerHTML = paginationHTML;
        }

        document.addEventListener('DOMContentLoaded', () => {
            filterProducts();
        });
    </script>

    <!--<div class="propaganda">
        <a href="https://www.amazon.com.br/Metodologia-PEMD-Planejamento-Estrat%C3%A9gico-diferenciar/dp/6556950920?crid=1E3AHP5BLBW5S&dib=eyJ2IjoiMSJ9.UzWAs5ixvNIXYtLdTZ1TQ8onVc7-jOgQO0AldcgRuLnmTCCcEgaco_OJw7rgM1kqC4XID9T7_ylFbt1Non9tYu1Ye0pejTk6fWn-2GGmhh2SFu7cecWqrvSBcDiJv_oTs8j1ItQo_PImfRDTkFsyG1J_3HYpRD6WIClg-OhtajDAronieN4xaoCqVDKgejQOf3LBIbTAQ8TCIxKDx5xeHYA0sqI2VYgjpaq7yv2DhsmJZXyriOg8Duu_ujTyxMAPslCY3Yr_zfMvF6GezGaoED0XcJUSrBnOQqrFTevrdt4.KDtQWl3eL0vPFS5WloHQvplGu-W4-c7Ddl1W24nrt5g&dib_tag=se&keywords=marketing+digital&qid=1742941774&sprefix=mark%2Caps%2C315&sr=8-2-spons&ufe=app_do%3Aamzn1.fos.6d798eae-cadf-45de-946a-f477d47705b9&sp_csd=d2lkZ2V0TmFtZT1zcF9hdGY&psc=1&linkCode=ll1&tag=tecinterati0f-20&linkId=ac2d91b0938c517ae8c94b8556e83285&language=pt_BR&ref_=as_li_ss_tl" target='_blank'>
            <img src="https://m.media-amazon.com/images/I/717pVE3aYmL._SY342_.jpg" alt="">
        </a>
    </div>-->
    <!-- Benefícios para Comerciantes -->
    <section id="comerciantes" class="benefits">
        <h3>Por que anunciar seu comércio conosco?</h3>
        <div class="content">
            <!-- Imagem ao lado do texto -->

            <div class="text">
                <p>Ao cadastrar seu comércio em nossa plataforma, você conecta seu negócio com clientes que estão buscando seus produtos. Aqui estão os principais benefícios:</p>
                <ul>
                    <li><strong>Aumente a visibilidade do seu comércio:</strong> Atraia novos clientes interessados no seu produto, direto para seu contato.</li>
                    <li><strong>Promoção do seu produto e serviço:</strong> Mostre seus produtos para um público-alvo qualificado e aumente o tráfego para o seu negócio.</li>
                    <li><strong>Gestão simples de anúncios:</strong> Cadastre seus produtos de forma fácil e eficiente, com uma plataforma intuitiva.</li>
                    <li><strong>Facilidade de contato direto:</strong> Direcione os clientes diretamente para o seu meio de comunicação (telefone, WhatsApp, e-mail, etc.), sem complicação.</li>
                </ul>
            </div>

            <div class="image">
                <img src="uploads/foto-comerciante/comercio3.jpg" alt="Benefícios para Comerciantes" style="width: 100%; height: auto;">
            </div>
        </div>
    </section>

    <!-- Benefícios para Clientes -->
    <section id="clientes" class="benefits">
        <h3>Por que escolher nossa plataforma?</h3>
        <div class="content">
            <!-- Imagem ao lado do texto -->
            <div class="image">
                <img src="uploads/cliente-produto/cliente2.jpg" alt="Benefícios para Clientes" style="width: 100%; height: auto;">
            </div>
            <div class="text">
                <p>Busque os melhores produtos da sua região e entre em contato diretamente com os comerciantes. Descubra os benefícios:</p>
                <ul>
                    <li><strong>Encontre facilmente o que você procura:</strong> Navegue por uma vasta gama de produtos disponíveis perto de você.</li>
                    <li><strong>Conecte-se diretamente com o comerciante:</strong> Ao encontrar o produto, você será direcionado diretamente para o contato do comerciante, facilitando a negociação e compra.</li>
                    <li><strong>Produtos próximos e de qualidade:</strong> Localize os melhores itens na sua área com a segurança de estar comprando de vendedores confiáveis.</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Seção Sobre -->
    <section id="sobre" class="benefits">
        <h3>Quem Somos?</h3>
        <div class="content">

            <div class="text">
                <p>Somos uma plataforma que conecta comerciantes a clientes, facilitando a divulgação de produtos e a comunicação direta entre ambos.</p>
            </div>
            <div class="image">
                <img src="uploads/somos/somos.png" alt="Benefícios para Comerciantes" style="width: 100%; height: auto;">
            </div>
        </div>
    </section>

    <div class="cta-buttons" style="padding: 30px;">
        <a href="backend/auth/registrar.php" class="cta-btn">Cadastre seu Comércio</a>
        <a href="backend/auth/registrar.php" class="cta-btn">Descubra Produtos</a>
    </div>

    <div class="cookie-banner" id="cookieBanner">
        <p>Usamos cookies para melhorar sua experiência. Gerencie suas preferências.</p>
        <div class="btns">
            <button class="accept-btn" onclick="aceitarTodos()">Aceitar todos</button>
            <button class="reject-btn" onclick="rejeitarTodos()">Rejeitar todos</button>
            <button class="settings-btn" onclick="abrirPreferencias()">Configurações</button>
        </div>
    </div>

    <div class="modal" id="modalCookies">
        <div class="modal-content">
            <h2>Preferências de Cookies</h2>
            <div class="switch">
                <span>Cookies Necessários</span>
                <span><strong>Sempre Ativos</strong></span>
            </div>
            <div class="switch">
                <label>Cookies de Desempenho (ex: Google Analytics)</label>
                <input type="checkbox" id="desempenho" />
            </div>
            <div class="switch">
                <label>Cookies de Marketing (ex: Pixel do Facebook)</label>
                <input type="checkbox" id="marketing" />
            </div>
            <div class="buttons">
                <button onclick="fecharModal()">Cancelar</button>
                <button class="accept-btn" onclick="salvarPreferencias()">Salvar</button>
            </div>
        </div>
    </div>

    <button class="btn-Preferencia" onclick="abrirPreferencias()">Preferências de Cookies</button>

    <script>
        function setCookie(nome, valor, dias) {
            const d = new Date();
            d.setTime(d.getTime() + (dias * 24 * 60 * 60 * 1000));
            document.cookie = `${nome}=${JSON.stringify(valor)};expires=${d.toUTCString()};path=/`;
        }

        function getCookie(nome) {
            const match = document.cookie.match(new RegExp('(^| )' + nome + '=([^;]+)'));
            if (match) return JSON.parse(match[2]);
            return null;
        }

        function aceitarTodos() {
            const consent = {
                desempenho: true,
                marketing: true
            };
            setCookie("cookiesConsentimento", consent, 180);
            aplicarConsentimento(consent);
            document.getElementById("cookieBanner").classList.remove("ativo");
        }

        function rejeitarTodos() {
            const consent = {
                desempenho: false,
                marketing: false
            };
            setCookie("cookiesConsentimento", consent, 180);
            document.getElementById("cookieBanner").classList.remove("ativo");
        }

        function abrirPreferencias() {
            document.getElementById("modalCookies").classList.add("ativo");
            const consent = getCookie("cookiesConsentimento");
            if (consent) {
                document.getElementById("desempenho").checked = consent.desempenho;
                document.getElementById("marketing").checked = consent.marketing;
            }
        }

        function fecharModal() {
            document.getElementById("modalCookies").classList.remove("ativo");
        }

        function salvarPreferencias() {
            const desempenho = document.getElementById("desempenho").checked;
            const marketing = document.getElementById("marketing").checked;
            const consent = {
                desempenho,
                marketing
            };
            setCookie("cookiesConsentimento", consent, 180);
            aplicarConsentimento(consent);
            fecharModal();
            document.getElementById("cookieBanner").classList.remove("ativo");
        }

        function aplicarConsentimento(consent) {
            if (consent.desempenho) {
                carregarAnalytics();
            }
            if (consent.marketing) {
                carregarFacebookPixel();
            }
        }

        function carregarAnalytics() {
            const script = document.createElement("script");
            script.src = "https://www.googletagmanager.com/gtag/js?id=G-490820365";
            script.async = true;
            document.head.appendChild(script);

            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', 'G-490820365');
        }

        function carregarFacebookPixel() {
            ! function(f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function() {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = '2.0';
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s)
            }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');

            fbq('init', '123456789012345');
            fbq('track', 'PageView');
        }

        window.onload = () => {
            const banner = document.getElementById("cookieBanner");
            const consent = getCookie("cookiesConsentimento");

            if (consent) {
                banner.classList.remove("ativo");
                aplicarConsentimento(consent);
            } else {
                banner.classList.add("ativo");
            }
        };
    </script>



    <footer style="background-color: #071a3f; color: #ffd700; padding: 40px 20px; font-family: Arial, sans-serif;">
        <div style="max-width: 1200px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between;">
            <!-- Informações de Contato -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Contato</h5>
                <p>📞 Telefone: (35) 99702-9569</p>
                <div class="rede-sociais">
                    <a href="https://www.facebook.com/alexandrejose.peres/" target="_blank"><img src="uploads/facebook.png">Facebook</a>
                    <a href="https://www.instagram.com/alexandrejpdp/" target="_blank"><img src="uploads/instagram.png">Instagram</a>
                    <a href="https://wa.me/5535997029569?text=Olá%2C%20tenho%20interesse" target="_blank"><img src="uploads/whatsapp.png">Whatsapp</a>
                </div>
            </div>

            <!-- Navegação Rápida -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Links Úteis</h5>
                <p><a href="#sobre">Sobre Nós</a></p>
                <p><a href="pages/produtos.php">Produtos</a></p>
                <p><a href="pages/comercios.php">Comércios</a></p>

            </div>

            <!-- Informações Legais -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Legal</h5>
                <p><a href="pages/politica-privacidade.php">Política de Privacidade</a></p>
                <p><a href="pages/termos-de-uso.php">Termos de Uso</a></p>
                <p><a href="#">Política de Cookies</a></p>
            </div>

            <!-- Newsletter -->
            <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                <h5 style="margin-bottom: 15px;">Newsletter</h5>
                <p>Inscreva-se para receber novidades e promoções.</p>
                <form>
                    <input type="email" placeholder="Seu e-mail" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                    <button type="submit" style="width: 100%; padding: 10px; background-color: #ff6600; color: #fff; border: none; cursor: pointer;">Enviar</button>
                </form>
            </div>
        </div>
    </footer>

    <!-- Direitos Autorais -->
    <div style="text-align: center; padding: 15px 0; background-color: #111; color: #ffd700;">
        © 2025 Empresa. Todos os direitos reservados.
    </div>

    <script src="assets/js/index.js"></script>

</body>

</html>
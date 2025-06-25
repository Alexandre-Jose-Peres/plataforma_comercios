<?php
include '../backend/database/conexao.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../usuarios/login.php?msg=Acesso negado! Faça login.");
    exit();
}

// Buscar os dados do usuário logado
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nome, foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

$menu1 = 'index';
$menu2 = 'comercio';
$menu3 = 'comerciantes';
$menu4 = 'pedidos';
$menu5 = 'produtos';
$menu6 = 'planos';
$menu7 = 'usuarios';


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dasshboard.css">

    <style>
        .container {
            text-align: center;
        }

        h1 {
            display: flex;
            font-size: 2rem;
            letter-spacing: 4px;
           
            white-space: nowrap;
            text-align: center;
            justify-content: center;
        }

        .letter {
            opacity: 0;
            transform: translateY(20px);
            display: inline-block;
            animation: fadeInUp 0.6s forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

    <!-- Botão do menu -->
    <button id="menu-toggle" class="menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar/Menu -->
    <nav class="sidebar" id="sidebar">
        <ul>
            <li><a href="index.php?pagina=<?php echo $menu2 ?>"><i class="fas fa-cash-register"></i><span>HOME</span></a></li>
            <li><a href="index.php?pagina=<?php echo $menu3 ?>"><i class="fa-regular fa-user"></i></i><span>VENDER</span></a></li>
            <li><a href="#"><i class="fas fa-box-open"></i><span>PRODUTOS</span></a></li>
            <li><a href="index.php?pagina=<?php echo $menu7 ?>"><i class="fas fa-users"></i><span>USUÁRIOS</span></a></li>
            <li><a href="#"><i class="fas fa-chart-line"></i><span>CLIENTES</span></a></li>
            <li><a href="#"><i class="fas fa-cog"></i><span>Configurações</span></a></li>
        </ul>
    </nav>

    <!-- Conteúdo principal -->
    <main class="content" id="content">
        <header class="header-nav">
            <div class="logo">
                <img src="<?php echo $icone_sistema; ?>" alt="<?php echo $nome_sistema; ?>">
            </div>


            <div class="container">
                <h1 id="brand-name"></h1>
            </div>

            <script>
                const brand = "SHOP SPACE";
                const brandElement = document.getElementById("brand-name");

                // Cores intercaladas para INTERATIVE
                const colors = ["#00bcd4", "#03a9f4", "#2196f3", "#3f51b5", "#00bcd4", "#03a9f4", "#2196f3", "#3f51b5", "#00bcd4", "#03a9f4"];

                brand.split("").forEach((char, i) => {
                    const span = document.createElement("span");
                    span.textContent = char === " " ? "\u00A0" : char;
                    span.classList.add("letter");

                    // TEC (índices 0,1,2) = azul fixo
                    if (i < 3) {
                        span.style.color = "#2196f3";
                    }
                    // INTERATIVE = cores intercaladas
                    else if (char !== " ") {
                        span.style.color = colors[i - 4] || "#2196f3";
                    }

                    span.style.animationDelay = `${i * 0.1}s`;
                    brandElement.appendChild(span);
                });
            </script>

            <div class="user-menu">
                <div class="user-info" onclick="toggleMenu()">
                    <?php
                    if (!empty($usuario['foto'])) : ?>
                        <img src="../uploads/usuarios/<?= $usuario['foto']; ?>" class="foto" width="150"><br>
                    <?php else : ?>
                        <img src="../uploads/usuário.png" class="foto" width="150px" alt="Imagem padrão">
                    <?php endif; ?>


                    <span> Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                </div>
                <div class="menu-dropdown" id="dropdown">
                    <a href="#" onclick="abrirEdicao(<?= $_SESSION['user_id'] ?>)">Editar perfil</a>
                    <a href="../backend/auth/logout.php">Sair</a>
                </div>
            </div>

            <script>
                function toggleMenu() {
                    const dropdown = document.getElementById("dropdown");
                    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
                }

                // Fecha o menu ao clicar fora
                window.onclick = function(event) {
                    if (!event.target.closest('.user-menu')) {
                        document.getElementById("dropdown").style.display = "none";
                    }
                };
            </script>
        </header>

        <?php

        if (@$_GET['pagina'] == $menu1) {
            require_once($menu1 . '.php');
        } else if (@$_GET['pagina'] == $menu2) {
            require_once($menu2 . '.php');
        } else if (@$_GET['pagina'] == $menu3) {
            require_once($menu3 . '.php');
        } else if (@$_GET['pagina'] == $menu4) {
            require_once($menu4 . '.php');
        } else if (@$_GET['pagina'] == $menu5) {
            require_once($menu5 . '.php');
        } else if (@$_GET['pagina'] == $menu6) {
            require_once($menu6 . '.php');
        } else if (@$_GET['pagina'] == $menu7) {
            require_once($menu7 . '.php');
        } else {
            require_once($menu1 . '.php');
        }


        ?>
    </main>
    <div id="modal-editar" class="modal" onclick="if(event.target === this) this.style.display='none'">
        <div class="modal-content">

            <div class="header-modal">
                <h3>Editar Usuário</h3>
            </div>
            <form id="form-editar" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">

                <label>Nome:</label>
                <input type="text" name="nome" id="edit-nome" required>

                <label>Email:</label>
                <input type="email" name="email" id="edit-email" required>

                <label>Senha (deixe em branco para manter):</label>
                <input type="password" name="senha" id="edit-senha">

                <label>Foto atual:</label><br>


                <img id="preview-imagem" src="" alt="Imagem do usuário" style="max-width: 150px; margin-bottom: 10px;"><br>

                <label>Nova foto (opcional):</label>
                <input type="file" name="imagem" id="edit-imagem" accept="image/*">

                <div class="btn">
                    <button id="btn-cadastrar" type="submit">Salvar</button>
                    <button id="btn-cancelar" type="button" onclick="document.getElementById('modal-editar').style.display='none'">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const inputImagem = document.getElementById('edit-imagem');
        const previewImagem = document.getElementById('preview-imagem');

        // Atualiza preview quando nova imagem é selecionada
        inputImagem.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                previewImagem.src = URL.createObjectURL(file);
            }
        });

        function abrirEdicao(id) {
            fetch('usuarios/editar_usuario.php?buscar_usuario=' + id)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('edit-id').value = data.id;
                    document.getElementById('edit-nome').value = data.nome;
                    document.getElementById('edit-email').value = data.email;
                    document.getElementById('edit-senha').value = '';
                    document.getElementById('modal-editar').style.display = 'flex';
                    document.getElementById('edit-imagem').value = '';

                    // Exibe a imagem atual do usuário ou a imagem padrão
                    previewImagem.src = data.imagem ? '../uploads/usuarios/' + data.imagem : 'img/sem-foto.png';


                    console.log('Imagem carregada:', previewImagem.src);
                });
        }



        document.getElementById('form-editar').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('usuarios/editar_usuario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    // Fecha o modal de edição
                    document.getElementById('modal-editar').style.display = 'none';

                    // Atualiza a página
                    location.reload();
                })
                .catch(error => {
                    console.error('Erro ao editar o usuário:', error);
                });
        });
    </script>

    <script src="../assets/js/script.js"></script>
</body>

</html>
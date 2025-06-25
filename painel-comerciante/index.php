<?php
include '../backend/database/conexao.php';

// Inicia a sessão apenas se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifique se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../usuarios/login.php?msg=Acesso negado! Faça login.");
    exit();
}
$usuario_id = $_SESSION['user_id'];

// 1️⃣ Buscar os dados do usuário logado
$sqlUser = "SELECT nome, email, telefone, cadastro_completo, cadastro_comercio FROM usuarios WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $usuario_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$usuario = $resultUser->fetch_assoc();

// Verifica se o cadastro está completo
$cadastro_completo = $usuario['cadastro_completo'];
$cadastro_comercio = $usuario['cadastro_comercio'];


// 2️⃣ Buscar dados do comerciante associado ao usuário
$sqlComerciante = "SELECT * FROM comerciantes WHERE usuario_id = ?";
$stmtComerciante = $conn->prepare($sqlComerciante);
$stmtComerciante->bind_param("i", $usuario_id);
$stmtComerciante->execute();
$resultComerciante = $stmtComerciante->get_result();
$comerciante = $resultComerciante->fetch_assoc();

// Se o formulário for enviado (cadastro ou edição)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coleta os dados do formulário
    $empresa_nome = $_POST['empresa_nome'];
    $cep = $_POST['cep'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $estado = $_POST['estado'];
    $telefone = $_POST['telefone'];

    $redes_sociais = $_POST['redes_sociais'];
    $foto = $comerciante['foto'] ?? null; // Foto atual
    $status = 1; // Status ativo por padrão
    $criado_em = date('Y-m-d H:i:s'); // Data e hora de criação

    // Verifica e faz upload da nova foto se enviada
    if (!empty($_FILES['foto']['name'])) {
        // Verifica a extensão da imagem
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif'];
        $pastaDestino = '../uploads/usuarios';  // Diretório correto

        if (in_array($extensao, $extensoesPermitidas)) {
            $nomeFoto = uniqid('comerciante_') . '.' . $extensao; // Gera um nome único para a foto
            $caminhoFoto = $pastaDestino . $nomeFoto;

            // Verifica se a pasta existe, caso contrário cria
            if (!is_dir($pastaDestino)) {
                mkdir($pastaDestino, 0777, true);
            }

            // Move a foto para o diretório
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoFoto)) {
                // Se já existir uma foto anterior, exclui ela
                if ($foto && file_exists("../" . $foto)) {
                    unlink("../" . $foto);
                }
                $foto = '../uploads/usuarios' . $nomeFoto; // Atualiza o caminho da foto (no banco de dados)
            }
        } else {
            echo "Apenas arquivos de imagem (JPG, JPEG, PNG, GIF) são permitidos.";
        }
    }

    if (!$comerciante) {
        $sql = "INSERT INTO comerciantes (usuario_id, empresa_nome, cep, endereco, cidade, estado, telefone, redes_sociais, foto, status, criado_em) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        $stmt->bind_param("issssssssis", $usuario_id, $empresa_nome, $cep, $endereco, $cidade, $estado, $telefone, $redes_sociais, $foto, $status, $criado_em);
    } else {
        $sql = "UPDATE comerciantes SET empresa_nome=?, cep=?, endereco=?, cidade=?, estado=?, telefone=?, redes_sociais=?, foto=? WHERE usuario_id=?";
        $stmt = $conn->prepare($sql);

        // Se a foto não for enviada, passamos NULL para o parâmetro de foto
        $fotoParam = $foto ?: NULL;
        $stmt->bind_param("ssssssssi", $empresa_nome, $cep, $endereco, $cidade, $estado, $telefone, $redes_sociais, $fotoParam, $usuario_id);
    }

    // Executa a query
    $stmt->execute();
    header("Location: index.php?msg=Dados atualizados com sucesso!"); // Redireciona após salvar
    exit();
}


$menu1 = 'home';
$menu2 = 'comerciantes';
$menu3 = 'comercio';
$menu4 = 'produtos';
$menu5 = 'configuracao';

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
            <li><a href="index.php?pagina=<?php echo $menu1 ?>"><i class="fas fa-cash-register"></i><span>HOME</span></a></li>
            <li><a href="index.php?pagina=<?php echo $menu2 ?>"><i class="fa-regular fa-user"></i></i><span>EDITAR PERFIL</span></a></li>
            <li><a href="index.php?pagina=<?php echo $menu3 ?>"><i class="fa-regular fa-user"></i></i><span>COMÉRCIO</span></a></li>
            <li><a href="index.php?pagina=<?php echo $menu4 ?>"><i class="fas fa-box-open"></i><span>PRODUTOS</span></a></li>
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
                    if (!empty($comerciante['foto'])) : ?>
                        <img src="../<?= $comerciante['foto']; ?>" class="foto-produto" width="150"><br>
                    <?php else : ?>
                        <img src="../uploads/usuário.png" class="foto" width="150px" alt="Imagem padrão">
                    <?php endif; ?>


                    <span> Bem-vindo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                </div>
                <div class="menu-dropdown" id="dropdown">
                    <!--<a href="#" onclick="abrirEdicao(<?= $_SESSION['user_id'] ?>)">Editar perfil</a>-->
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

        <?php if (!$cadastro_completo || !$cadastro_comercio): ?>
            <div class="alerta-cadastro">
                <?php if (!$cadastro_completo && !$cadastro_comercio): ?>
                    ⚠️ Complete seu <a href="index.php?pagina=<?php echo $menu2 ?>">perfil</a> e o <a href="index.php?pagina=<?php echo $menu3 ?>">cadastro do seu comércio</a> para ativar totalmente sua conta.
                <?php elseif (!$cadastro_completo): ?>
                    ⚠️ Complete seu <a href="index.php?pagina=<?php echo $menu2 ?>">perfil</a> para continuar usando a plataforma.
                <?php elseif (!$cadastro_comercio): ?>
                    ⚠️ Complete o <a href="index.php?pagina=<?php echo $menu3 ?>">cadastro do seu comércio</a> para continuar usando a plataforma.
                <?php endif; ?>
            </div>
        <?php endif; ?>


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
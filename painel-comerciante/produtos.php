<?php
require '../backend/database/conexao.php';
ob_start();
$pag = 'produtos';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../usuarios/login.php?msg=Acesso negado! Faça login.");
    exit();
}

$por_pagina_padrao = 10;
$pagina = max((int)($_GET['pagina'] ?? 1), 1);
$search = $_GET['search'] ?? '';
$comerciante_id = $_SESSION['user_id'];
$search_term = "%$search%";



$sql_total = "SELECT COUNT(*) AS total FROM produtos WHERE comerciante_id = ? AND (nome LIKE ? OR descricao LIKE ?)";
$stmt_total = $conn->prepare($sql_total);
$stmt_total->bind_param("iss", $comerciante_id, $search_term, $search_term);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_produtos = $result_total->fetch_assoc()['total'];

$por_pagina = $por_pagina_padrao;
$inicio = ($pagina - 1) * $por_pagina;

$sql = "SELECT * FROM produtos WHERE comerciante_id = ? AND (nome LIKE ? OR descricao LIKE ?) LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issii", $comerciante_id, $search_term, $search_term, $inicio, $por_pagina);
$stmt->execute();
$result = $stmt->get_result();
$total_paginas = ceil($total_produtos / $por_pagina);

if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_clean();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categorias = [
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
            ]; // categorias aqui
            $categoria_nome = $categorias[$row['categoria']] ?? 'Categoria não encontrada';
?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['nome']); ?></td>
                <td><?= htmlspecialchars($row['descricao']); ?></td>
                <td><?= "R$ " . number_format($row['preco'], 2, ',', '.'); ?></td>
                <td><?= htmlspecialchars($categoria_nome); ?></td>
                <td><?= $row['status']; ?></td>
                <td>
                    <div class="foto-container">
                        <?php
                        if (!empty($row['foto'])):
                            $fotos = explode(',', $row['foto']);
                            foreach ($fotos as $foto): ?>
                                <img src="../<?= $foto; ?>" class="foto-produto" width="50" alt="Foto do Produto"><br>
                            <?php endforeach;
                        else: ?>
                            <i class="fas fa-image" style="font-size: 50px; color: #F39C12;"></i>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <button class="btn edit" onclick="editarProduto(<?= $row['id']; ?>)">Editar</button>
                    <button class="btn delete" onclick="excluirProduto(<?= $row['id']; ?>)">🗑️ Excluir</button>
                </td>
            </tr>
<?php
        }
    } else {
        // Quando nenhum produto for encontrado
        echo '<tr><td colspan="8" style="text-align:center; color:#888;">Nenhum produto encontrado.</td></tr>';

    }

    exit();
}


?>





<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Lista de Produtos</title>
    <link rel="stylesheet" href="../assets/css/dasshboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="main-container">
        <div class="tabela-header">
            <div class="container-pesquisa">
                <i class="fas fa-search"></i>
                <input type="text" id="busca" placeholder="Buscar por nome ou descrição" />
            </div>
            <button id="botao" onclick="document.getElementById('myModal').style.display='flex'">Cadastrar Novo Produto</button>
        </div>
        <div class="table-container">
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Preço</th>
                        <th>Categoria</th>
                        <th>Status</th>
                        <th>Imagem</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-produtos">
                    <?php
                    $categorias = [
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
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            $categoria_nome = $categorias[$row['categoria']] ?? 'Categoria não encontrada';
                    ?>
                            <tr>
                                <td><?= $row['id']; ?></td>
                                <td><?= htmlspecialchars($row['nome']); ?></td>
                                <td><?= htmlspecialchars($row['descricao']); ?></td>
                                <td><?= "R$ " . number_format($row['preco'], 2, ',', '.'); ?></td>
                                <td><?= htmlspecialchars($categoria_nome); ?></td>
                                <td><?= $row['status']; ?></td>
                                <td>
                                    <div class="foto-container">
                                        <?php
                                        if (!empty($row['foto'])):
                                            $fotos = explode(',', $row['foto']);
                                            foreach ($fotos as $foto): ?>
                                                <img src="../<?= $foto; ?>" class="foto-produto" width="50" alt="Foto do Produto"><br>
                                            <?php endforeach;
                                        else: ?>
                                            <i class="fas fa-image" style="font-size: 50px; color: #F39C12;"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn edit" onclick="editarProduto(<?= $row['id']; ?>)">Editar</button>
                                    <button class="btn delete" onclick="excluirProduto(<?= $row['id']; ?>)">🗑️ Excluir</button>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="8">Nenhum produto encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#busca").on("keyup", function() {
                const valorBusca = $(this).val();
                const pagina = 1; // Sempre volta para a primeira página em nova busca

                $.get("produtos.php", {
                    ajax: 1,
                    pagina: pagina,
                    search: valorBusca
                }, function(dados) {
                    $('#tabela-produtos').html(dados);
                });
            });
        });

        $.get("ajax_listar_produtos.php", {
            search: valorBusca
        }, function(dados) {
            $('#tabela-produtos').html(dados);
        });
    </script>

    <div class="pagination" style="margin-top: 15px;">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <a href="?pagina=<?= $i ?>" class="<?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <script>
        // Função de paginação com AJAX
        document.querySelectorAll('.pagination a').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const pagina = this.getAttribute('href').split('=')[1];
                const search = document.getElementById('busca').value;

                fetch('produtos.php?ajax=1&pagina=' + pagina + '&search=' + encodeURIComponent(search))
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('tabela-produtos').innerHTML = html;
                        window.history.pushState({}, '', '?pagina=' + pagina + '&search=' + encodeURIComponent(search)); // Atualiza a URL sem recarregar
                    });
            });
        });
    </script>

    </div>


    <script>
        $(document).ready(function() {
            // Evento de envio do formulário via AJAX
            $("#CadastrarForm").submit(function(event) {
                event.preventDefault(); // Impede o envio tradicional do formulário

                var formData = new FormData(this); // Captura os dados do formulário, incluindo arquivos

                $.ajax({
                    type: "POST",
                    url: "produtos/cadastrar.php", // Arquivo PHP que processa o cadastro
                    data: formData,
                    contentType: false, // Importante para upload de arquivos
                    processData: false, // Importante para FormData
                    success: function(response) {
                        console.log(response); // Verifique a saída no console do navegador
                        try {
                            var res = JSON.parse(response);
                            alert(res.message); // Exibe a mensagem de sucesso/erro do PHP
                            if (res.status === "success") {
                                $("#CadastrarForm")[0].reset(); // Limpa o formulário após cadastro
                                location.reload(); // Recarrega a página para atualizar a lista de produtos
                            }
                        } catch (e) {
                            console.error("Erro ao processar a resposta:", response);
                            alert("Erro inesperado. Veja o console.");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Erro na requisição:", error);
                        alert("Erro na requisição. Tente novamente.");
                    }
                });
            });
        });
    </script>


    <script>
        function editarProduto(id) {
            $.ajax({
                type: "POST",
                url: "produtos/get_produto.php",
                data: {
                    id: id
                },
                success: function(response) {
                    let produto = JSON.parse(response);

                    document.getElementById('edit-id').value = produto.id;
                    document.getElementById('edit-nome').value = produto.nome;
                    document.getElementById('edit-descricao').value = produto.descricao;
                    document.getElementById('edit-preco').value = produto.preco;
                    document.getElementById('edit-categoria').value = produto.categoria;
                    document.getElementById('edit-status').value = produto.status;

                    if (produto.foto) {
                        let imgPreview = document.getElementById('edit-img-preview');
                        imgPreview.src = "http://localhost/plataforma_comercios/" + produto.foto;
                        imgPreview.style.display = "block";
                    }

                    // Exibe a modal
                    document.getElementById('editModal').style.display = 'flex';
                },
                error: function() {
                    alert("Erro ao buscar dados do produto.");
                }
            });
        }

        // Função para excluir um produto
        function excluirProduto(id) {
            if (confirm("Tem certeza que deseja excluir este produto?")) {
                $.ajax({
                    type: "POST",
                    url: "produtos/excluir.php",
                    data: {
                        id: id
                    },
                    success: function(response) {
                        alert(response);
                        location.reload(); // Atualiza a página para refletir a exclusão
                    },
                    error: function() {
                        alert("Erro ao excluir o produto.");
                    }
                });
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $("#editForm").submit(function(event) {
                event.preventDefault();

                // Cria o objeto FormData para enviar os dados do formulário, incluindo o arquivo
                let formData = new FormData(this); // 'this' se refere ao formulário

                $.ajax({
                    type: "POST",
                    url: "produtos/editar.php",
                    data: formData,
                    processData: false, // Necessário para enviar o FormData
                    contentType: false, // Necessário para enviar o FormData
                    success: function(response) {
                        alert(response);
                        document.getElementById('editModal').style.display = 'none';
                        location.reload();
                    },
                    error: function() {
                        alert("Erro ao salvar as alterações.");
                    }
                });
            });
        });
    </script>


</body>

</html>

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('myModal').style.display='none'">&times;</span>
        <header>

            <h2>Cadastro de Produto</h2>
        </header>
        <form id="CadastrarForm" method="post" enctype="multipart/form-data">

            <label for="nome">Nome do Produto:</label>
            <input type="text" id="nome" name="nome" required><br>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required></textarea><br>

            <div class="container-grupo">

                <div>
                    <label for="preco">Preço:</label>
                    <input type="number" id="preco" name="preco" step="0.01" required><br>
                </div>

                <div>
                    <label for="categoria">Categoria:</label>
                    <select name="categoria" id="categoria" required>
                        <?php
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

                        foreach ($categorias as $key => $value) {
                            $selected = ($categoria == $key) ? 'selected' : '';  // Verifique se a categoria já está selecionada
                            echo "<option value=\"$key\" $selected>$value</option>";
                        }
                        ?>
                    </select><br>

                </div>


                <div>
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select><br>
                </div>
            </div>

            <label for="arquivos">Imagens e Vídeos:</label>
            <input type="file" id="foto-cadastrar" name="arquivos[]" multiple><br><br>

            <button type="submit">Cadastrar</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <header>
            <h2>Editar Produto</h2>
        </header>
        <form id="editForm" method="post" action="produtos/editar.php" enctype="multipart/form-data">
            <label for="nome">Nome do Produto:</label>
            <input type="text" id="edit-nome" name="nome" required><br>

            <label for="descricao">Descrição:</label>
            <textarea id="edit-descricao" name="descricao" required></textarea><br>

            <div class="container-grupo">

                <div>
                    <label for="preco">Preço:</label>
                    <input type="number" id="edit-preco" name="preco" step="0.01" required><br>
                </div>

                <div>
                    <label for="categoria">Categoria:</label>
                    <select name="categoria" id="edit-categoria" required>
                        <?php
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

                        foreach ($categorias as $key => $value) {
                            $selected = ($categoria == $key) ? 'selected' : '';  // Verifique se a categoria já está selecionada
                            echo "<option value=\"$key\" $selected>$value</option>";
                        }
                        ?>
                    </select><br>

                </div>


                <div>
                    <label for="status">Status:</label>
                    <select id="edit-status" name="status" required>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select><br>
                </div>
            </div>

            <img id="edit-img-preview" style="display:none; width:150px" alt="Imagem do Produto">

            <label for="foto">Imagens e Vídeos:</label>
            <input type="file" id="foto" name="foto"><br><br>

            <input type="hidden" id="edit-id" name="id">

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#editForm").submit(function(event) {
            event.preventDefault();

            // Cria o objeto FormData para enviar os dados do formulário, incluindo o arquivo
            let formData = new FormData(this); // 'this' se refere ao formulário

            $.ajax({
                type: "POST",
                url: "produtos/editar.php",
                data: formData,
                processData: false, // Necessário para enviar o FormData
                contentType: false, // Necessário para enviar o FormData
                success: function(response) {
                    alert(response);
                    document.getElementById('editModal').style.display = 'none';
                    location.reload();
                },
                error: function() {
                    alert("Erro ao salvar as alterações.");
                }
            });
        });
    });


    function excluirProduto(id) {
        if (confirm("Tem certeza que deseja excluir este produto?")) {
            $.post("produtos/excluir.php", {
                id
            }, function(response) {
                alert(response);
                location.reload();
            });
        }
    }
</script>
<?php
include '../backend/database/conexao.php';

$pag = 'produtos';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dasshboard.css">
    <script src="../js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        #editModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
    </style>
</head>

<body>
    <button id="botão" onclick="document.getElementById('myModal').style.display='block'">Abrir Cadastro</button>


    <div class="tabela-header">
        <div class="pag">
            <label for="linhasPorPagina">Linhas por página:</label>
            <select id="linhasPorPagina" onchange="alterarItensPorPagina()">
                <option value="2">10</option>
                <option value="5">50</option>
                <option value="10">100</option>
            </select>
        </div>
        <div class="search-box">
            <svg viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="7" stroke="black" stroke-width="2" />
                <line x1="16" y1="16" x2="22" y2="22" stroke="black" stroke-width="2" />
            </svg>
            <input type="text" placeholder="Pesquisar...">
        </div>
    </div>
    <div class="container">
        <table>

            <?php

            $sql = "SELECT * FROM produtos WHERE comerciante_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $comerciante_id); // "i" é o tipo para integer (ID do comerciante)
            $stmt->execute();
            $result = $stmt->get_result();

            // Verifica se há registros
            if ($result->num_rows > 0) {
            ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Comércio</th>
                            <th>Nome do Produto</th>
                            <th>Descrição</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Foto</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-corpo">
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome_comercio']); ?></td> <!-- Nome do comércio exibido -->
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($row['preco']); ?></td>
                                <td><?php echo htmlspecialchars($row['estoque']); ?></td>
                                <td><img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="Imagem do Produto" width="50"></td>
                                <td><?php echo htmlspecialchars($row['criado_em']); ?></td>
                                <td>
                                    <button id="editar" onclick="editarComercio(<?php echo $row['id']; ?>)">✏️ Editar</button>
                                    <button id="excluir" onclick="excluirComercio(<?php echo $row['id']; ?>)">🗑️ Excluir</button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php
            } else {
                echo '<p>Não existem dados para serem exibidos!</p>';
            }

            // Fechar conexão
            $conn->close();
            ?>



        </table>
        <div id="paginacao">
            <div id="pagina-botoes"></div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        let itensPorPagina = 10;
        let paginaAtual = 1;
        let tabelaCorpo = document.getElementById("tabela-corpo");
        let linhas = Array.from(tabelaCorpo.getElementsByTagName("tr"));
        let paginacao = document.getElementById("pagina-botoes");

        function exibirPagina(pagina) {
            tabelaCorpo.innerHTML = "";
            let inicio = (pagina - 1) * itensPorPagina;
            let fim = inicio + itensPorPagina;
            linhas.slice(inicio, fim).forEach(linha => tabelaCorpo.appendChild(linha));
            atualizarPaginacao();
        }

        function atualizarPaginacao() {

            let totalPaginas = Math.ceil(linhas.length / itensPorPagina);
            paginacao.innerHTML = `
        <div id="pagina-info">Página ${paginaAtual} de ${totalPaginas}</div>
        
    `;
            for (let i = 1; i <= totalPaginas; i++) {
                let btn = document.createElement("button");
                btn.innerText = i;
                btn.onclick = () => {
                    paginaAtual = i;
                    exibirPagina(i);
                };
                if (i === paginaAtual) btn.style.fontWeight = "bold";
                paginacao.appendChild(btn);
            }
        }

        function alterarItensPorPagina() {
            itensPorPagina = parseInt(document.getElementById("linhasPorPagina").value);
            paginaAtual = 1;
            exibirPagina(paginaAtual);
        }

        exibirPagina(paginaAtual);
    </script>

    <script>
        $(document).ready(function() {
            // Evento para capturar o envio do formulário via AJAX
            $("#CadastrarForm").submit(function(event) {
                event.preventDefault(); // Impede o envio tradicional do formulário

                var formData = $(this).serialize(); // Coleta os dados do formulário

                $.ajax({
                    type: "POST",
                    url: "produtos/cadastrar.php", // URL do arquivo PHP que processa o cadastro
                    data: formData,
                    success: function(response) {
                        alert(response); // Mensagem de sucesso ou erro
                        if (response.includes("Produto cadastrado com sucesso!")) {
                            // Limpa os campos do formulário
                            $("form")[0].reset();

                            // Fecha a modal
                            document.getElementById('myModal').style.display = 'none';

                            // Atualiza a página comercios.php
                            location.reload(); // Isso recarrega a página atual
                        }
                    },
                    error: function() {
                        alert("Erro ao enviar os dados. Tente novamente.");
                    }
                });
            });
        });


        // Função para atualizar a tabela após o cadastro
        function atualizarTabela() {
            $.ajax({
                type: "GET",
                url: "produtos/listar.php", // Arquivo PHP para listar os comercios
                success: function(response) {
                    $("#tabela-corpo").html(response); // Atualiza o conteúdo da tabela
                },
                error: function() {
                    alert("Erro ao atualizar a tabela.");
                }
            });
        }
    </script>

    <script>
        // Função para abrir o modal de edição com os dados do comércio
        function editarComercio(id) {
            $.ajax({
                type: "POST",
                url: "produtos/get_comercio.php", // Criamos um novo arquivo para buscar os dados do comércio
                data: {
                    id: id
                },
                success: function(response) {
                    let comercio = JSON.parse(response);

                    // Preenche os campos do formulário no modal de edição
                    document.getElementById('edit-id').value = comercio.id;
                    document.getElementById('edit-nome').value = comercio.nome;
                    document.getElementById('edit-comercio_id').value = comercio.comercio_id;
                    document.getElementById('edit-descricao').value = comercio.descricao;
                    document.getElementById('edit-preco').value = comercio.preco;
                    document.getElementById('edit-estoque').value = comercio.estoque;
                    document.getElementById('edit-foto').value = comercio.foto;

                    // Abre a modal de edição
                    document.getElementById('editModal').style.display = 'block';
                },
                error: function() {
                    alert("Erro ao buscar dados do comércio.");
                }
            });
        }

        // Função para excluir um comércio
        function excluirComercio(id) {
            if (confirm("Tem certeza que deseja excluir este comércio?")) {
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
                        alert("Erro ao excluir o comércio.");
                    }
                });
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $("#editForm").submit(function(event) {
                event.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    type: "POST",
                    url: "produtos/editar.php",
                    data: formData,
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

            <h2>Cadastro de Comércio</h2>
        </header>
        <form id="CadastrarForm" method="post">

            <div class="grupo">
                <div>
                    <label>Nome do Produto:</label>
                    <input type="text" name="nome_produto" required>
                </div>

                <div>
                    <select name="nome_comercio" required>
                        <option value="">Selecione</option>
                        <?php
                        include '../config.php';
                        $sql = "SELECT nome FROM comercios ORDER BY nome ASC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['nome']) . '">' . htmlspecialchars($row['nome']) . '</option>';
                            }
                        }
                        ?>
                    </select>

                </div>


            </div>

            <div>
                <label>Descrição:</label>
                <input type="text" name="descricao">
            </div>

            <div class="grupo">
                <label>Preço:</label>
                <input type="text" name="preco" required>

                <div>
                    <label>Estoque:</label>
                    <input type="text" name="estoque" required>
                </div>
            </div>



            <div class="grupo">


                <div>
                    <label>Foto:</label>
                    <input type="text" name="foto">
                </div>
            </div>


            <button type="submit">Cadastrar</button>
        </form>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
        <header>
            <h2>Editar Comércio</h2>
        </header>

        <form id="editForm">
            <input type="hidden" id="edit-id" name="id">
            <label>Nome do Produto:</label>
            <input type="text" id="edit-nome" name="nome" required>
            <label>Nome do Comércio:</label>
            <input type="text" id="edit-comercio_id" name="comercio_id" required>
            <label>Descrição:</label>
            <input type="text" id="edit-descricao" name="descricao">
            <label>Preço:</label>
            <input type="text" id="edit-preco" name="preco" required>
            <label>Estoque:</label>
            <input type="text" id="edit-estoque" name="estoque" required>
            <label>Foto:</label>
            <input type="text" id="edit-foto" name="foto">


            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>
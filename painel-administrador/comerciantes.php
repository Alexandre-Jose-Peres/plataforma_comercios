<?php
include '../backend/database/conexao.php';

$pag = 'comerciantes';
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
        <?php
        $sql = "SELECT * FROM comerciantes ORDER BY id DESC"; // Substitua "tabela_nome" pelo nome correto da tabela
        $result = $conn->query($sql);

        // Verifica se há registros
        if ($result->num_rows > 0) {
        ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>endereço</th>
                        <th>Telefone</th>
                        <th>E-Mail</th>
                        <th>status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-corpo">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['usuario_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['endereco']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
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
            $("#Cadastrarform").submit(function(event) {
                event.preventDefault(); // Impede o envio tradicional do formulário

                var formData = $(this).serialize(); // Coleta os dados do formulário

                $.ajax({
                    type: "POST",
                    url: "comerciantes/cadastrar.php", // URL do arquivo PHP que processa o cadastro
                    data: formData,
                    success: function(response) {
                        alert(response); // Mensagem de sucesso ou erro
                        if (response.includes("Comerciante cadastrado com sucesso!")) {
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
                url: "comerciantes/listar.php", // Arquivo PHP para listar os comercios
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
                url: "comerciantes/get_comercio.php", 
                data: {
                    id: id
                },
                success: function(response) {
                    let comercio = JSON.parse(response);

                    // Preenche os campos do formulário no modal de edição
                    document.getElementById('edit-id').value = comercio.id;
                    document.getElementById('edit-nome').value = comercio.nome;
                    document.getElementById('edit-endereco').value = comercio.endereco;
                    document.getElementById('edit-telefone').value = comercio.telefone;
                    document.getElementById('edit-email').value = comercio.email;

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
                    url: "comerciantes/excluir.php",
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
                    url: "comerciantes/cadastrar.php",
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
        <form id="Cadastrarform" method="post">

            <div class="grupo">
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                </div>

            </div>

            <div>
                <label>Endereço:</label>
                <input type="text" name="endereco">
            </div>


            <div class="grupo">
                <div>
                    <label>Telefone:</label>
                    <input type="text" name="telefone" pattern="[0-9]{10,11}" title="Digite um telefone válido (somente números, 10 ou 11 dígitos)" required>
                </div>

                <div>
                    <label>E-Mail:</label>
                    <input type="text" name="email" required>
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

            <label>Nome:</label>
            <input type="text" id="edit-nome" name="nome" required>

            <label>Endereço</label>
            <input type="text" id="edit-endereco" name="endereco">


            <label>Telefone:</label>
            <input type="text" id="edit-telefone" name="telefone" pattern="[0-9]{10,11}" required>

            <label>Endereço</label>
            <input type="text" id="edit-email" name="email">

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>
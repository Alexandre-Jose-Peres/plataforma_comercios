<?php
include '../backend/database/conexao.php';

$pag = 'pedidos';
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
        <table>

            <?php
            $sql = "SELECT comercios.*, comerciantes.nome AS nome_comerciante 
        FROM comercios 
        JOIN comerciantes ON comercios.usuario_id = comerciantes.id 
        ORDER BY comercios.id DESC";
            $result = $conn->query($sql);

            // Verifica se há registros
            if ($result->num_rows > 0) {
            ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Comércio</th>
                            <th>Comerciante</th>
                            <th>Descrição</th>
                            <th>Cidade</th>
                            <th>Telefone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-corpo">
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['nome_comerciante']); ?></td>
                                <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                                <td><?php echo htmlspecialchars($row['cidade']); ?></td>
                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
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
            $("form").submit(function(event) {
                event.preventDefault(); // Impede o envio tradicional do formulário

                var formData = $(this).serialize(); // Coleta os dados do formulário

                $.ajax({
                    type: "POST",
                    url: "comercios/cadastrar.php", // URL do arquivo PHP que processa o cadastro
                    data: formData,
                    success: function(response) {
                        alert(response); // Mensagem de sucesso ou erro
                        if (response.includes("Comércio cadastrado com sucesso!")) {
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
                url: "comercios/listar.php", // Arquivo PHP para listar os comercios
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
                url: "comercios/get_comercio.php", // Criamos um novo arquivo para buscar os dados do comércio
                data: {
                    id: id
                },
                success: function(response) {
                    let comercio = JSON.parse(response);

                    // Preenche os campos do formulário no modal de edição
                    document.getElementById('edit-id').value = comercio.id;
                    document.getElementById('edit-nome').value = comercio.nome;
                    document.getElementById('edit-descricao').value = comercio.descricao;
                    document.getElementById('edit-cidade').value = comercio.cidade;
                    document.getElementById('edit-telefone').value = comercio.telefone;

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
                    url: "comercios/excluir.php",
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
                    url: "comercios/editar.php",
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
        <form method="post">

            <div class="grupo">
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                </div>

                <div>
                    <select name="nome_comerciante" required>
                        <option value="">Selecione</option>
                        <?php
                        include '../config.php';
                        $sql = "SELECT nome FROM comerciantes ORDER BY nome ASC";
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

            <div>
                <label>Endereço:</label>
                <input type="text" name="endereco" required>
            </div>

            <div class="grupo">

                <div>
                    <label>Cidade:</label>
                    <input type="text" name="cidade" required>
                </div>

                <div>
                    <label>Estado:</label>
                    <select name="estado" required>
                        <option value="">Selecione</option>
                        <option value="AC">Acre</option>
                        <option value="AL">Alagoas</option>
                        <option value="AM">Amazonas</option>
                        <option value="AP">Amapá</option>
                        <option value="BA">Bahia</option>
                        <option value="CE">Ceará</option>
                        <option value="DF">Distrito Federal</option>
                        <option value="ES">Espírito Santo</option>
                        <option value="GO">Goiás</option>
                        <option value="MA">Maranhão</option>
                        <option value="MT">Mato Grosso</option>
                        <option value="MS">Mato Grosso do Sul</option>
                        <option value="MG">Minas Gerais</option>
                        <option value="PA">Pará</option>
                        <option value="PB">Paraíba</option>
                        <option value="PR">Paraná</option>
                        <option value="PE">Pernambuco</option>
                        <option value="PI">Piauí</option>
                        <option value="RJ">Rio de Janeiro</option>
                        <option value="RN">Rio Grande do Norte</option>
                        <option value="RS">Rio Grande do Sul</option>
                        <option value="RO">Rondônia</option>
                        <option value="RR">Roraima</option>
                        <option value="SC">Santa Catarina</option>
                        <option value="SP">São Paulo</option>
                        <option value="SE">Sergipe</option>
                        <option value="TO">Tocantins</option>
                    </select>
                </div>
            </div>

            <div class="grupo">
                <div>
                    <label>Telefone:</label>
                    <input type="text" name="telefone" pattern="[0-9]{10,11}" title="Digite um telefone válido (somente números, 10 ou 11 dígitos)" required>
                </div>

                <div>
                    <label>Categoria:</label>
                    <input type="text" name="categoria" required>
                </div>
            </div>


            <div>
                <label>Redes Sociais:</label>
                <input type="url" name="redes_sociais" placeholder="https://facebook.com/seuperfil">
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

            <label>Descrição:</label>
            <input type="text" id="edit-descricao" name="descricao">

            <label>Cidade:</label>
            <input type="text" id="edit-cidade" name="cidade" required>

            <label>Telefone:</label>
            <input type="text" id="edit-telefone" name="telefone" pattern="[0-9]{10,11}" required>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>
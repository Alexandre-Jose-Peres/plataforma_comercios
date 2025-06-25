<?php
include '../backend/database/conexao.php';

$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$itens_por_pagina = 5;
$offset = ($pagina - 1) * $itens_por_pagina;

$buscar = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

if (!empty($buscar)) {
    $sql = "SELECT * FROM usuarios 
            WHERE nome LIKE '%$buscar%' OR email LIKE '%$buscar%' 
            ORDER BY id DESC 
            LIMIT $offset, $itens_por_pagina";
    $count_sql = "SELECT COUNT(*) AS total FROM usuarios 
                  WHERE nome LIKE '%$buscar%' OR email LIKE '%$buscar%'";
} else {
    $sql = "SELECT * FROM usuarios ORDER BY id DESC LIMIT $offset, $itens_por_pagina";
    $count_sql = "SELECT COUNT(*) AS total FROM usuarios";
}

$result = $conn->query($sql);
$count_result = $conn->query($count_sql);
$total_usuarios = $count_result->fetch_assoc()['total'];
$total_paginas = ceil($total_usuarios / $itens_por_pagina);

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($usuario['nome']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['email']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['nivel']) . "</td>";
        echo "<td>" . htmlspecialchars($usuario['status']) . "</td>";
        echo "<td><img src='../uploads/usuarios/" . htmlspecialchars($usuario['foto']) . "'id='imagem' alt='Imagem' ></td>";

        echo "<td>" . htmlspecialchars($usuario['data_cadastro']) . "</td>";
        echo "<td>
                <a href='#' class='btn edit' onclick='abrirEdicao(" . $usuario['id'] . ")'>Editar</a>
                <a href='#' class='btn delete' onclick='excluirUsuario(" . $usuario['id'] . ")'>Excluir</a>
              </td>";
        echo "</tr>";
    }
    if (empty($usuarios)) {
        echo "<tr><td colspan='4'>Nenhum usuário encontrado.</td></tr>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link rel="stylesheet" href="../assets/css/dasshboard.css">
</head>

<body>
    <div class="main-container">
        <div class="container-pesquisa">
            <i class="fas fa-search"></i>
            <input type="text" id="busca" placeholder="Buscar por nome ou email" />
        </div>
        <button id="btn-abrir-modal">Cadastrar Novo Usuário</button>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Nível</th>
                        <th>Status</th>
                        <th>Foto</th>
                        <th>Data de Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-usuarios">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="4">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td><?= htmlspecialchars($usuario['email']) ?></td>
                                <td><?= htmlspecialchars($usuario['nivel']) ?></td>
                                <td><?= htmlspecialchars($usuario['status']) ?></td>
                                <td><img src="../uploads/usuarios/<?php echo htmlspecialchars($usuario['foto']); ?>" id="imagem" alt="Foto do usuário">
                                </td>
                                <td><?= htmlspecialchars($usuario['data_cadastro']) ?></td>
                                <td>
                                    <!--<a href="#" class="btn edit" onclick="abrirEdicao(<?= $usuario['id'] ?>)">Editar</a>-->
                                    <a href="#" class="btn delete" onclick="excluirUsuario(<?= $usuario['id'] ?>)">Excluir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination" style="margin-top: 15px;">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="#" data-pagina="<?= $i ?>" class="<?= $i === $pagina ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>

        <!-- Modal de Cadastro -->
        <div id="modal" class="modal" onclick="if(event.target === this) this.style.display='none'">
            <div class="modal-content">

                <div class="header-modal">
                    <h3>Cadastrar Usuário</h3>
                </div>
                <form id="form-cadastro">
                    <label>Nome:</label>
                    <input type="text" name="nome" required>
                    <label>Email:</label>
                    <input type="email" name="email" required>
                    <label>Senha:</label>
                    <input type="password" name="senha" required>

                    <input type="file" name="imagem" accept="image/*" required>

                    <div class="btn">
                        <button id="btn-cadastrar" type="submit">Cadastrar</button>
                        <button id="btn-cancelar" type="button" onclick="document.getElementById('modal').style.display='none'">Cancelar</button>
                    </div>
                </form>

            </div>
        </div>

        <script>
            document.getElementById('btn-abrir-modal').addEventListener('click', function() {
                document.getElementById('modal').style.display = 'flex';
            });
        </script>
        

        <div id="mensagem-modal" class="modal" onclick="if(event.target === this) fecharMensagem()">
            <div class="modal-content">
                <p id="mensagem-texto"></p>
                <button id="btn-cancelar" onclick="fecharMensagem()">Fechar</button>
            </div>
        </div>

        <!-- Modal de confirmação -->
        <div id="confirmar-modal" class="modal" onclick="if(event.target === this) fecharConfirmacao()">
            <div class="modal-content">
                <p id="confirmar-texto">Deseja realmente excluir este usuário?</p>
                <button id="btn-cadastrar" onclick="confirmarExclusao()">Sim</button>
                <button id="btn-cancelar" onclick="fecharConfirmacao()">Cancelar</button>
            </div>
        </div>

        <script>
            document.getElementById('busca').addEventListener('input', function() {
                const valor = this.value;
                fetch('usuarios.php?ajax=1&search=' + encodeURIComponent(valor))
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('tabela-usuarios').innerHTML = html;
                    });
            });
        </script>

        <script>
            // Função de paginação com AJAX
            document.querySelectorAll('.pagination a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const pagina = this.getAttribute('href').split('=')[1];
                    const search = document.getElementById('busca').value;

                    fetch('usuarios.php?ajax=1&pagina=' + pagina + '&search=' + encodeURIComponent(search))
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('tabela-usuarios').innerHTML = html;
                            window.history.pushState({}, '', '?pagina=' + pagina + '&search=' + encodeURIComponent(search)); // Atualiza a URL sem recarregar
                        });
                });
            });
        </script>

        <script>
            document.getElementById('form-cadastro').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const formData = new FormData(form);

                fetch('usuarios/cadastrar.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.sucesso) {
                            mostrarMensagem(data.sucesso, true);
                            form.reset();
                            document.getElementById('modal').style.display = 'none';

                            // Atualiza lista
                            document.getElementById('busca').dispatchEvent(new Event('input'));
                        } else {
                            mostrarMensagem(data.erro, false);
                        }
                    })
                    .catch(() => {
                        mostrarMensagem('Erro ao tentar cadastrar.', false);
                    });
            });
        </script>


       


        <script>
            let idParaExcluir = null;

            function excluirUsuario(id) {
                idParaExcluir = id;
                document.getElementById('confirmar-modal').style.display = 'flex';
            }

            function confirmarExclusao() {
                if (!idParaExcluir) return;

                fetch('usuarios/excluir_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'id=' + encodeURIComponent(idParaExcluir)
                    })
                    .then(response => response.json())
                    .then(data => {
                        fecharConfirmacao();
                        if (data.status === 'ok') {
                            mostrarMensagem('Usuário excluído com sucesso!', true);
                        } else {
                            mostrarMensagem('Erro ao excluir usuário.', false);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        fecharConfirmacao();
                        mostrarMensagem('Erro de conexão ao tentar excluir o usuário.', false);
                    });

                idParaExcluir = null;
            }

            function fecharConfirmacao() {
                document.getElementById('confirmar-modal').style.display = 'none';
                idParaExcluir = null;
            }

            function mostrarMensagem(mensagem, sucesso) {
                const modal = document.getElementById('mensagem-modal');
                const texto = document.getElementById('mensagem-texto');
                texto.innerText = mensagem;
                texto.style.color = sucesso ? 'green' : 'red';
                modal.style.display = 'flex';

                if (sucesso) {
                    setTimeout(() => location.reload(), 1000);
                }
            }

            function fecharMensagem() {
                document.getElementById('mensagem-modal').style.display = 'none';
            }
        </script>




        <script>
            document.getElementById('btn-abrir-modal').addEventListener('click', function() {
                document.getElementById('modal').style.display = 'flex';
            });

            document.getElementById('busca').addEventListener('input', function() {
                const valor = this.value;
                fetch('usuarios.php?ajax=1&search=' + encodeURIComponent(valor))
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('tabela-usuarios').innerHTML = html;
                    });
            });

            document.querySelectorAll('.pagination a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const pagina = this.getAttribute('data-pagina');
                    const search = document.getElementById('busca').value;

                    fetch('usuarios.php?ajax=1&pagina=' + pagina + '&search=' + encodeURIComponent(search))
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('tabela-usuarios').innerHTML = html;
                            history.pushState({}, '', '?pagina=' + pagina + '&search=' + encodeURIComponent(search));
                        });
                });
            });
        </script>
    </div>
</body>

</html>
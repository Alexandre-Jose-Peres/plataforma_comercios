<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: ../usuarios/login.php?msg=Acesso negado! Faça login.");
    exit();
}

require_once '../backend/database/conexao.php'; // Arquivo de conexão com o banco

$user_id = $_SESSION['user_id'];
$comercio = []; // Inicializamos a variável para evitar problemas de variável indefinida

$modo_edicao = false; // Inicializamos a variável para indicar que não estamos em modo de edição

// Verifica se o usuário já tem um comércio cadastrado
$sql = "SELECT * FROM comerciantes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // Aqui deve ser o ID do usuário (comerciante)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $comercio = $result->fetch_assoc(); // Preenche a variável $comercio com os dados do comerciante
}

// Verificar se estamos editando ou cadastrando
if (isset($_GET['id'])) {
    // Se 'id' for passado via GET, significa que é uma edição
    $id_comercio = $_GET['id'];
    $modo_edicao = true;

 // Certifique-se de que o ID do comércio foi passado corretamente
if (!isset($id_comercio) || !filter_var($id_comercio, FILTER_VALIDATE_INT)) {
    echo "ID de comerciante inválido.";
    exit();
}

// Consulta ao banco de dados para recuperar os dados do comerciante
$sql = "SELECT * FROM comerciantes WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_comercio);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifique se algum comerciante foi encontrado
    if ($result->num_rows > 0) {
        $comercio = $result->fetch_assoc(); // Armazena os dados do comerciante para edição
    } else {
        echo "Comerciante não encontrado.";
        exit();
    }
} else {
    echo "Erro ao preparar a consulta.";
    exit();
}
}

$modo_edicao = isset($comercio) && !empty($comercio); 


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/dasshboard.css">
    <title>Completar Cadastro</title>
</head>

<body>
    <h2>Complete seu perfil</h2>
    <?php
// Verificar se os dados do comerciante foram carregados
$modo_edicao = isset($comercio) && !empty($comercio);
?>

<form action="perfil/cadastrar.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="usuario_id" value="<?= htmlspecialchars($user_id) ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($comercio['id'] ?? '') ?>">

    <label>Nome da Empresa:</label>
    <input type="text" name="empresa_nome" value="<?= htmlspecialchars($comercio['empresa_nome'] ?? '') ?>" required><br>

    <label>CEP:</label>
    <input type="text" id="cep" name="cep" value="<?= htmlspecialchars($comercio['cep'] ?? '') ?>" required maxlength="9" oninput="formatarCep(this)" onblur="buscarCep(this.value)"><br>

    <label>Endereço:</label>
    <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($comercio['endereco'] ?? '') ?>" required autocomplete="off"><br>

    <label>Cidade:</label>
    <input type="text" id="cidade" name="cidade" value="<?= htmlspecialchars($comercio['cidade'] ?? '') ?>" required readonly><br>

    <label>Estado:</label>
    <select name="estado" id="estado" required>
        <option value="">Selecione um estado</option>
        <?php
        $estados = [
            "AC" => "Acre", "AL" => "Alagoas", "AP" => "Amapá", "AM" => "Amazonas", "BA" => "Bahia", "CE" => "Ceará",
            "DF" => "Distrito Federal", "ES" => "Espírito Santo", "GO" => "Goiás", "MA" => "Maranhão", "MT" => "Mato Grosso",
            "MS" => "Mato Grosso do Sul", "MG" => "Minas Gerais", "PA" => "Pará", "PB" => "Paraíba", "PR" => "Paraná",
            "PE" => "Pernambuco", "PI" => "Piauí", "RJ" => "Rio de Janeiro", "RN" => "Rio Grande do Norte", "RS" => "Rio Grande do Sul",
            "RO" => "Rondônia", "RR" => "Roraima", "SC" => "Santa Catarina", "SP" => "São Paulo", "SE" => "Sergipe", "TO" => "Tocantins"
        ];

        $estadoSelecionado = $comercio['estado'] ?? '';
        foreach ($estados as $sigla => $nome) {
            $selected = ($sigla === $estadoSelecionado) ? 'selected' : '';
            echo "<option value='$sigla' $selected>$nome</option>";
        }
        ?>
    </select>
    <br>

    <label>Telefone:</label>
    <input type="text" name="telefone" value="<?= htmlspecialchars($comercio['telefone'] ?? '') ?>" required><br>

    <label>Redes Sociais:</label>
    <input type="text" name="redes_sociais" value="<?= htmlspecialchars($comercio['redes_sociais'] ?? '') ?>"><br>

    <label>Foto:</label>
    <input type="file" name="foto"><br>

    <?php if (!empty($comercio['foto'])): ?>
        <img src="<?= 'uploads/' . htmlspecialchars($comercio['foto']) ?>" width="100"><br>
    <?php endif; ?>

    <input type="hidden" name="status" value="ativo">

    <button type="submit"><?= $modo_edicao ? 'Atualizar' : 'Cadastrar' ?></button>
</form>


    <!-- SCRIPT PARA FORMATAR O CEP E BUSCAR ENDEREÇO -->
    <script>
        function formatarCep(campo) {
            let cep = campo.value.replace(/\D/g, ''); // Remove tudo que não for número
            if (cep.length > 5) {
                campo.value = cep.substring(0, 5) + '-' + cep.substring(5, 8);
            } else {
                campo.value = cep;
            }
        }

        function buscarCep(cep) {
            cep = cep.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (cep.length === 8) { // CEP tem 8 dígitos
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('endereco').value = data.logradouro;
                            document.getElementById('cidade').value = data.localidade;
                            document.getElementById('estado').value = data.uf;
                        } else {
                            alert("CEP não encontrado!");
                        }
                    })
                    .catch(error => console.error('Erro ao buscar o CEP:', error));
            }
        }
    </script>
</body>

</html>

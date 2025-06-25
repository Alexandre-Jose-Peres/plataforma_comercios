<?php
header('Content-Type: application/json');
include '../../backend/database/conexao.php'; // Usa $conn

$response = ['erro' => '', 'sucesso' => ''];

// Buscar usuário para edição
if (isset($_GET['buscar_usuario'])) {
    $id = (int)$_GET['buscar_usuario'];

    $stmt = $conn->prepare("SELECT id, nome, email, foto AS imagem FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();

    echo json_encode($usuario ?: [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}


define('CAMINHO_IMAGENS_FISICO_USUARIOS', __DIR__ . '/../../uploads/usuarios/');
define('URL_IMAGENS_USUARIOS', 'uploads/usuarios/');

$response = ['erro' => '', 'sucesso' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'])) {
    $usuario_id = (int)$_POST['usuario_id'];

    // Dados do comerciante
    $empresa_nome = trim($_POST['empresa_nome'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $redes_sociais = trim($_POST['redes_sociais'] ?? '');
    $status = 1;
    $criado_em = date('Y-m-d H:i:s');

    // Busca comerciante atual
    $stmt = $conn->prepare("SELECT * FROM comerciantes WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comerciante = $result->fetch_assoc();

    $foto = $comerciante['foto'] ?? null;

    // Upload da imagem
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto']['tmp_name'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extensao, $tiposPermitidos)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Tipo de imagem inválido. Use JPG, PNG, GIF ou WEBP.']);
            exit;
        }

        // Apagar imagem anterior
        if (!empty($foto)) {
            $caminhoAntigo = __DIR__ . '/../../' . $foto;
            if (file_exists($caminhoAntigo)) {
                unlink($caminhoAntigo);
            }
        }

        if (!is_dir(CAMINHO_IMAGENS_FISICO_USUARIOS)) {
            mkdir(CAMINHO_IMAGENS_FISICO_USUARIOS, 0777, true);
        }

        $nomeImagem = uniqid('comerciante_') . '.' . $extensao;
        $caminhoFinal = CAMINHO_IMAGENS_FISICO_USUARIOS . $nomeImagem;

        if (!move_uploaded_file($tmpName, $caminhoFinal)) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao salvar a nova imagem.']);
            exit;
        }

        $foto = URL_IMAGENS_USUARIOS . $nomeImagem;
    }

    // Verifica se cadastro está completo
    $cadastro_completo = (!empty($empresa_nome) && !empty($cep) && !empty($endereco) && !empty($cidade) && !empty($estado) && !empty($telefone)) ? 1 : 0;

    // INSERIR OU ATUALIZAR
    if ($comerciante) {
        $sql = "UPDATE comerciantes SET empresa_nome=?, cep=?, endereco=?, cidade=?, estado=?, telefone=?, redes_sociais=?, foto=? WHERE usuario_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $empresa_nome, $cep, $endereco, $cidade, $estado, $telefone, $redes_sociais, $foto, $usuario_id);
    } else {
        $sql = "INSERT INTO comerciantes (usuario_id, empresa_nome, cep, endereco, cidade, estado, telefone, redes_sociais, foto, status, criado_em)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssis", $usuario_id, $empresa_nome, $cep, $endereco, $cidade, $estado, $telefone, $redes_sociais, $foto, $status, $criado_em);
    }

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['erro' => 'Erro ao salvar os dados do comerciante.']);
        exit;
    }

    // Atualizar status de cadastro na tabela usuários
    $stmt = $conn->prepare("UPDATE usuarios SET cadastro_completo = ? WHERE id = ?");
    $stmt->bind_param("ii", $cadastro_completo, $usuario_id);
    $stmt->execute();

    $response['sucesso'] = 'Dados do comerciante atualizados com sucesso.';
    echo json_encode($response);
    exit;
}

http_response_code(400);
$response['erro'] = 'Requisição inválida.';
echo json_encode($response);
exit;


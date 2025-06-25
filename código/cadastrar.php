<?php
include '../backend/database/conexao.php';
$pag = 'perfil';

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
// Variáveis para armazenar mensagens de erro
$erro_msg = "";

// Função para upload da imagem
function uploadImagem($imagem, $pasta_destino)
{
    $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_destino = $pasta_destino . '/' . $nome_arquivo;

    if (move_uploaded_file($imagem['tmp_name'], $caminho_destino)) {
        return $nome_arquivo;
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dados do formulário
    $nome = $_POST['nome'] ?? null;
    $descricao = $_POST['descricao'] ?? null;
    $endereco_comercio = $_POST['endereco_comercio'] ?? null;
    $cidade_comercio = $_POST['cidade_comercio'] ?? null;
    $estado_comercio = $_POST['estado_comercio'] ?? null;
    $telefone_comercio = $_POST['telefone_comercio'] ?? null;
    $email_comercio = $_POST['email_comercio'] ?? null;
    $site = $_POST['site'] ?? null;
    $horario_func = $_POST['horario_func'] ?? null;
    $categoria = $_POST['categoria'] ?? null;
    $plano = $_POST['plano'] ?? null;

    // Verifique se todos os campos obrigatórios estão preenchidos
    if (!$nome || !$descricao || !$endereco_comercio || !$cidade_comercio || !$estado_comercio || !$telefone_comercio || !$email_comercio) {
        $erro_msg = "Todos os campos obrigatórios devem ser preenchidos!";
    } else {
        // Preparar o upload da imagem
        $foto_comercio = null;
        if (isset($_FILES['foto_comercio']) && $_FILES['foto_comercio']['error'] == 0) {
            $foto_comercio = uploadImagem($_FILES['foto_comercio'], '../img/comercios');
            if (!$foto_comercio) {
                $erro_msg = "Erro ao fazer upload da imagem.";
            }
        }

        if (empty($erro_msg)) {


            // Verifique se o comerciante com esse ID existe na tabela 'comerciantes'
            $sql_check = "SELECT id FROM usuarios WHERE id = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("i", $comerciante_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows == 0) {
                die('Erro: O comerciante com este ID não existe na tabela de comerciantes.');
            } else {
                // Inserção no banco de dados para o comércio
                $sql = "INSERT INTO comercios (comerciante_id, nome, descricao, endereco, cidade, estado, telefone, email, site, horario_func, categoria, plano, foto, status, criado_em) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', NOW())";
                $stmt = $conn->prepare($sql);

                // Verifique se o prepare() foi bem-sucedido
                if ($stmt === false) {
                    die('Erro na preparação da consulta: ' . $conn->error);
                }

                // Vincule os parâmetros
                $stmt->bind_param("issssssssssss",  $usuario_id, $nome, $descricao, $endereco_comercio, $cidade_comercio, $estado_comercio, $telefone_comercio, $email_comercio, $site, $horario_func, $categoria, $plano, $foto_comercio);

                // Execute a consulta
                if ($stmt->execute()) {
                    echo "<script>alert('Comércio cadastrado com sucesso!'); window.location.href = 'index.php';</script>";
                    exit();
                } else {
                    $erro_msg = "Erro ao salvar os dados do comércio: " . $stmt->error;
                }
            }
        }
    }
}


?>

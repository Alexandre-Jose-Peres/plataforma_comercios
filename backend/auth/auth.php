<?php
session_start();
include '../database/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, nome, senha, nivel FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nome, $hashed_password, $nivel);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $nome;
            $_SESSION['user_tipo'] = $nivel;

            // Redirecionar com base no nível do usuário
            if ($nivel == 'comerciante') {
                header("Location: ../../painel-comerciante/index.php?pagina=home");
                exit();
            } elseif ($nivel == 'administrador') {
                echo "<script>localStorage.setItem('id_usu', '$id');</script>";
                echo "<script>window.location.href = '../../painel-administrador/index.php?pagina=home';</script>";
                exit();
            }if ($nivel == 'cliente') {
                header("Location: ../../index.php?pagina=home");
                exit();
            } else {
                header("Location: ../../login.php?msg=Tipo de usuário inválido!");
                exit();
            }
        } else {
            header("Location: login.php?msg=Senha incorreta!");
            exit();
        }
    } else {
        header("Location: login.php?msg=Usuário não encontrado!");
        exit();
    }

    $stmt->close();
    $conn->close();
}

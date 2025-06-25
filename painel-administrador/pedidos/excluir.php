<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    $sql = "DELETE FROM comercios WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Comércio excluído com sucesso!";
    } else {
        echo "Erro ao excluir comércio: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

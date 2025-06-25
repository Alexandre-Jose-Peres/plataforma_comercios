<?php
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    $sql = "DELETE FROM comerciantes WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Comerciante excluído com sucesso!";
    } else {
        echo "Erro ao excluir comércio: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>

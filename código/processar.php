// Se não houver um comércio cadastrado, usa os dados do comerciante para preencher
if (!$comercio) {
    $comercio = [
        'nome' => $comerciante['empresa_nome'],
        'email' =>'',
        'telefone' => $comerciante['telefone'],
        'endereco' => $comerciante['endereco'],
        'cidade' =>$comerciante['cidade'],
        'estado' => $comerciante['estado'],
        'descricao' => '',
        'cep' => $comerciante['cep'],
        'site' => '',
        'horario_func' => '',
        'categoria' => '',
        'foto' => ''
    ];
}

// 3️⃣ Buscar dados do comércio associado ao comerciante
$sqlComercio = "SELECT * FROM comercios WHERE comerciante_id = ?";
$stmtComercio = $conn->prepare($sqlComercio);
if ($stmtComercio === false) {
    die('Erro ao preparar consulta de comércio: ' . $conn->error);
}
$stmtComercio->bind_param("i", $comerciante_id);
$stmtComercio->execute();
$resultComercio = $stmtComercio->get_result();
$comercio = $resultComercio->fetch_assoc();

// 4️⃣ Se não houver um comércio cadastrado, usa os dados do comerciante para preencher
if (!$comercio) {
    $comercio = [
        'nome' => $comerciante['empresa_nome'],
        'email' => '',
        'telefone' => $comerciante['telefone'],
        'endereco' => $comerciante['endereco'],
        'cidade' => $comerciante['cidade'],
        'estado' => $comerciante['estado'],
        'descricao' => '',
        'cep' => $comerciante['cep'],
        'site' => '',
        'horario_func' => '',
        'categoria' => '',
        'foto' => ''
    ];
}

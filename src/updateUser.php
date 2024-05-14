<?php

// use src\database\Connection;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-AMZ-META-TOKEN-ID, X-AMZ-META-TOKEN-SECRET');

include_once("../../page/conexao/conexao.class.php");
   
$pdo = new Conexao(); 

$objData = json_decode(file_get_contents('php://input'), true);
$id = $objData['id'];


try {
    $query = 'SELECT idusuario_transportadora, nome_completo, usuario, telefone, admin, cpfcnpj, data_cadastro, data_atualizacao FROM usuario_transportadora WHERE idusuario_transportadora = :idusuario_transportadora';
    $statement = $pdo->prepare($query);
    $statement->execute(['idusuario_transportadora' => $id]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new \Exception('User does not exists', 404);
    }

    $nome_completo = filter_var($data['nome_completo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $telefone = filter_var($data['telefone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $usuario = filter_var($data['usuario'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $cpfcnpj = filter_var($data['cpfcnpj'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = 'UPDATE usuario_transportadora SET';
    $updates = [];
    $params = [];

    // Verifica e adiciona cada campo para atualização apenas se não estiver vazio
    if (!empty($nome_completo)) {
        $updates[] = ' nome_completo = :nome_completo';
        $params['nome_completo'] = $nome_completo;
    }

    if (!empty($telefone)) {
        $updates[] = ' telefone = :telefone';
        $params['telefone'] = $telefone;
    }

    if (!empty($usuario)) {
        $updates[] = ' usuario = :usuario';
        $params['usuario'] = $usuario;
    }

    if (!empty($cpfcnpj)) {
        $updates[] = ' cpfcnpj = :cpfcnpj';
        $params['cpfcnpj'] = $cpfcnpj;
    }

    // Adiciona a cláusula SET apenas se houver campos para atualização
    if (!empty($updates)) {
        $query .= implode(',', $updates);
        $query .= ' , data_atualizacao = CURRENT_TIMESTAMP';
        $query .= ' WHERE idusuario_transportadora = :idusuario_transportadora';
        $params['idusuario_transportadora'] = $id;

        $statement = $pdo->prepare($query);
        $statement->execute($params);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Nenhum campo fornecido para atualização."]);
    }

    http_response_code(200);
    echo json_encode(['message' => 'Usário atualizado com sucesso']);
    exit();
} catch (\Exception $e) {
    echo json_encode(['message' => $e->getMessage()]);
}

<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-AMZ-META-TOKEN-ID, X-AMZ-META-TOKEN-SECRET');

// use src\database\Connection;

include_once("../../page/conexao/conexao.class.php");
   

$pdo = new Conexao(); 
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];


try {
    $query = 'SELECT idusuario_transportadora, nome_completo, usuario, telefone, admin, cpfcnpj, data_cadastro, data_atualizacao FROM usuario_transportadora WHERE idusuario_transportadora = :idusuario_transportadora';
    $statement = $pdo->prepare($query);
    $statement->execute(['idusuario_transportadora' => $id]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        throw new \Exception('User does not exists', 404);
    }

    http_response_code(200);
    echo json_encode($user);
} catch (\PDOException $e) {
    echo json_encode(['message' => 'Erro ao buscar usuário: ' . $e->getMessage()]);
}

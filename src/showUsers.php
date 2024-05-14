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
    $query = 'SELECT idusuario_transportadora, nome_completo, telefone, admin, usuario, cpfcnpj, data_cadastro, data_atualizacao FROM usuario_transportadora';
    $statement = $pdo->prepare($query);
    $statement->execute();
    $users = $statement->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($users);

  } catch (\PDOException $e) {
    http_response_code(500);
    throw new \Exception('Erro ao buscar usuários: ' . $e->getMessage());
  }
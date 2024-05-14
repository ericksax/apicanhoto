<?php
    // use src\database\Connection;
    
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: X-AMZ-META-TOKEN-ID, X-AMZ-META-TOKEN-SECRET, Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
    
    include_once("../../page/conexao/conexao.class.php");
   
    $pdo = new Conexao(); 
    
    $data = json_decode(file_get_contents("php://input", true), true);
    $chave = $data['chave_acesso'];
    
  try {
    $query = 'SELECT dc.*, 
    u.nome_completo, 
    u.telefone, 
    u.usuario, 
    u.admin, 
    u.data_cadastro, 
    u.data_atualizacao, 
    u.idusuario_transportadora
    FROM documento_canhoto dc
    JOIN usuario_transportadora u ON dc.idusuario_transportadora = u.idusuario_transportadora
    WHERE dc.chave_acesso = :chave_acesso;
    ';
    $statement = $pdo->prepare($query);
    $statement->execute(['chave_acesso' => $chave]);
    $document = $statement->fetch(\PDO::FETCH_ASSOC);

    if (!$document) {
      http_response_code(404);
      throw new \Exception('Document not found', 404);
    }

    http_response_code(200);
    echo json_encode($document, JSON_PRETTY_PRINT);
  } catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => $e->getMessage()]);
  }

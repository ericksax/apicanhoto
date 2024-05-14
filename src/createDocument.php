<?php
// Configuração das cabeçalhos CORS
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

try { 
    $query = 'INSERT INTO documento_canhoto (
        idusuario_transportadora, 
        numero_documento, 
        chave_acesso, 
        nome_destinatario, 
        cpfcnpj_destinatario, 
        end_logradouro, 
        end_numero, 
        end_complemento, 
        end_cidade, 
        end_bairro, 
        end_cep, 
        recebedor_nome, 
        recebedor_documento, 
        foto_canhoto, 
        tel_destinatario, 
        uf_destinatario
    ) 
    VALUES (
        :idusuario_transportadora, 
        :numero_documento, 
        :chave_acesso, 
        :nome_destinatario, 
        :cpfcnpj_destinatario, 
        :end_logradouro, 
        :end_numero, 
        :end_complemento, 
        :end_cidade, 
        :end_bairro, 
        :end_cep, 
        :recebedor_nome, 
        :recebedor_documento, 
        :foto_canhoto, 
        :tel_destinatario, 
        :uf_destinatario
    );';
    
    $statement = $pdo->prepare($query);
    $statement->execute($data);

    http_response_code(201);
    print( json_encode( array('message' => 'Documento criado com sucesso!') ) );
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
}
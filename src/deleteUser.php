<?php
// Configuração dos cabeçalhos CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: X-AMZ-META-TOKEN-ID, X-AMZ-META-TOKEN-SECRET, Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once("../../page/conexao/conexao.class.php");
   
try {
    $pdo = new Conexao();
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id'])) {
        $id = $data['id'];

        $query = 'SELECT idusuario_transportadora, nome_completo, usuario, telefone, admin, cpfcnpj, data_cadastro, data_atualizacao 
                  FROM usuario_transportadora 
                  WHERE idusuario_transportadora = :idusuario_transportadora';
        $statement = $pdo->prepare($query);
        $statement->execute(['idusuario_transportadora' => $id]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['message' => 'User does not exist']);
            exit();
        }

        $query = 'DELETE FROM usuario_transportadora WHERE idusuario_transportadora = :idusuario_transportadora';
        $statement = $pdo->prepare($query);
        $statement->execute(['idusuario_transportadora' => $id]);

        http_response_code(200);
        echo json_encode(['message' => 'Usuário excluído com sucesso']);
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'ID not provided']);
    }
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Erro ao buscar usuário: ' . $e->getMessage()]);
}

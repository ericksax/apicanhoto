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

$objData = $_POST;
$image = $_FILES['file'];
$id = $objData['id'];

$pdo = new Conexao(); {
    try {
        $query = 'SELECT * FROM documento_canhoto WHERE iddocumento = :iddocumento';
        $statement = $pdo->prepare($query);
        $statement->execute(['iddocumento' => $id]);
        $document = $statement->fetch(\PDO::FETCH_ASSOC);

        $domain = 'http://ativa.nivel3ti.com.br:44472';
        $basePath = DIRECTORY_SEPARATOR . 'wms_ativa' . DIRECTORY_SEPARATOR . 'apiservice'
            . DIRECTORY_SEPARATOR . 'apicanhoto' . DIRECTORY_SEPARATOR . 'uploads'
            . DIRECTORY_SEPARATOR;
        // $basePath = 'http://localhost:8000' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $basePath = str_replace(DIRECTORY_SEPARATOR, '/', $basePath);

        if (!$document) {
            http_response_code(404);
            throw new \Exception("Document not found", 404);
        }

        if (!$objData) {
            echo json_encode($objData);
            http_response_code(400);
            throw new \Exception("Dados inválidos", 400);
        }
        
        $imagePath = __DIR__ . '\\..\\uploads\\' . $image['name'];

        $query = 'UPDATE documento_canhoto SET';
        $params = [];

        if (isset($objData['recebedor_nome'])) {
            $query .= ' recebedor_nome = :recebedor_nome,';
            $params['recebedor_nome'] = $objData['recebedor_nome'];
        }

        if (isset($objData['recebedor_documento'])) {
            $query .= ' recebedor_documento = :recebedor_documento,';
            $params['recebedor_documento'] = $objData['recebedor_documento'];
        }

        if (isset($image['name'])) {
            $query .= ' foto_canhoto = :foto_canhoto,';
            $params['foto_canhoto'] = $domain . $basePath . $image['name'];
        }

        $query .= ' data_atualizacao = :data_atualizacao WHERE iddocumento = :iddocumento';
        $params['iddocumento'] = $id;
        $params['data_atualizacao'] = date('Y-m-d H:i:s');

        // Remover a última vírgula, se houver
        $query = rtrim($query, ',');

        $statement = $pdo->prepare($query);
        $statement->execute($params);


        move_uploaded_file($image['tmp_name'], $imagePath);

        http_response_code(200);
        echo json_encode(['message' => 'Document updated successfully']);
    } catch (\Throwable $th) {
        http_response_code((int) $th->getCode());
        echo $th->getMessage();
    }
}

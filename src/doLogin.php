<?php

require_once '../../libs/JWT.php';

use Firebase\JWT\JWT;

// use Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-AMZ-META-TOKEN-ID, X-AMZ-META-TOKEN-SECRET');


include_once("../../page/conexao/conexao.class.php");
   
$pdo = new Conexao(); 

$data =  json_decode(file_get_contents("php://input", true), true);

$userLogin = filter_var($data['usuario'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$userPass = filter_var($data['senha'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$userLogin || !$userPass) {
    http_response_code(400);
    throw new \Exception('missing credentials', 400);
}

try {
    $query = "SELECT * FROM usuario_transportadora WHERE usuario = :usuario";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':usuario', $userLogin);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        throw new \Exception('User not found', 404);
    }

    if (!password_verify($userPass, $user->senha)) {
        http_response_code(401);
        throw new \Exception('Invalide credentials', 401);
    }

    $payload = [
        'user_id' => $user->idusuario_transportadora,
        "is_admin" => $user->admin,
        'exp' => time() + (60 * 60),
    ];

    $token = JWT::encode($payload, 's3cr3t', 'HS256');

    // Retornar o token para o usuário
    http_response_code(201);
    echo json_encode([
        'token' => $token,
        'user' => [
            'id' => $user->idusuario_transportadora,
            'name' => $user->nome_completo,
            'login' => $user->usuario
        ]
    ]);
} catch (\Exception $e) {
    http_response_code($e->getCode());
    echo json_encode(['message' => $e->getMessage()]);
}

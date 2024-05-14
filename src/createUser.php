<?php



header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-AMZ-META-TOKEN-ID, X-AMZ-META-TOKEN-SECRET');

include_once("../../page/conexao/conexao.class.php");

$data = json_decode(file_get_contents('php://input'), true);
   
$pdo = new Conexao(); 

$usuario = filter_var($data['usuario'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$nome_completo = filter_var($data['nome_completo'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$senha = filter_var($data['senha'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$cpfcnpj = filter_var($data['cpfcnpj'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$telefone = filter_var($data['telefone'], FILTER_SANITIZE_NUMBER_INT);

if (!isset($data['admin'])) {
    $admin = 0;
} else {
    $admin = filter_var($data['admin'], FILTER_SANITIZE_NUMBER_INT);
}

if (!$usuario || !$nome_completo || !$senha || !$telefone || !$cpfcnpj) {
    http_response_code(400); // Requisição inválida
    echo json_encode(['message' => 'Todos os campos são obrigatórios']);
    exit();
}

if (strlen($senha) < 8) {
    http_response_code(400); // Requisição inválida
    echo json_encode(['message' => 'A senha deve ter pelo menos 8 caracteres']);
    exit();
}

if (UserExists($usuario)) {
    http_response_code(409); // Conflito
    echo json_encode(['message' => 'Usuário já existe']);
    exit();
}

try {
    $encryptedPassword = password_hash($senha, PASSWORD_DEFAULT);

    $user = [
        'usuario' => $usuario,
        'nome_completo' => $nome_completo,
        'telefone' => $telefone,
        'cpfcnpj' => $cpfcnpj,
        'senha' => $encryptedPassword,
    ];


    if ($admin) {
        $user['admin'] = 1;
    }

    // Montar a query SQL com base no array $user
    $query = 'INSERT INTO usuario_transportadora (usuario, nome_completo, telefone, cpfcnpj, ';

    // Adicionar admin à query se estiver definido no array $user
    if (isset($user['admin'])) {
        $query .= 'admin, ';
    }

    $query .= 'senha) VALUES (:usuario, :nome_completo, :telefone, :cpfcnpj,';

    // Adicionar marcador de posição para admin se estiver definido no array $user
    if (isset($user['admin'])) {
        $query .= ':admin, ';
    }

    $query .= ':senha)';

    $statement = $pdo->prepare($query);
    $statement->execute($user);

    http_response_code(201);
    echo json_encode(['message' => 'Usuário criado com sucesso']);
    exit();
} catch (\PDOException $e) {
    http_response_code(500); // Erro interno do servidor
    echo json_encode(['message' => 'Erro no servidor: ' . $e->getMessage()]);
    exit();
}

function UserExists($usuario)
{
    $pdo = new Conexao();

    $query = 'SELECT * FROM usuario_transportadora WHERE usuario = :usuario';
    $statement = $pdo->prepare($query);
    $statement->execute(['usuario' => $usuario]);
    $user = $statement->fetch(\PDO::FETCH_ASSOC);
    return $user ? true : false;
}

<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
include '../config.php'; // aqui $pdo deve ser PDO

$response = ["sucesso" => false, "msg" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $response["msg"] = "Email e senha são obrigatórios.";
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id_pk, nome, email, tipo, senha FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) {
            unset($user['senha']); // remove a senha do JSON
            $response["sucesso"] = true;
            $response["msg"] = "Login realizado com sucesso!";
            $response["usuario"] = $user;
        } else {
            $response["msg"] = "Email ou senha incorretos.";
        }

    } catch (PDOException $e) {
        $response["msg"] = "Erro no banco de dados: " . $e->getMessage();
    }

    echo json_encode($response);
    exit;

} else {
    $response["msg"] = "Use o método POST.";
    echo json_encode($response);
}

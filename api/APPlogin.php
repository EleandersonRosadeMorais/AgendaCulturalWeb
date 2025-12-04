<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
include '../config.php';

$response = array("sucesso" => false, "msg" => "");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $response["msg"] = "Email e senha são obrigatórios";
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, nome, email, tipo, senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $response["msg"] = "Email ou senha incorretos";
            $stmt->close();
            echo json_encode($response);
            exit;
        }

        $usuario = $result->fetch_assoc();

        if (!password_verify($senha, $usuario['senha'])) {
            $response["msg"] = "Email ou senha incorretos";
            $stmt->close();
            echo json_encode($response);
            exit;
        }

        unset($usuario['senha']);

        $response["sucesso"] = true;
        $response["msg"] = "Login realizado com sucesso!";
        $response["usuario"] = $usuario;
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response["msg"] = "Erro no sistema: " . $e->getMessage();
    }
    
} else {
    $response["msg"] = "Método inválido. Use POST.";
}

if (isset($conn)) {
    $conn->close();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
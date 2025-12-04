<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

include '../config.php'; // define $pdo

$response = ["sucesso" => false, "msg" => ""];

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_pk = $_GET['id'] ?? null; // futuro para edição

    // Dados do POST
    $nome           = $_POST['nome'] ?? '';
    $dataNascimento = $_POST['dataNascimento'] ?? null;
    $cpf            = $_POST['cpf'] ?? '';
    $email          = $_POST['email'] ?? '';
    $tipo           = $_POST['tipo'] ?? 'comum';
    $senha          = $_POST['senha'] ?? '';

    if (empty($nome) || empty($cpf) || empty($email) || empty($senha)) {
        $response["msg"] = "Campos obrigatórios não enviados.";
        echo json_encode($response);
        exit;
    }

    try {
        if (!$id_pk) {
            // Verifica se CPF ou email já existem
            $stmt = $pdo->prepare("SELECT id_pk FROM usuario WHERE cpf = ? OR email = ?");
            $stmt->execute([$cpf, $email]);

            if ($stmt->rowCount() > 0) {
                $response["msg"] = "CPF ou Email já cadastrado.";
            } else {
                // Hash da senha
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

                // Inserir novo usuário
                $stmt = $pdo->prepare("
                    INSERT INTO usuario (nome, dataNascimento, cpf, email, tipo, senha)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nome, $dataNascimento, $cpf, $email, $tipo, $senhaHash]);

                $response["sucesso"] = true;
                $response["msg"] = "Usuário cadastrado com sucesso!";
                $response["id_gerado"] = $pdo->lastInsertId();
            }
        } else {
            $response["msg"] = "Função de edição ainda não implementada.";
        }
    } catch (PDOException $e) {
        $response["msg"] = "Erro no banco de dados: " . $e->getMessage();
    }

} else {
    $response["msg"] = "Use o método POST.";
}

echo json_encode($response);

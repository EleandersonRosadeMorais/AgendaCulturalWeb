<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

include '../config.php';
session_start();

$response = ["sucesso" => false, "msg" => ""];

$id_pk = $_GET['id'] ?? null;

if (!$id_pk) {
    echo json_encode(["sucesso" => false, "msg" => "ID do usuário não informado na URL."]);
    exit;
}

// GET → Carregar perfil
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT id_pk, nome, dataNascimento, cpf, email, tipo FROM usuario WHERE id_pk = ?");
        $stmt->execute([$id_pk]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $response["sucesso"] = true;
            $response["dados"] = $usuario;
        } else {
            $response["msg"] = "Usuário não encontrado.";
        }
    } catch (PDOException $e) {
        $response["msg"] = $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// POST → Editar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? null;
    $dataNascimento = $_POST['dataNascimento'] ?? null;
    $cpf = $_POST['cpf'] ?? null;
    $email = $_POST['email'] ?? null;
    $tipo = $_POST['tipo'] ?? 'comum';
    $senha = $_POST['senha'] ?? null;

    if (!$nome || !$cpf || !$email) {
        echo json_encode(["sucesso" => false, "msg" => "Nome, CPF e Email são obrigatórios."]);
        exit;
    }

    try {
        if ($senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuario SET nome=?, dataNascimento=?, cpf=?, email=?, tipo=?, senha=? WHERE id_pk=?");
            $stmt->execute([$nome, $dataNascimento, $cpf, $email, $tipo, $senhaHash, $id_pk]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuario SET nome=?, dataNascimento=?, cpf=?, email=?, tipo=? WHERE id_pk=?");
            $stmt->execute([$nome, $dataNascimento, $cpf, $email, $tipo, $id_pk]);
        }

        $response["sucesso"] = true;
        $response["msg"] = "Perfil atualizado com sucesso!";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $response["msg"] = "CPF ou Email já cadastrado.";
        } else {
            $response["msg"] = $e->getMessage();
        }
    }

    echo json_encode($response);
    exit;
}

// Método inválido
$response["msg"] = "Método inválido. Use GET ou POST.";
echo json_encode($response);

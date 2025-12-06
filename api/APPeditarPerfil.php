<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

include '../config.php';

$id_pk = $_GET['id'] ?? null;

// GET → Carregar perfil
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$id_pk) {
        echo json_encode(["erro" => "ID não informado."]);
        exit;
    }
    
    try {
        // SIMPLES: Testar se tem problema com charset
        $teste = $pdo->query("SELECT 'teste' as teste")->fetch();
        
        // Se tiver \x ou \z no teste, usar CONVERT
        if (strpos($teste['teste'], '\\x') !== false || strpos($teste['teste'], '\\z') !== false) {
            $query = "SELECT id_pk, CONVERT(nome USING utf8mb4) as nome, dataNascimento, CONVERT(cpf USING utf8mb4) as cpf, CONVERT(email USING utf8mb4) as email, CONVERT(tipo USING utf8mb4) as tipo FROM usuario WHERE id_pk = ?";
        } else {
            $query = "SELECT id_pk, nome, dataNascimento, cpf, email, tipo FROM usuario WHERE id_pk = ?";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_pk]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Se tiver \x ou \z nos dados, corrigir
            foreach ($usuario as $key => $value) {
                if (is_string($value) && (strpos($value, '\\x') !== false || strpos($value, '\\z') !== false)) {
                    $usuario[$key] = stripcslashes($value);
                }
            }
            
            echo json_encode($usuario, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["erro" => "Usuário não encontrado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["erro" => "Erro ao buscar usuário."]);
    }
    exit;
}

// POST → Editar perfil (mantém igual ao seu original)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ["sucesso" => false, "msg" => ""];
    
    if (!$id_pk) {
        $response["msg"] = "ID do usuário não informado.";
        echo json_encode($response);
        exit;
    }
    
    $nome = $_POST['nome'] ?? null;
    $dataNascimento = $_POST['dataNascimento'] ?? null;
    $cpf = $_POST['cpf'] ?? null;
    $email = $_POST['email'] ?? null;
    $tipo = $_POST['tipo'] ?? 'comum';
    $senha = $_POST['senha'] ?? null;

    if (!$nome || !$cpf || !$email) {
        $response["msg"] = "Nome, CPF e Email são obrigatórios.";
        echo json_encode($response);
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
echo json_encode(["erro" => "Método inválido. Use GET ou POST."]);
?>
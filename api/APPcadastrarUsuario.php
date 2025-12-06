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
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        // 🔥 SIMPLES: Testar se tem problema com charset
        $teste = $pdo->query("SELECT 'teste' as teste")->fetch();
        $tem_problema = (strpos($teste['teste'] ?? '', '\\x') !== false || 
                        strpos($teste['teste'] ?? '', '\\z') !== false);
        
        if (!$id_pk) {
            // CADASTRO NOVO
            
            if ($tem_problema) {
                // MODO SERVIDOR: Verificar com CONVERT
                $stmt = $pdo->prepare("
                    SELECT id_pk 
                    FROM usuario 
                    WHERE CONVERT(cpf USING utf8mb4) = CONVERT(? USING utf8mb4) 
                    OR CONVERT(email USING utf8mb4) = CONVERT(? USING utf8mb4)
                ");
            } else {
                // MODO LOCALHOST: Query normal
                $stmt = $pdo->prepare("SELECT id_pk FROM usuario WHERE cpf = ? OR email = ?");
            }
            
            $stmt->execute([$cpf, $email]);

            if ($stmt->rowCount() > 0) {
                $response["msg"] = "CPF ou Email já cadastrado.";
            } else {
                // Hash da senha
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                
                // 🔥 Se for servidor, corrigir encoding dos dados
                if ($tem_problema) {
                    $nome = mb_convert_encoding($nome, 'UTF-8', 'auto');
                    $cpf = mb_convert_encoding($cpf, 'UTF-8', 'auto');
                    $email = mb_convert_encoding($email, 'UTF-8', 'auto');
                    $tipo = mb_convert_encoding($tipo, 'UTF-8', 'auto');
                }

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
        if ($e->getCode() == 23000) {
            $response["msg"] = "CPF ou Email já cadastrado.";
        } else {
            $response["msg"] = "Erro no cadastro.";
        }
    }

} else {
    $response["msg"] = "Use o método POST.";
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
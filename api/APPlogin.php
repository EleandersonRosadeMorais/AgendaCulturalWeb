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
        // Testar se tem problema com charset
        $teste = $pdo->query("SELECT 'teste' as teste")->fetch();
        $tem_problema = (strpos($teste['teste'] ?? '', '\\x') !== false || 
                        strpos($teste['teste'] ?? '', '\\z') !== false);
        
        if ($tem_problema) {
            // MODO SERVIDOR: Usar CONVERT para campos de texto, mas NÃO para senha
            $query = "SELECT 
                id_pk, 
                CONVERT(nome USING utf8mb4) as nome, 
                CONVERT(email USING utf8mb4) as email, 
                CONVERT(tipo USING utf8mb4) as tipo, 
                senha 
                FROM usuario 
                WHERE email = ?";
        } else {
            // MODO LOCALHOST: Query normal
            $query = "SELECT id_pk, nome, email, tipo, senha FROM usuario WHERE email = ?";
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 🔥 CORREÇÃO: Aplicar stripclashes apenas nos campos de texto, NÃO na senha
            $camposParaCorrigir = ['nome', 'email', 'tipo'];
            foreach ($camposParaCorrigir as $campo) {
                if (isset($user[$campo]) && is_string($user[$campo]) && 
                    (strpos($user[$campo], '\\x') !== false || strpos($user[$campo], '\\z') !== false)) {
                    $user[$campo] = stripcslashes($user[$campo]);
                }
            }
            
            // Verificar senha com password_verify
            if (password_verify($senha, $user['senha'])) {
                unset($user['senha']); // remove a senha do JSON
                $response["sucesso"] = true;
                $response["msg"] = "Login realizado com sucesso!";
                $response["usuario"] = $user;
            } else {
                $response["msg"] = "Email ou senha incorretos.";
            }
        } else {
            $response["msg"] = "Email ou senha incorretos.";
        }

    } catch (PDOException $e) {
        $response["msg"] = "Erro no banco de dados: " . $e->getMessage();
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} else {
    $response["msg"] = "Use o método POST.";
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
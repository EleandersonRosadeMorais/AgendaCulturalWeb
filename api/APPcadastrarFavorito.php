<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

include __DIR__ . '/../config.php';

$response = ["sucesso" => false, "msg" => ""];

// Aceita GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $usuario_fk = $_GET['usuario_fk'] ?? null;
    $evento_fk  = $_GET['evento_fk'] ?? null;

    if (empty($usuario_fk) || empty($evento_fk)) {
        $response["msg"] = "Parâmetros usuario_fk e evento_fk são obrigatórios.";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {
        // 🔥 SIMPLES: Testar se tem problema com charset
        $teste = $pdo->query("SELECT 'teste' as teste")->fetch();
        $tem_problema = (strpos($teste['teste'] ?? '', '\\x') !== false || 
                        strpos($teste['teste'] ?? '', '\\z') !== false);
        
        if ($tem_problema) {
            // MODO SERVIDOR: Usar CONVERT na verificação
            $stmt = $pdo->prepare("
                SELECT id_pk 
                FROM favorito 
                WHERE CONVERT(usuario_fk USING utf8mb4) = CONVERT(? USING utf8mb4) 
                AND CONVERT(evento_fk USING utf8mb4) = CONVERT(? USING utf8mb4)
            ");
        } else {
            // MODO LOCALHOST: Query normal
            $stmt = $pdo->prepare("SELECT id_pk FROM favorito WHERE usuario_fk = ? AND evento_fk = ?");
        }
        
        $stmt->execute([$usuario_fk, $evento_fk]);

        if ($stmt->rowCount() > 0) {
            $response["msg"] = "Este evento já está favoritado.";
        } else {
            // Insere novo favorito
            $stmt = $pdo->prepare("INSERT INTO favorito (usuario_fk, evento_fk, dataCriacao) VALUES (?, ?, NOW())");
            $stmt->execute([$usuario_fk, $evento_fk]);

            $response["sucesso"] = true;
            $response["msg"] = "Favorito adicionado com sucesso!";
            $response["id"] = $pdo->lastInsertId();
        }
    } catch (PDOException $e) {
        $response["msg"] = "Erro ao adicionar favorito.";
    }

} else {
    $response["msg"] = "Método inválido. Use GET.";
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
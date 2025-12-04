<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

// Inclui seu config.php que define $pdo
include __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ["sucesso" => false, "msg" => ""];

// Aceita GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $usuario_fk = $_GET['usuario_fk'] ?? null;
    $evento_fk  = $_GET['evento_fk'] ?? null;

    if (empty($usuario_fk) || empty($evento_fk)) {
        $response["msg"] = "Parâmetros usuario_fk e evento_fk são obrigatórios.";
        echo json_encode($response);
        exit;
    }

    try {
        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT id_pk FROM favorito WHERE usuario_fk = ? AND evento_fk = ?");
        $stmt->execute([$usuario_fk, $evento_fk]);

        if ($stmt->rowCount() > 0) {
            $response["msg"] = "Este evento já está favoritado.";
        } else {
            // Insere novo favorito
            $stmt = $pdo->prepare("INSERT INTO favorito (usuario_fk, evento_fk, dataCriacao) VALUES (?, ?, NOW())");
            $stmt->execute([$usuario_fk, $evento_fk]);

            $response["sucesso"] = true;
            $response["msg"] = "Favorito adicionado com sucesso!";
        }
    } catch (PDOException $e) {
        $response["msg"] = "Erro no banco de dados: " . $e->getMessage();
    }

} else {
    $response["msg"] = "Método inválido. Use GET.";
}

echo json_encode($response);
?>

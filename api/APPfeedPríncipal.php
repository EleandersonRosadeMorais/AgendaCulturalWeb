<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
include '../config.php'; // aqui existe $pdo

$response = array("sucesso" => false);

// 🔹 ID é obrigatório
if (!isset($_GET["id"])) {
    $response["msg"] = "ID do evento não informado na URL.";
    echo json_encode($response);
    exit;
}

$id = $_GET["id"];

// 🔹 Buscar apenas o evento solicitado
$stmt = $pdo->prepare("
    SELECT e.id_pk, e.titulo, e.data, e.hora, e.local, e.descricao, 
           e.tipoEvento, e.responsavel, e.banner,
           c.titulo AS categoria,
           u.nome AS criador
    FROM evento e
    LEFT JOIN categoria c ON e.categoria_fk = c.id_pk
    LEFT JOIN usuario u ON e.usuario_fk = u.id_pk
    WHERE e.id_pk = ?
    LIMIT 1
");

$stmt->execute([$id]);
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

if ($evento) {
    $response["sucesso"] = true;
    $response["evento"] = $evento; // retorna apenas 1 evento
} else {
    $response["msg"] = "Evento não encontrado.";
}

echo json_encode($response);
?>

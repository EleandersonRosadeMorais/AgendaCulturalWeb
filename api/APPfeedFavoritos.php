<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
include '../config.php'; // aqui tem $pdo

$response = array("sucesso" => false);

// Verifica parâmetros obrigatórios
if (!isset($_GET["usuario_fk"]) || !isset($_GET["evento_fk"])) {
    $response["msg"] = "Parâmetros usuario_fk e evento_fk são obrigatórios.";
    echo json_encode($response);
    exit;
}

$usuario_fk = $_GET["usuario_fk"];
$evento_fk  = $_GET["evento_fk"];

// PDO prepare
$stmt = $pdo->prepare("
    SELECT 
        e.id_pk, 
        e.titulo, 
        e.data, 
        e.hora, 
        e.local, 
        e.descricao,
        e.tipoEvento,
        e.responsavel,
        e.banner,
        c.titulo AS categoria,
        u.nome AS criador,
        f.dataCriacao AS favoritadoEm
    FROM favorito f
    INNER JOIN evento e ON f.evento_fk = e.id_pk
    LEFT JOIN categoria c ON e.categoria_fk = c.id_pk
    LEFT JOIN usuario u ON e.usuario_fk = u.id_pk
    WHERE f.usuario_fk = ? AND e.id_pk = ?
    LIMIT 1
");

$stmt->execute([$usuario_fk, $evento_fk]);
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

if ($evento) {
    $response["sucesso"] = true;
    $response["favorito"] = $evento;
} else {
    $response["msg"] = "Nenhum evento favorito encontrado para este ID.";
}

echo json_encode($response);
?>

<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
include '../config.php';

$response = array("sucesso" => false, "msg" => "", "favoritos" => array());

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $usuario_id = $_GET['usuario_id'] ?? '';

    if (empty($usuario_id)) {
        $response["msg"] = "ID do usuário é obrigatório";
        echo json_encode($response);
        exit;
    }

    try {
        $sql = "SELECT 
                    f.id_pk as favorito_id,
                    f.data_adicao,
                    e.id_pk as evento_id,
                    e.titulo,
                    e.data,
                    e.hora,
                    e.local,
                    e.descricao,
                    e.tipoEvento,
                    e.responsavel,
                    e.banner,
                    e.categoria_fk as categoria_id
                FROM favoritos f
                INNER JOIN evento e ON f.evento_fk = e.id_pk
                WHERE f.usuario_fk = ?
                ORDER BY f.data_adicao DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $favoritos = array();
        
        while ($row = $result->fetch_assoc()) {
            $favoritos[] = array(
                "favorito_id" => $row['favorito_id'],
                "data_adicao" => $row['data_adicao'],
                "evento_id" => $row['evento_id'],
                "titulo" => $row['titulo'],
                "data" => $row['data'],
                "hora" => $row['hora'],
                "local" => $row['local'],
                "descricao" => $row['descricao'],
                "tipoEvento" => $row['tipoEvento'],
                "responsavel" => $row['responsavel'],
                "banner" => $row['banner'],
                "categoria_id" => $row['categoria_id']
            );
        }
        
        $response["sucesso"] = true;
        $response["msg"] = "Favoritos carregados com sucesso";
        $response["favoritos"] = $favoritos;
        $response["total"] = count($favoritos);
        
        $stmt->close();
        
    } catch (Exception $e) {
        $response["msg"] = "Erro no sistema: " . $e->getMessage();
    }
    
} else {
    $response["msg"] = "Método inválido. Use GET.";
}

if (isset($conn)) {
    $conn->close();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
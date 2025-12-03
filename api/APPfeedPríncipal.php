<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
include '../config.php';

$response = array("sucesso" => false, "msg" => "", "eventos" => array());

try {
    $sql = "SELECT 
                e.id_pk as id,
                e.titulo,
                e.data,
                e.hora,
                e.local,
                e.descricao,
                e.tipoEvento,
                e.responsavel,
                e.banner,
                e.categoria_fk as categoria_id,
                e.usuario_fk as usuario_id
            FROM evento e
            ORDER BY e.data DESC, e.hora DESC";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $eventos = array();
        
        while ($row = $result->fetch_assoc()) {
            $eventos[] = array(
                "id" => $row['id'],
                "titulo" => $row['titulo'],
                "data" => $row['data'],
                "hora" => $row['hora'],
                "local" => $row['local'],
                "descricao" => $row['descricao'],
                "tipoEvento" => $row['tipoEvento'],
                "responsavel" => $row['responsavel'],
                "banner" => $row['banner'],
                "categoria_id" => $row['categoria_id'],
                "usuario_id" => $row['usuario_id']
            );
        }
        
        $response["sucesso"] = true;
        $response["msg"] = "Eventos carregados com sucesso";
        $response["eventos"] = $eventos;
        $response["total"] = count($eventos);
        
        $result->free();
    } else {
        $response["msg"] = "Erro ao buscar eventos: " . $conn->error;
    }
    
} catch (Exception $e) {
    $response["msg"] = "Erro no sistema: " . $e->getMessage();
}

if (isset($conn)) {
    $conn->close();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
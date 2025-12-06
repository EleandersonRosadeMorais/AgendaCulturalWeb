<?php
// api/evento.php - VERSÃO COMPATÍVEL
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Permitir requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir config
include '../config.php';

// Função para retornar apenas os dados (NOVA FUNÇÃO)
function returnEventData($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// 🔹 ID é obrigatório
if (!isset($_GET["id"])) {
    http_response_code(400);
    echo json_encode(["erro" => "ID do evento não informado na URL."], JSON_UNESCAPED_UNICODE);
    exit();
}

$id = intval($_GET["id"]);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["erro" => "ID do evento inválido."], JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // 🔹 DETECTAR SE ESTÁ NO SERVIDOR (com problema de charset)
    $teste = $pdo->query("SELECT 'teste' as teste")->fetch(PDO::FETCH_ASSOC);
    $problema_charset = (strpos($teste['teste'] ?? '', '\\x') !== false || 
                        strpos($teste['teste'] ?? '', '\\z') !== false);
    
    if ($problema_charset) {
        // MODO SERVIDOR: Usar CONVERT em todos os campos de texto
        $query = "
            SELECT 
                e.id_pk,
                CONVERT(e.titulo USING utf8mb4) as titulo,
                e.data,
                e.hora,
                CONVERT(e.local USING utf8mb4) as local,
                CONVERT(e.descricao USING utf8mb4) as descricao,
                CONVERT(e.tipoEvento USING utf8mb4) as tipoEvento,
                CONVERT(e.responsavel USING utf8mb4) as responsavel,
                e.banner,
                CONVERT(c.titulo USING utf8mb4) AS categoria,
                CONVERT(u.nome USING utf8mb4) AS criador
            FROM evento e
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk
            LEFT JOIN usuario u ON e.usuario_fk = u.id_pk
            WHERE e.id_pk = ?
            LIMIT 1
        ";
    } else {
        // MODO LOCALHOST: Query normal
        $query = "
            SELECT e.id_pk, e.titulo, e.data, e.hora, e.local, e.descricao, 
                   e.tipoEvento, e.responsavel, e.banner,
                   c.titulo AS categoria,
                   u.nome AS criador
            FROM evento e
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk
            LEFT JOIN usuario u ON e.usuario_fk = u.id_pk
            WHERE e.id_pk = ?
            LIMIT 1
        ";
    }
    
    // Preparar e executar query
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($evento) {
        // 🔹 FUNÇÃO PARA CORRIGIR DADOS
        function corrigirDados($dado) {
            if (is_array($dado)) {
                foreach ($dado as $key => $value) {
                    $dado[$key] = corrigirDados($value);
                }
                return $dado;
            } elseif (is_string($dado)) {
                // Se parece com dados binários (\x ou \z)
                if (strpos($dado, '\\x') !== false || strpos($dado, '\\z') !== false) {
                    // Tentar decodificar
                    $dado = stripcslashes($dado);
                    
                    // Se ainda tiver problemas
                    if (strpos($dado, '\\x') !== false || strpos($dado, '\\z') !== false) {
                        $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'];
                        foreach ($encodings as $enc) {
                            $converted = @mb_convert_encoding($dado, 'UTF-8', $enc);
                            if ($converted && strpos($converted, '\\x') === false && strpos($converted, '\\z') === false) {
                                return $converted;
                            }
                        }
                    }
                }
                return $dado;
            }
            return $dado;
        }
        
        // 🔹 APLICAR CORREÇÃO AOS DADOS
        $evento = corrigirDados($evento);
        
        // 🔹 ADICIONAR URL COMPLETA PARA BANNER
        if (!empty($evento['banner'])) {
            // Verificar se já é uma URL completa
            if (filter_var($evento['banner'], FILTER_VALIDATE_URL)) {
                $evento['banner_url'] = $evento['banner'];
            } else {
                // Construir URL completa
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
                $host = $_SERVER['HTTP_HOST'];
                // Remover barra inicial se houver
                $bannerPath = ltrim($evento['banner'], '/');
                $evento['banner_url'] = "$protocol://$host/uploads/$bannerPath";
            }
        }
        
        // 🔹 RETORNAR APENAS OS DADOS DO EVENTO
        returnEventData($evento);
        
    } else {
        http_response_code(404);
        echo json_encode(["erro" => "Evento não encontrado."], JSON_UNESCAPED_UNICODE);
        exit();
    }
    
} catch (PDOException $e) {
    // Log do erro
    error_log("Erro PDO ao buscar evento ID $id: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao buscar evento."], JSON_UNESCAPED_UNICODE);
    exit();
    
} catch (Exception $e) {
    error_log("Erro geral ao buscar evento ID $id: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(["erro" => "Erro interno do servidor."], JSON_UNESCAPED_UNICODE);
    exit();
}
?>
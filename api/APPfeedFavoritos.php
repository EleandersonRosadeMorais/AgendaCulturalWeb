<?php
// api/verificar_favorito.php - RETORNA APENAS O OBJETO
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

// Função para corrigir dados (mantém interna)
function corrigirDados($dado) {
    if (is_array($dado)) {
        foreach ($dado as $key => $value) {
            $dado[$key] = corrigirDados($value);
        }
        return $dado;
    } elseif (is_string($dado)) {
        if (strpos($dado, '\\x') !== false || strpos($dado, '\\z') !== false) {
            $dado = stripcslashes($dado);
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

// Função para enviar resposta de erro
function enviarErro($mensagem, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode(["erro" => $mensagem], JSON_UNESCAPED_UNICODE);
    exit();
}

// Verifica parâmetros obrigatórios
if (!isset($_GET["usuario_fk"]) || !isset($_GET["evento_fk"])) {
    enviarErro("Parâmetros usuario_fk e evento_fk são obrigatórios.", 400);
}

$usuario_fk = intval($_GET["usuario_fk"]);
$evento_fk  = intval($_GET["evento_fk"]);

if ($usuario_fk <= 0 || $evento_fk <= 0) {
    enviarErro("Parâmetros inválidos.", 400);
}

try {
    // 🔹 DETECTAR SE ESTÁ NO SERVIDOR
    $teste = $pdo->query("SELECT 'teste' as teste")->fetch(PDO::FETCH_ASSOC);
    $problema_charset = (strpos($teste['teste'] ?? '', '\\x') !== false || 
                        strpos($teste['teste'] ?? '', '\\z') !== false);
    
    if ($problema_charset) {
        // MODO SERVIDOR: Usar CONVERT
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
                CONVERT(u.nome USING utf8mb4) AS criador,
                f.dataCriacao AS favoritadoEm,
                f.usuario_fk,
                f.evento_fk
            FROM favorito f
            INNER JOIN evento e ON f.evento_fk = e.id_pk
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk
            LEFT JOIN usuario u ON e.usuario_fk = u.id_pk
            WHERE f.usuario_fk = ? AND f.evento_fk = ?  -- ⚠️ CORREÇÃO AQUI: f.evento_fk em vez de e.id_pk
            LIMIT 1
        ";
    } else {
        // MODO LOCALHOST: Query normal
        $query = "
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
                f.dataCriacao AS favoritadoEm,
                f.usuario_fk,
                f.evento_fk
            FROM favorito f
            INNER JOIN evento e ON f.evento_fk = e.id_pk
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk
            LEFT JOIN usuario u ON e.usuario_fk = u.id_pk
            WHERE f.usuario_fk = ? AND f.evento_fk = ?  -- ⚠️ CORREÇÃO AQUI: f.evento_fk em vez de e.id_pk
            LIMIT 1
        ";
    }
    
    // Preparar e executar query
    $stmt = $pdo->prepare($query);
    $stmt->execute([$usuario_fk, $evento_fk]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($evento) {
        // 🔹 CORRIGIR DADOS
        $evento = corrigirDados($evento);
        
        // 🔹 CORRIGIR URL DO BANNER
        if (!empty($evento['banner'])) {
            // Verificar se já é uma URL completa (começa com http:// ou https://)
            if (strpos($evento['banner'], 'http://') === 0 || strpos($evento['banner'], 'https://') === 0) {
                // Já é URL completa, usar diretamente
                $evento['banner_url'] = $evento['banner'];
            } else {
                // É apenas nome do arquivo, montar URL completa
                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                            . "://$_SERVER[HTTP_HOST]";
                $evento['banner_url'] = $base_url . "/uploads/" . $evento['banner'];
            }
        }
        
        // 🔹 FORMATAR DATA DO FAVORITO
        if (!empty($evento['favoritadoEm'])) {
            $evento['favoritadoEm_formatado'] = date('d/m/Y H:i', strtotime($evento['favoritadoEm']));
        }
        
        // 🔹 AGORA RETORNA APENAS O OBJETO DO EVENTO DIRETAMENTE
        echo json_encode($evento, JSON_UNESCAPED_UNICODE);
        
    } else {
        // Se não encontrou, retorna null (ou pode retornar objeto vazio)
        echo json_encode(null);
    }
    
} catch (PDOException $e) {
    // Log do erro
    error_log("Erro PDO ao verificar favorito: " . $e->getMessage());
    
    // Para debugging, retorna o erro detalhado
    if (strpos($e->getMessage(), 'favorito') !== false) {
        // Verificar se a tabela favorito existe
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'favorito'")->fetch();
            if (!$check) {
                enviarErro("Tabela 'favorito' não existe no banco de dados.", 500);
            }
        } catch (Exception $ex) {
            enviarErro("Erro ao verificar tabela: " . $ex->getMessage(), 500);
        }
    }
    
    enviarErro("Erro ao buscar favorito: " . $e->getMessage(), 500);
    
} catch (Exception $e) {
    error_log("Erro geral ao verificar favorito: " . $e->getMessage());
    enviarErro("Erro interno do servidor.", 500);
}
?>
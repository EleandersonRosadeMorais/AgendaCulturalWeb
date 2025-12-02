<?php
// Configurações do MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'bdAgendaCultural');
define('DB_USER', 'root');
define('DB_PASS', '');

// Conexão com o MySQL
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// ============ FUNÇÕES DO SISTEMA ============

// Função para verificar login
function verificarLogin($email, $senha)
{
    global $pdo;
    // ... (código existente)
}

// Função para cadastrar usuário
function cadastrarUsuario($dados)
{
    global $pdo;
    // ... (código existente)
}

// Função para buscar eventos futuros
function getEventosFuturos()
{
    global $pdo;
    // ... (código existente)
}

// Função para buscar eventos passados
function getEventosPassados()
{
    global $pdo;
    // ... (código existente)
}

// Função para verificar se é admin
if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return ($_SESSION['usuario']['tipo'] ?? '') === 'admin';
    }
}

// Função para obter usuário atual
function getUsuarioAtual()
{
    return $_SESSION['usuario'] ?? null;
}

// Função para adicionar favorito
function adicionarFavorito($usuarioId, $eventoId)
{
    global $pdo;
    // ... (código existente)
}

// Função para remover favorito
function removerFavorito($usuarioId, $eventoId)
{
    global $pdo;
    // ... (código existente)
}

// Função para buscar favoritos do usuário
function getEventosFavoritos($usuarioId)
{
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, c.titulo as categoria_titulo 
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            INNER JOIN favorito f ON e.id_pk = f.evento_fk 
            WHERE f.usuario_fk = ? 
            ORDER BY e.data, e.hora
        ");
        $stmt->execute([$usuarioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // SEMPRE retornar array, mesmo que vazio
        return is_array($resultados) ? $resultados : [];
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar favoritos: " . $e->getMessage());
        return []; // Retornar array vazio em caso de erro
    }
}

// Funções auxiliares para cores e ícones
function getCorPorTipo($tipo)
{
    $cores = [
        'Música' => '#FF6B6B',
        'Educação' => '#4ECDC4',
        'Cultura' => '#45B7D1',
        'Esportes' => '#96CEB4',
        'Feira' => '#FFA726',
        'teatro' => '#AB47BC',
        'workshop' => '#26A69A'
    ];
    return $cores[$tipo] ?? '#02416D';
}

function getIconePorTipo($tipo)
{
    $icones = [
        'Música' => '<i class="fas fa-music"></i>',
        'Educação' => '<i class="fas fa-graduation-cap"></i>',
        'Cultura' => '<i class="fas fa-landmark"></i>',
        'Esportes' => '<i class="fas fa-running"></i>',
        'Feira' => '<i class="fas fa-store"></i>',
        'teatro' => '<i class="fas fa-theater-masks"></i>',
        'workshop' => '<i class="fas fa-laptop-code"></i>'
    ];
    return $icones[$tipo] ?? '<i class="fas fa-calendar-alt"></i>';
}
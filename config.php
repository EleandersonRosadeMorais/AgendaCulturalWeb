<?php
// Configurações do MySQL
define('DB_HOST', 'localhost');
define('DB_NAME', 'bdAgendaCultural');
define('DB_USER', 'root'); // Altere se necessário
define('DB_PASS', ''); // Altere se necessário

// Conexão com o MySQL
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Função para verificar login
function verificarLogin($email, $senha) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario'] = [
                'id' => $usuario['id_pk'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'tipo' => $usuario['tipo'],
                'dataNasc' => $usuario['dataNascimento'],
                'cpf' => $usuario['cpf']
            ];
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        return false;
    }
}

// Função para cadastrar usuário
function cadastrarUsuario($dados) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO usuario (nome, dataNascimento, cpf, email, tipo, senha) VALUES (?, ?, ?, ?, ?, ?)");
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $dados['nome'],
            $dados['dataNasc'],
            $dados['cpf'],
            $dados['email'],
            $dados['tipo'] ?? 'comum',
            $senhaHash
        ]);
    } catch(PDOException $e) {
        error_log("Erro no cadastro: " . $e->getMessage());
        return false;
    }
}

// Função para buscar eventos futuros
function getEventosFuturos() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, c.titulo as categoria_titulo 
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            WHERE e.data >= CURDATE() 
            ORDER BY e.data, e.hora
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar eventos futuros: " . $e->getMessage());
        return [];
    }
}

// Função para buscar eventos passados
function getEventosPassados() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, c.titulo as categoria_titulo 
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            WHERE e.data < CURDATE() 
            ORDER BY e.data DESC, e.hora DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar eventos passados: " . $e->getMessage());
        return [];
    }
}

// Função para adicionar favorito
function adicionarFavorito($usuarioId, $eventoId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO favorito (usuario_fk, evento_fk) VALUES (?, ?)");
        return $stmt->execute([$usuarioId, $eventoId]);
    } catch(PDOException $e) {
        error_log("Erro ao adicionar favorito: " . $e->getMessage());
        return false;
    }
}

// Função para remover favorito
function removerFavorito($usuarioId, $eventoId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM favorito WHERE usuario_fk = ? AND evento_fk = ?");
        return $stmt->execute([$usuarioId, $eventoId]);
    } catch(PDOException $e) {
        error_log("Erro ao remover favorito: " . $e->getMessage());
        return false;
    }
}

// Função para buscar favoritos do usuário
function getEventosFavoritos($usuarioId) {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar favoritos: " . $e->getMessage());
        return [];
    }
}

// Função para obter usuário atual
function getUsuarioAtual() {
    return $_SESSION['usuario'] ?? null;
}

// Função para verificar se é admin
function isAdmin() {
    return ($_SESSION['usuario']['tipo'] ?? '') === 'admin';
}

// Funções auxiliares para cores e ícones
function getCorPorTipo($tipo) {
    $cores = [
        'palestra' => '#FF6B6B',
        'feira' => '#4ECDC4',
        'jogos' => '#45B7D1',
        'reuniao' => '#96CEB4',
        'musica' => '#FFA726',
        'teatro' => '#AB47BC',
        'workshop' => '#26A69A'
    ];
    return $cores[$tipo] ?? '#02416D';
}

function getIconePorTipo($tipo) {
    $icones = [
        'palestra' => '<i class="fas fa-chalkboard-teacher"></i>',
        'feira' => '<i class="fas fa-store"></i>',
        'jogos' => '<i class="fas fa-running"></i>',
        'reuniao' => '<i class="fas fa-users"></i>',
        'musica' => '<i class="fas fa-music"></i>',
        'teatro' => '<i class="fas fa-theater-masks"></i>',
        'workshop' => '<i class="fas fa-laptop-code"></i>'
    ];
    return $icones[$tipo] ?? '<i class="fas fa-calendar-alt"></i>';
}
?>
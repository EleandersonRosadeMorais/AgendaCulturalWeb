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

// Função para buscar um evento específico pelo ID
function getEventoById($id) {
    global $pdo;
    
    try {
        // Primeiro, testar query mais simples
        error_log("DEBUG: Buscando evento ID $id");
        
        $stmt = $pdo->prepare("
            SELECT 
                e.*,
                c.titulo as categoria_titulo
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            WHERE e.id_pk = ?
        ");
        
        if (!$stmt) {
            error_log("Erro ao preparar statement: " . print_r($pdo->errorInfo(), true));
            return false;
        }
        
        $stmt->execute([$id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($evento) {
            error_log("DEBUG: Evento encontrado - " . $evento['titulo']);
            
            // Se encontrou o evento, buscar dados do usuário separadamente
            if (!empty($evento['usuario_fk'])) {
                $stmt2 = $pdo->prepare("SELECT nome, email FROM usuario WHERE id_pk = ?");
                $stmt2->execute([$evento['usuario_fk']]);
                $usuario = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($usuario) {
                    $evento['usuario_nome'] = $usuario['nome'];
                    $evento['usuario_email'] = $usuario['email'];
                }
            }
            
            return $evento;
        } else {
            error_log("DEBUG: Evento ID $id não encontrado");
            return false;
        }
        
    } catch (PDOException $e) {
        error_log("ERRO na função getEventoById: " . $e->getMessage());
        error_log("Query: SELECT e.*, c.titulo as categoria_titulo FROM evento e LEFT JOIN categoria c ON e.categoria_fk = c.id_pk WHERE e.id_pk = $id");
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
            ORDER BY e.data ASC, e.hora ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
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
    } catch (PDOException $e) {
        error_log("Erro ao buscar eventos passados: " . $e->getMessage());
        return [];
    }
}

// Função para buscar TODOS os eventos
function getTodosEventos() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, c.titulo as categoria_titulo 
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            ORDER BY e.data DESC, e.hora DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar todos eventos: " . $e->getMessage());
        return [];
    }
}

// Função para verificar login
function verificarLogin($email, $senha) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Remover senha do array
            unset($usuario['senha']);
            return $usuario;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erro ao verificar login: " . $e->getMessage());
        return false;
    }
}

// Função para cadastrar usuário
function cadastrarUsuario($dados) {
    global $pdo;
    
    try {
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id_pk FROM usuario WHERE email = ?");
        $stmt->execute([$dados['email']]);
        
        if ($stmt->rowCount() > 0) {
            return ['erro' => 'Email já cadastrado'];
        }
        
        // Hash da senha
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        // Inserir usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuario (nome, dataNascimento, cpf, email, senha, tipo) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $tipo = $dados['tipo'] ?? 'comum';
        $stmt->execute([
            $dados['nome'],
            $dados['dataNascimento'],
            $dados['cpf'],
            $dados['email'],
            $senhaHash,
            $tipo
        ]);
        
        return ['sucesso' => true, 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Erro ao cadastrar usuário: " . $e->getMessage());
        return ['erro' => 'Erro ao cadastrar usuário: ' . $e->getMessage()];
    }
}

// Função para verificar se é admin
if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['usuario']) && ($_SESSION['usuario']['tipo'] ?? '') === 'admin';
    }
}

// Função para obter usuário atual
function getUsuarioAtual() {
    return $_SESSION['usuario'] ?? null;
}

// ============ FUNÇÕES PARA FAVORITOS (ATUALIZADAS) ============

// Função para adicionar favorito (ATUALIZADA para incluir dataCriacao)
function adicionarFavorito($eventoId) {
    if (!isset($_SESSION['usuario'])) {
        return false;
    }
    
    global $pdo;
    
    try {
        // Primeiro verifica se já existe
        $stmt = $pdo->prepare("SELECT id_pk FROM favorito WHERE usuario_fk = ? AND evento_fk = ?");
        $stmt->execute([$_SESSION['usuario']['id'], $eventoId]);
        
        if ($stmt->rowCount() == 0) {
            // Se não existe, adiciona com dataCriacao
            $stmt = $pdo->prepare("INSERT INTO favorito (usuario_fk, evento_fk, dataCriacao) VALUES (?, ?, NOW())");
            $stmt->execute([$_SESSION['usuario']['id'], $eventoId]);
            
            // Atualizar sessão
            if (!isset($_SESSION['favoritos'])) {
                $_SESSION['favoritos'] = [];
            }
            
            if (!in_array($eventoId, $_SESSION['favoritos'])) {
                $_SESSION['favoritos'][] = $eventoId;
            }
            
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erro ao adicionar favorito: " . $e->getMessage());
        return false;
    }
}

// Função para remover favorito
function removerFavorito($eventoId) {
    if (!isset($_SESSION['usuario'])) {
        return false;
    }
    
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM favorito WHERE usuario_fk = ? AND evento_fk = ?");
        $stmt->execute([$_SESSION['usuario']['id'], $eventoId]);
        
        // Remover da sessão
        if (isset($_SESSION['favoritos'])) {
            $_SESSION['favoritos'] = array_values(array_diff($_SESSION['favoritos'], [$eventoId]));
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao remover favorito: " . $e->getMessage());
        return false;
    }
}

// Função para buscar favoritos do usuário (com dataCriacao)
function getEventosFavoritos($usuarioId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT e.*, c.titulo as categoria_titulo, f.dataCriacao as data_favorito
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            INNER JOIN favorito f ON e.id_pk = f.evento_fk 
            WHERE f.usuario_fk = ? 
            ORDER BY f.dataCriacao DESC
        ");
        $stmt->execute([$usuarioId]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return is_array($resultados) ? $resultados : [];
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar favoritos: " . $e->getMessage());
        return [];
    }
}

// Função para verificar se um evento é favorito
function isEventoFavorito($eventoId) {
    if (!isset($_SESSION['usuario'])) {
        return false;
    }
    
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id_pk FROM favorito WHERE usuario_fk = ? AND evento_fk = ?");
        $stmt->execute([$_SESSION['usuario']['id'], $eventoId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Erro ao verificar favorito: " . $e->getMessage());
        return false;
    }
}

// Função para carregar favoritos na sessão
function carregarFavoritosSessao() {
    if (!isset($_SESSION['usuario'])) {
        return;
    }
    
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT evento_fk FROM favorito WHERE usuario_fk = ?");
        $stmt->execute([$_SESSION['usuario']['id']]);
        $favoritos = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        $_SESSION['favoritos'] = $favoritos;
    } catch (PDOException $e) {
        error_log("Erro ao carregar favoritos: " . $e->getMessage());
        $_SESSION['favoritos'] = [];
    }
}

// Função para contar favoritos de um usuário
function contarFavoritosUsuario($usuarioId) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM favorito WHERE usuario_fk = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        error_log("Erro ao contar favoritos: " . $e->getMessage());
        return 0;
    }
}

// ============ FUNÇÕES PARA GERENCIAR FAVORITOS ============

// Função para processar ação de favoritos
function processarAcaoFavorito($acao, $eventoId) {
    if (!isset($_SESSION['usuario'])) {
        return ['erro' => 'Usuário não logado'];
    }
    
    if ($acao === 'adicionar_favorito') {
        if (adicionarFavorito($eventoId)) {
            return ['sucesso' => true, 'mensagem' => 'Evento adicionado aos favoritos!'];
        } else {
            return ['erro' => 'Evento já está nos favoritos'];
        }
    } elseif ($acao === 'remover_favorito') {
        if (removerFavorito($eventoId)) {
            return ['sucesso' => true, 'mensagem' => 'Evento removido dos favoritos!'];
        } else {
            return ['erro' => 'Erro ao remover dos favoritos'];
        }
    }
    
    return ['erro' => 'Ação inválida'];
}

// Funções auxiliares para cores e ícones
function getCorPorTipo($tipo) {
    $cores = [
        'Música' => '#FF6B6B',
        'Educação' => '#4ECDC4',
        'Cultura' => '#45B7D1',
        'Esportes' => '#96CEB4',
        'Feira' => '#FFA726',
        'teatro' => '#AB47BC',
        'workshop' => '#26A69A',
        'Palestra' => '#FF4081',
        'Exposição' => '#7E57C2',
        'Show' => '#FF9100'
    ];
    return $cores[$tipo] ?? '#02416D';
}

function getIconePorTipo($tipo) {
    $icones = [
        'Música' => '<i class="fas fa-music"></i>',
        'Educação' => '<i class="fas fa-graduation-cap"></i>',
        'Cultura' => '<i class="fas fa-landmark"></i>',
        'Esportes' => '<i class="fas fa-running"></i>',
        'Feira' => '<i class="fas fa-store"></i>',
        'teatro' => '<i class="fas fa-theater-masks"></i>',
        'workshop' => '<i class="fas fa-laptop-code"></i>',
        'Palestra' => '<i class="fas fa-chalkboard-teacher"></i>',
        'Exposição' => '<i class="fas fa-images"></i>',
        'Show' => '<i class="fas fa-microphone-alt"></i>'
    ];
    return $icones[$tipo] ?? '<i class="fas fa-calendar-alt"></i>';
}

// ============ INICIALIZAÇÃO ============

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carregar favoritos na sessão se usuário estiver logado
if (isset($_SESSION['usuario']) && !isset($_SESSION['favoritos'])) {
    carregarFavoritosSessao();
}
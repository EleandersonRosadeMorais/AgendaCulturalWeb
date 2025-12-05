<?php
session_start();
require_once 'config.php';

// Verificar se é admin
if (!isset($_SESSION['usuario']) || !isAdmin()) {
    header('Location: index.php');
    exit();
}

// Buscar todos os usuários do banco
function getTodosUsuarios() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id_pk, nome, dataNascimento, cpf, email, tipo, 
                   DATE_FORMAT(dataNascimento, '%d/%m/%Y') as data_nasc_formatada
            FROM usuario 
            ORDER BY tipo DESC, nome ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar usuários: " . $e->getMessage());
        return [];
    }
}

// Buscar estatísticas
function getEstatisticasUsuarios() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total de usuários
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuario");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Por tipo
        $stmt = $pdo->query("SELECT tipo, COUNT(*) as quantidade FROM usuario GROUP BY tipo");
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats['admin'] = $stats['comum'] = $stats['organizador'] = 0;
        foreach ($tipos as $tipo) {
            $stats[$tipo['tipo']] = $tipo['quantidade'];
        }
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
        return ['total' => 0, 'admin' => 0, 'comum' => 0, 'organizador' => 0];
    }
}

// Processar ações
$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && isset($_POST['usuario_id'])) {
        $usuarioId = intval($_POST['usuario_id']);
        
        try {
            if ($_POST['acao'] === 'alterar_tipo') {
                $novoTipo = $_POST['novo_tipo'] ?? 'comum';
                
                $stmt = $pdo->prepare("UPDATE usuario SET tipo = ? WHERE id_pk = ?");
                $stmt->execute([$novoTipo, $usuarioId]);
                
                $mensagem_sucesso = "Tipo de usuário alterado com sucesso!";
                
            } elseif ($_POST['acao'] === 'excluir_usuario') {
                // Não permite excluir a si mesmo
                if ($usuarioId == $_SESSION['usuario']['id']) {
                    $mensagem_erro = "Você não pode excluir sua própria conta!";
                } else {
                    // Primeiro remove os eventos do usuário
                    $stmt = $pdo->prepare("DELETE FROM favorito WHERE evento_fk IN (SELECT id_pk FROM evento WHERE usuario_fk = ?)");
                    $stmt->execute([$usuarioId]);
                    
                    $stmt = $pdo->prepare("DELETE FROM evento WHERE usuario_fk = ?");
                    $stmt->execute([$usuarioId]);
                    
                    // Depois remove os favoritos do usuário
                    $stmt = $pdo->prepare("DELETE FROM favorito WHERE usuario_fk = ?");
                    $stmt->execute([$usuarioId]);
                    
                    // Finalmente exclui o usuário
                    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_pk = ?");
                    $stmt->execute([$usuarioId]);
                    
                    $mensagem_sucesso = "Usuário excluído com sucesso!";
                }
            }
        } catch (PDOException $e) {
            error_log("Erro na ação admin: " . $e->getMessage());
            $mensagem_erro = "Erro ao processar a ação: " . $e->getMessage();
        }
    }
}

// Buscar dados
$usuarios = getTodosUsuarios();
$estatisticas = getEstatisticasUsuarios();

// Processar busca
$termo_busca = $_GET['buscar'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';
$usuarios_filtrados = $usuarios;

if (!empty($termo_busca)) {
    $usuarios_filtrados = array_filter($usuarios, function($usuario) use ($termo_busca) {
        return stripos($usuario['nome'], $termo_busca) !== false || 
               stripos($usuario['email'], $termo_busca) !== false ||
               stripos($usuario['cpf'], $termo_busca) !== false;
    });
}

if (!empty($filtro_tipo) && $filtro_tipo !== 'todos') {
    $usuarios_filtrados = array_filter($usuarios_filtrados, function($usuario) use ($filtro_tipo) {
        return $usuario['tipo'] === $filtro_tipo;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/admin_usuarios.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-users-cog"></i> Gerenciador de Usuários</h1>
            <p>Painel administrativo para gerenciar usuários do sistema</p>
            <p class="admin-logado">Logado como: <strong><?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></strong> (<?php echo htmlspecialchars($_SESSION['usuario']['email']); ?>)</p>
        </div>

       
        <?php if ($mensagem_sucesso): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem_erro): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $mensagem_erro; ?>
            </div>
        <?php endif; ?>


        
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                <div class="filtro-group">
                    <label for="buscar"><i class="fas fa-search"></i> Buscar:</label>
                    <input type="text" id="buscar" name="buscar" 
                           value="<?php echo htmlspecialchars($termo_busca); ?>"
                           placeholder="Nome, email ou CPF...">
                </div>
                
                <div class="filtro-group">
                    <label for="tipo"><i class="fas fa-filter"></i> Filtrar por tipo:</label>
                    <select id="tipo" name="tipo" onchange="this.form.submit()">
                        <option value="todos" <?php echo $filtro_tipo === 'todos' || empty($filtro_tipo) ? 'selected' : ''; ?>>Todos os tipos</option>
                        <option value="admin" <?php echo $filtro_tipo === 'admin' ? 'selected' : ''; ?>>Administradores</option>
                        <option value="comum" <?php echo $filtro_tipo === 'comum' ? 'selected' : ''; ?>>Usuários Comuns</option>
                    </select>
                </div>
                
                <div class="filtro-actions">
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="admin_usuarios.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

       
        <?php if (!empty($termo_busca) || !empty($filtro_tipo)): ?>
            <div class="search-results">
                <p>
                    <i class="fas fa-info-circle"></i> 
                    Encontrados <strong><?php echo count($usuarios_filtrados); ?></strong> usuário(s)
                    <?php if (!empty($termo_busca)): ?>
                        para "<strong><?php echo htmlspecialchars($termo_busca); ?></strong>"
                    <?php endif; ?>
                    <?php if (!empty($filtro_tipo) && $filtro_tipo !== 'todos'): ?>
                        do tipo <strong><?php echo htmlspecialchars($filtro_tipo); ?></strong>
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>

       
        <div class="usuarios-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Lista de Usuários</h2>
                <div class="section-info">
                    <span class="total-registros">
                        <i class="fas fa-database"></i> 
                        <?php echo count($usuarios_filtrados); ?> registros
                    </span>
                </div>
            </div>

            <?php if (empty($usuarios_filtrados)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h3>Nenhum usuário encontrado</h3>
                    <p>
                        <?php if (!empty($termo_busca) || !empty($filtro_tipo)): ?>
                            Nenhum usuário corresponde aos critérios de busca.
                        <?php else: ?>
                            Não há usuários cadastrados no sistema.
                        <?php endif; ?>
                    </p>
                    <a href="admin_usuarios.php" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Limpar Filtros
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="usuarios-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuário</th>
                                <th>Contato</th>
                                <th>Data de Nascimento</th>
                                <th>Tipo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios_filtrados as $usuario): 
                                $isCurrentUser = $usuario['id_pk'] == $_SESSION['usuario']['id'];
                            ?>
                                <tr class="<?php echo $isCurrentUser ? 'current-user' : ''; ?>">
                                    <td class="user-id">#<?php echo $usuario['id_pk']; ?></td>
                                    
                                    <td class="user-info">
                                        <div class="user-avatar">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div class="user-details">
                                            <h4><?php echo htmlspecialchars($usuario['nome']); ?></h4>
                                            <p class="user-cpf">CPF: <?php echo htmlspecialchars($usuario['cpf']); ?></p>
                                        </div>
                                    </td>
                                    
                                    <td class="user-contact">
                                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['email']); ?></p>
                                    </td>
                                    
                                    <td class="user-birthdate">
                                        <i class="fas fa-birthday-cake"></i> 
                                        <?php echo htmlspecialchars($usuario['data_nasc_formatada'] ?? $usuario['dataNascimento']); ?>
                                    </td>
                                    
                                    <td class="user-type">
                                        <span class="badge badge-<?php echo $usuario['tipo']; ?>">
                                            <i class="fas fa-<?php echo $usuario['tipo'] === 'admin' ? 'crown' : ($usuario['tipo'] === 'organizador' ? 'calendar-plus' : 'user'); ?>"></i>
                                            <?php echo htmlspecialchars(ucfirst($usuario['tipo'])); ?>
                                        </span>
                                    </td>
                                    
                                    <td class="user-actions">
                                        <div class="action-buttons">
                                           
                                            <form method="POST" class="action-form">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_pk']; ?>">
                                                <input type="hidden" name="acao" value="alterar_tipo">
                                                
                                                <select name="novo_tipo" class="type-select" onchange="this.form.submit()" <?php echo $isCurrentUser ? 'disabled' : ''; ?>>
                                                    <option value="comum" <?php echo $usuario['tipo'] === 'comum' ? 'selected' : ''; ?>>Comum</option>
                                                    <option value="admin" <?php echo $usuario['tipo'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                            </form>
                                            
                                            
                                            <?php if (!$isCurrentUser): ?>
                                                <form method="POST" class="action-form" 
                                                      onsubmit="return confirm('Tem certeza que deseja excluir o usuário \\'<?php echo addslashes($usuario['nome']); ?>\\'? Esta ação não pode ser desfeita!');">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id_pk']; ?>">
                                                    <input type="hidden" name="acao" value="excluir_usuario">
                                                    <button type="submit" class="btn btn-delete">
                                                        <i class="fas fa-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="current-user-label">Você</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="admin-navigation">
            <a href="index.php" class="btn btn-admin">
                <i class="fas fa-arrow-left"></i> Voltar a Página Principal
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Ir para Início
            </a>

            <a href="admin_eventos.php" class="btn btn-secondary">
                <i class="fas fa-tasks"></i>  Gerenciador Eventos
            </a>
        </div>
    </div>
</body>
</html>
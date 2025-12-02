<?php
session_start();
require_once 'config.php';

// Verificar se é admin
if (!isset($_SESSION['usuario']) || !isAdmin()) {
    header('Location: index.php');
    exit();
}

// Buscar todos os eventos do banco com informações do usuário
function getTodosEventosAdmin() {
    global $pdo;
    
    try {
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
                e.data_criacao,
                c.titulo as categoria_titulo,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            LEFT JOIN usuario u ON e.usuario_fk = u.id_pk 
            ORDER BY e.data DESC, e.hora DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar eventos admin: " . $e->getMessage());
        return [];
    }
}

// Buscar estatísticas dos eventos
function getEstatisticasEventos() {
    global $pdo;
    
    try {
        $stats = [];
        
        // Total de eventos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM evento");
        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Eventos futuros
        $stmt = $pdo->query("SELECT COUNT(*) as futuros FROM evento WHERE data >= CURDATE()");
        $stats['futuros'] = $stmt->fetch(PDO::FETCH_ASSOC)['futuros'];
        
        // Eventos passados
        $stmt = $pdo->query("SELECT COUNT(*) as passados FROM evento WHERE data < CURDATE()");
        $stats['passados'] = $stmt->fetch(PDO::FETCH_ASSOC)['passados'];
        
        // Eventos por categoria
        $stmt = $pdo->query("
            SELECT c.titulo, COUNT(*) as quantidade 
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            GROUP BY c.titulo 
            ORDER BY quantidade DESC
        ");
        $stats['por_categoria'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Erro ao buscar estatísticas: " . $e->getMessage());
        return ['total' => 0, 'futuros' => 0, 'passados' => 0, 'por_categoria' => []];
    }
}

// Processar ações
$mensagem_sucesso = '';
$mensagem_erro = '';

if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    
    try {
        // Primeiro exclui os favoritos associados
        $stmt = $pdo->prepare("DELETE FROM favorito WHERE evento_fk = ?");
        $stmt->execute([$id_excluir]);
        
        // Depois exclui o evento
        $stmt = $pdo->prepare("DELETE FROM evento WHERE id_pk = ?");
        $stmt->execute([$id_excluir]);
        
        $mensagem_sucesso = "Evento #{$id_excluir} excluído com sucesso!";
    } catch (PDOException $e) {
        error_log("Erro ao excluir evento: " . $e->getMessage());
        $mensagem_erro = "Erro ao excluir evento: " . $e->getMessage();
    }
}

// Buscar dados do banco
$eventos = getTodosEventosAdmin();
$estatisticas = getEstatisticasEventos();

// Processar busca
$termo_busca = $_GET['buscar'] ?? '';
$eventos_filtrados = $eventos;

if (!empty($termo_busca)) {
    $eventos_filtrados = array_filter($eventos, function($evento) use ($termo_busca) {
        return stripos($evento['titulo'], $termo_busca) !== false || 
               stripos($evento['local'], $termo_busca) !== false ||
               stripos($evento['responsavel'], $termo_busca) !== false ||
               stripos($evento['usuario_nome'], $termo_busca) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Eventos - Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/admin_eventos.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-calendar-alt"></i> Gerenciador de Eventos</h1>
            <p>Painel administrativo - Gerencie todos os eventos do sistema</p>
            <p class="admin-logado">Logado como: <strong><?php echo htmlspecialchars($_SESSION['usuario']['nome']); ?></strong></p>
        </div>

        <!-- Mensagens -->
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

        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-alt"></i>
                <div class="stat-number"><?php echo $estatisticas['total']; ?></div>
                <div class="stat-label">Total de Eventos</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <div class="stat-number"><?php echo $estatisticas['futuros']; ?></div>
                <div class="stat-label">Eventos Futuros</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-history"></i>
                <div class="stat-number"><?php echo $estatisticas['passados']; ?></div>
                <div class="stat-label">Eventos Realizados</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-pie"></i>
                <div class="stat-number"><?php echo count($estatisticas['por_categoria']); ?></div>
                <div class="stat-label">Categorias Ativas</div>
            </div>
        </div>

        <!-- Filtros e Busca -->
        <div class="filtros-container">
            <form method="GET" class="filtros-form">
                <div class="filtro-group">
                    <label for="buscar"><i class="fas fa-search"></i> Buscar:</label>
                    <input type="text" id="buscar" name="buscar" 
                           value="<?php echo htmlspecialchars($termo_busca); ?>"
                           placeholder="Título, local, responsável...">
                </div>
                
                <div class="filtro-actions">
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="admin_eventos.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Limpar
                    </a>
                    <a href="cadastroEvento.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Novo Evento
                    </a>
                </div>
            </form>
        </div>

        <!-- Resultados da Busca -->
        <?php if (!empty($termo_busca)): ?>
            <div class="search-results">
                <p>
                    <i class="fas fa-info-circle"></i> 
                    Encontrados <strong><?php echo count($eventos_filtrados); ?></strong> evento(s) 
                    para "<strong><?php echo htmlspecialchars($termo_busca); ?></strong>"
                </p>
            </div>
        <?php endif; ?>

        <!-- Tabela de Eventos -->
        <div class="eventos-section">
            <div class="section-header">
                <h2><i class="fas fa-list"></i> Lista de Eventos</h2>
                <div class="section-info">
                    <span class="total-registros">
                        <i class="fas fa-database"></i> 
                        <?php echo count($eventos_filtrados); ?> registros
                    </span>
                </div>
            </div>

            <?php if (empty($eventos_filtrados)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Nenhum evento encontrado</h3>
                    <p>
                        <?php if (!empty($termo_busca)): ?>
                            Nenhum evento corresponde aos critérios de busca.
                        <?php else: ?>
                            Não há eventos cadastrados no sistema.
                        <?php endif; ?>
                    </p>
                    <a href="cadastroEvento.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Cadastrar Primeiro Evento
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="eventos-table">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Data e Hora</th>
                                <th>Local</th>
                                <th>Tipo</th>
                                <th>Responsável</th>
                                <th>Criador</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventos_filtrados as $evento): 
                                $cor = getCorPorTipo($evento['tipoEvento']);
                                $icone = getIconePorTipo($evento['tipoEvento']);
                                $isPassado = strtotime($evento['data']) < strtotime(date('Y-m-d'));
                            ?>
                                <tr class="<?php echo $isPassado ? 'evento-passado' : ''; ?>">
                                    <td class="evento-info">
                                        <div class="evento-avatar" style="background: <?php echo $cor; ?>;">
                                            <?php echo $icone; ?>
                                        </div>
                                        <div class="evento-details">
                                            <h4><?php echo htmlspecialchars($evento['titulo']); ?></h4>
                                            <p class="evento-id">ID: #<?php echo $evento['id_pk']; ?></p>
                                            <?php if ($evento['categoria_titulo']): ?>
                                                <p class="evento-categoria">
                                                    <i class="fas fa-tag"></i> 
                                                    <?php echo htmlspecialchars($evento['categoria_titulo']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <td class="evento-data">
                                        <p><i class="fas fa-calendar-day"></i> 
                                            <strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?></strong>
                                        </p>
                                        <p><i class="fas fa-clock"></i> 
                                            <?php echo date('H:i', strtotime($evento['hora'])); ?>
                                        </p>
                                        <?php if ($isPassado): ?>
                                            <span class="status-badge passado">
                                                <i class="fas fa-check-circle"></i> Realizado
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge futuro">
                                                <i class="fas fa-bell"></i> Futuro
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="evento-local">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo htmlspecialchars($evento['local']); ?>
                                    </td>
                                    
                                    <td class="evento-tipo">
                                        <span class="badge" style="background: <?php echo $cor; ?>; color: white;">
                                            <?php echo htmlspecialchars($evento['tipoEvento']); ?>
                                        </span>
                                    </td>
                                    
                                    <td class="evento-responsavel">
                                        <i class="fas fa-user-tie"></i> 
                                        <?php echo htmlspecialchars($evento['responsavel']); ?>
                                    </td>
                                    
                                    <td class="evento-criador">
                                        <?php if ($evento['usuario_nome']): ?>
                                            <p><i class="fas fa-user"></i> 
                                                <?php echo htmlspecialchars($evento['usuario_nome']); ?>
                                            </p>
                                            <p class="criador-email">
                                                <i class="fas fa-envelope"></i> 
                                                <?php echo htmlspecialchars($evento['usuario_email']); ?>
                                            </p>
                                        <?php else: ?>
                                            <span class="sem-criador">Usuário removido</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="evento-actions">
                                        <div class="action-buttons">
                                            <a href="visualizar_evento.php?id=<?php echo $evento['id_pk']; ?>" 
                                               class="btn btn-view" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <a href="editar_evento.php?id=<?php echo $evento['id_pk']; ?>" 
                                               class="btn btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <a href="admin_eventos.php?excluir=<?php echo $evento['id_pk']; ?><?php echo !empty($termo_busca) ? '&buscar=' . urlencode($termo_busca) : ''; ?>"
                                               class="btn btn-delete" 
                                               title="Excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir o evento \\'<?php echo addslashes($evento['titulo']); ?>\\'? Esta ação não pode ser desfeita!');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Links de navegação -->
        <div class="admin-navigation">
            <a href="admin_usuarios.php" class="btn btn-admin">
                <i class="fas fa-users-cog"></i> Gerenciar Usuários
            </a>
            <a href="admin.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar ao Painel Admin
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Ir para Início
            </a>
        </div>
    </div>
</body>
</html>
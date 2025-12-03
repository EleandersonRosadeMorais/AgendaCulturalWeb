<?php
session_start();
require_once 'config.php';

// Verificar se o ID do evento foi passado
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$eventoId = intval($_GET['id']);

// Buscar o evento do banco
function getEventoCompleto($id) {
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
                e.categoria_fk,
                e.usuario_fk,
                c.titulo as categoria_titulo,
                u.nome as usuario_nome,
                u.email as usuario_email
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
            LEFT JOIN usuario u ON e.usuario_fk = u.id_pk 
            WHERE e.id_pk = ?
        ");
        $stmt->execute([$id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $evento ? $evento : false;
        
    } catch (PDOException $e) {
        error_log("Erro ao buscar evento: " . $e->getMessage());
        return false;
    }
}

// Buscar o evento
$evento = getEventoCompleto($eventoId);

if (!$evento) {
    header('Location: index.php');
    exit();
}

// Verificar se é admin
$isAdmin = isset($_SESSION['usuario']) && ($_SESSION['usuario']['tipo'] ?? '') === 'admin';

// Verificar se o usuário atual pode favoritar
$usuarioAtual = getUsuarioAtual();
$podeFavoritar = $usuarioAtual && !$isAdmin;

// Verificar se já é favorito
$isFavorito = false;
if ($podeFavoritar) {
    $isFavorito = isEventoFavorito($eventoId);
}

// Processar ação de favorito
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $acaoEventoId = intval($_GET['id']);
    
    if ($acaoEventoId === $eventoId) {
        if ($usuarioAtual && !$isAdmin) {
            if ($_GET['acao'] === 'adicionar_favorito') {
                if (adicionarFavorito($eventoId)) {
                    $_SESSION['mensagem'] = '✅ Evento adicionado aos favoritos!';
                    $_SESSION['mensagem_tipo'] = 'sucesso';
                    $isFavorito = true;
                }
            } elseif ($_GET['acao'] === 'remover_favorito') {
                if (removerFavorito($eventoId)) {
                    $_SESSION['mensagem'] = '🗑️ Evento removido dos favoritos!';
                    $_SESSION['mensagem_tipo'] = 'sucesso';
                    $isFavorito = false;
                }
            }
            header('Location: evento.php?id=' . $eventoId);
            exit();
        }
    }
}

// Processar exclusão (apenas admin)
if (isset($_GET['excluir']) && $isAdmin) {
    $id_excluir = intval($_GET['excluir']);
    
    if ($id_excluir === $eventoId) {
        try {
            // Primeiro exclui os favoritos associados
            $stmt = $pdo->prepare("DELETE FROM favorito WHERE evento_fk = ?");
            $stmt->execute([$id_excluir]);
            
            // Depois exclui o evento
            $stmt = $pdo->prepare("DELETE FROM evento WHERE id_pk = ?");
            $stmt->execute([$id_excluir]);
            
            $_SESSION['mensagem'] = "Evento '{$evento['titulo']}' excluído com sucesso!";
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: admin_eventos.php');
            exit();
        } catch (PDOException $e) {
            error_log("Erro ao excluir evento: " . $e->getMessage());
            $_SESSION['mensagem'] = "Erro ao excluir evento!";
            $_SESSION['mensagem_tipo'] = 'erro';
        }
    }
}

// Dados para exibição
$cor = getCorPorTipo($evento['tipoEvento']);
$icone = getIconePorTipo($evento['tipoEvento']);
$isPassado = strtotime($evento['data']) < strtotime(date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($evento['titulo']); ?> - Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/evento.css">
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <!-- Banner do evento -->
    <div class="evento-banner">
        <?php if (!empty($evento['banner'])): ?>
            <img src="<?php echo htmlspecialchars($evento['banner']); ?>" 
                 alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                 onerror="this.style.display='none'; this.parentElement.classList.add('no-banner')">
        <?php endif; ?>
        <div class="banner-overlay">
            <div class="container">
                <div class="evento-header">
                    <div class="evento-tipo-banner">
                        <?php echo $icone; ?>
                        <span><?php echo htmlspecialchars($evento['tipoEvento']); ?></span>
                    </div>
                    <h1><?php echo htmlspecialchars($evento['titulo']); ?></h1>
                    <div class="evento-metadata">
                        <span class="data-hora">
                            <i class="fas fa-calendar-day"></i>
                            <?php echo date('d/m/Y', strtotime($evento['data'])); ?>
                            <i class="fas fa-clock"></i>
                            <?php echo date('H:i', strtotime($evento['hora'])); ?>
                        </span>
                        <span class="local">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($evento['local']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="evento-container">
            <!-- Ações do evento -->
            <div class="evento-actions-top">
                <a href="index.php" class="btn btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar para Eventos
                </a>
                
                <?php if ($podeFavoritar): ?>
                    <?php if ($isFavorito): ?>
                        <a href="evento.php?id=<?php echo $eventoId; ?>&acao=remover_favorito" class="btn btn-favorito ativo">
                            <i class="fas fa-heart"></i> Remover dos Favoritos
                        </a>
                    <?php else: ?>
                        <a href="evento.php?id=<?php echo $eventoId; ?>&acao=adicionar_favorito" class="btn btn-favorito">
                            <i class="fas fa-heart"></i> Adicionar aos Favoritos
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($isAdmin): ?>
                    <a href="editar_evento.php?id=<?php echo $eventoId; ?>" class="btn btn-editar">
                        <i class="fas fa-edit"></i> Editar Evento
                    </a>
                    <a href="evento.php?id=<?php echo $eventoId; ?>&excluir=<?php echo $eventoId; ?>" 
                       class="btn btn-excluir"
                       onclick="return confirm('Tem certeza que deseja excluir o evento \\'<?php echo addslashes($evento['titulo']); ?>\\'? Esta ação não pode ser desfeita!');">
                        <i class="fas fa-trash"></i> Excluir Evento
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Conteúdo principal -->
            <div class="evento-content">
                <div class="evento-grid">
                    <!-- Informações principais -->
                    <div class="evento-info-card">
                        <h2><i class="fas fa-info-circle"></i> Informações do Evento</h2>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-tie"></i>
                                Organizador/Responsável
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($evento['responsavel']); ?></div>
                        </div>
                        
                        <?php if (!empty($evento['categoria_titulo'])): ?>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-tag"></i>
                                Categoria
                            </div>
                            <div class="info-value"><?php echo htmlspecialchars($evento['categoria_titulo']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-check"></i>
                                Status
                            </div>
                            <div class="info-value">
                                <?php if ($isPassado): ?>
                                    <span class="status-badge passado">
                                        <i class="fas fa-check-circle"></i> Evento Realizado
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge futuro">
                                        <i class="fas fa-bell"></i> Evento Futuro
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($evento['usuario_nome'])): ?>
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-plus"></i>
                                Criado por
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($evento['usuario_nome']); ?>
                                <?php if (!empty($evento['usuario_email'])): ?>
                                    <br><small><?php echo htmlspecialchars($evento['usuario_email']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Descrição completa -->
                    <div class="evento-descricao-card">
                        <h2><i class="fas fa-align-left"></i> Descrição</h2>
                        <div class="descricao-content">
                            <?php echo nl2br(htmlspecialchars($evento['descricao'])); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Informações adicionais -->
                <div class="evento-detalhes-card">
                    <h2><i class="fas fa-list-alt"></i> Detalhes Adicionais</h2>
                    <div class="detalhes-grid">
                        <div class="detalhe-item">
                            <div class="detalhe-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="detalhe-content">
                                <h3>Data Completa</h3>
                                <p><?php echo date('l, d \\d\\e F \\d\\e Y', strtotime($evento['data'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="detalhe-item">
                            <div class="detalhe-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="detalhe-content">
                                <h3>Horário</h3>
                                <p><?php echo date('H:i', strtotime($evento['hora'])); ?> horas</p>
                            </div>
                        </div>
                        
                        <div class="detalhe-item">
                            <div class="detalhe-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="detalhe-content">
                                <h3>Localização</h3>
                                <p><?php echo htmlspecialchars($evento['local']); ?></p>
                            </div>
                        </div>
                        
                        <div class="detalhe-item">
                            <div class="detalhe-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="detalhe-content">
                                <h3>Responsável</h3>
                                <p><?php echo htmlspecialchars($evento['responsavel']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Ações inferiores -->
                <div class="evento-actions-bottom">
                    <a href="index.php" class="btn btn-voltar">
                        <i class="fas fa-arrow-left"></i> Voltar para Eventos
                    </a>
                    
                    <?php if ($isAdmin): ?>
                        <a href="admin_eventos.php" class="btn btn-admin">
                            <i class="fas fa-cog"></i> Painel Administrativo
                        </a>
                    <?php endif; ?>
                    
                    <!-- Botão de compartilhamento (simulado) -->
                    <button class="btn btn-compartilhar" onclick="compartilharEvento()">
                        <i class="fas fa-share-alt"></i> Compartilhar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function compartilharEvento() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo addslashes($evento['titulo']); ?>',
                    text: 'Confira este evento na Agenda Cultural!',
                    url: window.location.href
                })
                .then(() => console.log('Evento compartilhado com sucesso'))
                .catch((error) => console.log('Erro ao compartilhar:', error));
            } else {
                // Fallback para copiar link
                const link = window.location.href;
                navigator.clipboard.writeText(link)
                    .then(() => {
                        alert('Link copiado para a área de transferência!');
                    })
                    .catch((error) => {
                        alert('Erro ao copiar link. Tente manualmente: ' + link);
                    });
            }
        }
    </script>
</body>
</html>
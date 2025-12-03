<?php
session_start();
require_once 'config.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuarioAtual = getUsuarioAtual();

// Buscar eventos favoritos do usuário
$favoritos = getEventosFavoritos($usuarioAtual['id']);

// Processar remoção de favoritos
if (isset($_GET['acao']) && $_GET['acao'] === 'remover_favorito' && isset($_GET['id'])) {
    $eventoId = intval($_GET['id']);
    
    if (removerFavorito($eventoId)) {
        $_SESSION['mensagem'] = '✅ Evento removido dos favoritos!';
        $_SESSION['mensagem_tipo'] = 'sucesso';
    } else {
        $_SESSION['mensagem'] = '❌ Erro ao remover dos favoritos';
        $_SESSION['mensagem_tipo'] = 'erro';
    }
    
    header('Location: favoritos.php');
    exit;
}

// Exibir mensagens de feedback
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo = $_SESSION['mensagem_tipo'] ?? 'info';
    unset($_SESSION['mensagem'], $_SESSION['mensagem_tipo']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Favoritos - Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        /* Estilo para mensagens de feedback */
        .mensagem-flash {
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mensagem-sucesso {
            background-color: #10b981;
            border-left: 5px solid #059669;
        }
        .mensagem-aviso {
            background-color: #f59e0b;
            border-left: 5px solid #d97706;
        }
        .mensagem-erro {
            background-color: #ef4444;
            border-left: 5px solid #dc2626;
        }
        .mensagem-flash i {
            font-size: 1.2em;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Estilo para botões */
        .btn-remover {
            background-color: #fff5f5;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .btn-remover:hover {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .btn-voltar-feed {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            margin-top: 15px;
        }
        .btn-voltar-feed:hover {
            background-color: #e9ecef;
            color: #495057;
        }
        
        /* Badge para favoritos */
        .favorito-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8em;
            z-index: 2;
        }
        
        /* Layout da página */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .empty-message {
            text-align: center;
            padding: 60px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 40px;
        }
        .empty-icon {
            font-size: 4em;
            color: #ef4444;
            margin-bottom: 20px;
        }
        .empty-message h3 {
            color: #343a40;
            margin-bottom: 10px;
        }
        .empty-message p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .stats-favoritos {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-mini {
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9em;
        }
        .stat-mini i {
            color: #ef4444;
        }
        .stat-mini .count {
            font-weight: bold;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <?php if (isset($mensagem)): ?>
        <div class="mensagem-flash mensagem-<?php echo $tipo; ?>">
            <i class="fas fa-<?php echo $tipo === 'sucesso' ? 'check-circle' : ($tipo === 'erro' ? 'times-circle' : 'exclamation-triangle'); ?>"></i>
            <span><?php echo htmlspecialchars($mensagem); ?></span>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">
                 Meus Eventos Favoritos
            </h1>
        </div>

        <!-- Estatísticas dos favoritos -->
        <?php if (!empty($favoritos)): ?>
            <?php
            // Separar favoritos por status
            $hoje = date('Y-m-d');
            $futuros = array_filter($favoritos, function($evento) use ($hoje) {
                return $evento['data'] >= $hoje;
            });
            $passados = array_filter($favoritos, function($evento) use ($hoje) {
                return $evento['data'] < $hoje;
            });
            ?>
            <div class="stats-favoritos">
            
                </div>

            </div>
        <?php endif; ?>

        <?php if (empty($favoritos)): ?>
            <div class="empty-message">
                <h3>Nenhum evento favoritado</h3>
                <p>Adicione eventos aos favoritos para vê-los aqui!</p>
                <p style="font-size: 0.9em; color: #6c757d; margin-top: 10px;">
                    <i class="fas fa-info-circle"></i> 
                    Clique no ícone de coração nos eventos para adicionar aos favoritos
                </p>
                <a href="index.php" class="btn btn-primary btn-voltar-feed">
                    <i class="fas fa-arrow-left"></i> Voltar a Página Principal
                </a>
            </div>
        <?php else: ?>
            <!-- Favoritos Futuros -->
            <?php if (!empty($futuros)): ?>
                <h2 class="page-title">
                    <i class="fas fa-calendar-check"></i> Próximos Favoritos
                    <span class="badge"><?php echo count($futuros); ?></span>
                </h2>
                
                <div class="eventos-lista">
                    <?php 
                    // Ordenar futuros por data crescente
                    usort($futuros, function ($a, $b) {
                        return strcmp($a['data'], $b['data']);
                    });
                    
                    foreach ($futuros as $evento):
                        // Verificar se as chaves existem
                        $tipo = $evento['tipoEvento'] ?? $evento['tipo'] ?? 'Outro';
                        $cor = getCorPorTipo($tipo);
                        $icone = getIconePorTipo($tipo);
                    ?>
                        <div class="evento-card" style="border-top: 4px solid <?php echo $cor; ?>">
                            <div class="favorito-badge">
                                <i class="fas fa-heart"></i> Favoritado
                            </div>
                            
                            <div class="evento-banner <?php echo empty($evento['banner']) ? 'no-image' : ''; ?>">
                                <?php if (!empty($evento['banner'])): ?>
                                    <img src="<?php echo htmlspecialchars($evento['banner']); ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                        class="evento-banner-img"
                                        onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
                                <?php endif; ?>
                                <div class="banner-content">
                                    <div class="evento-tipo">
                                        <?php echo $icone; ?>
                                        <?php echo htmlspecialchars($tipo); ?>
                                    </div>
                                    <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                                </div>
                            </div>

                            <div class="evento-info">
                                <div class="evento-titulo"><?php echo htmlspecialchars($evento['titulo']); ?></div>

                                <div class="evento-detalhes">
                                    <div class="detalhe-item">
                                        <i class="fas fa-calendar-day" style="color: <?php echo $cor; ?>"></i>
                                        <strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?></strong>
                                    </div>
                                    <div class="detalhe-item">
                                        <i class="fas fa-clock" style="color: <?php echo $cor; ?>"></i>
                                        <span class="detalhe-destaque">
                                            <i class="fas fa-bell"></i>
                                            <?php echo date('H:i', strtotime($evento['hora'])); ?>
                                        </span>
                                    </div>
                                    <div class="detalhe-item">
                                        <i class="fas fa-map-marker-alt" style="color: <?php echo $cor; ?>"></i>
                                        <?php echo htmlspecialchars($evento['local'] ?? 'Local não informado'); ?>
                                    </div>
                                </div>

                                <div class="evento-descricao">
                                    <?php 
                                    $descricao = $evento['descricao'] ?? 'Descrição não disponível';
                                    echo strlen($descricao) > 150 ? substr($descricao, 0, 150) . '...' : $descricao;
                                    ?>
                                </div>

                                <div class="evento-acoes">
                                    <a href="evento.php?id=<?php echo $evento['id_pk'] ?? $evento['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-info-circle"></i> Detalhes
                                    </a>

                                    <a href="favoritos.php?acao=remover_favorito&id=<?php echo $evento['id_pk'] ?? $evento['id']; ?>" 
                                       class="btn btn-remover"
                                       onclick="return confirm('Tem certeza que deseja remover este evento dos favoritos?')">
                                        <i class="fas fa-heart-broken"></i> Remover
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Favoritos Passados -->
            <?php if (!empty($passados)): ?>
                <h2 class="page-title" style="margin-top: 40px;">
                    <i class="fas fa-history"></i> Favoritos Realizados
                    <span class="badge"><?php echo count($passados); ?></span>
                </h2>
                
                <div class="eventos-lista">
                    <?php 
                    // Ordenar passados por data decrescente
                    usort($passados, function ($a, $b) {
                        return strcmp($b['data'], $a['data']);
                    });
                    
                    foreach ($passados as $evento):
                        $tipo = $evento['tipoEvento'] ?? $evento['tipo'] ?? 'Outro';
                        $cor = getCorPorTipo($tipo);
                        $icone = getIconePorTipo($tipo);
                    ?>
                        <div class="evento-card passado">
                            <div class="status-badge">
                                <i class="fas fa-check-circle"></i> Realizado
                            </div>
                            
                            <div class="favorito-badge">
                                <i class="fas fa-heart"></i> Favoritado
                            </div>

                            <div class="evento-banner <?php echo empty($evento['banner']) ? 'no-image' : ''; ?>">
                                <?php if (!empty($evento['banner'])): ?>
                                    <img src="<?php echo htmlspecialchars($evento['banner']); ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                        class="evento-banner-img"
                                        onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
                                <?php endif; ?>
                                <div class="banner-content">
                                    <div class="evento-tipo">
                                        <?php echo $icone; ?>
                                        <?php echo htmlspecialchars($tipo); ?>
                                    </div>
                                    <h3><?php echo htmlspecialchars($evento['titulo']); ?></h3>
                                </div>
                            </div>

                            <div class="evento-info">
                                <div class="evento-titulo"><?php echo htmlspecialchars($evento['titulo']); ?></div>

                                <div class="evento-detalhes">
                                    <div class="detalhe-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?></strong>
                                    </div>
                                    <div class="detalhe-item">
                                        <i class="fas fa-clock"></i>
                                        <span class="detalhe-destaque">
                                            <i class="fas fa-bell"></i>
                                            <?php echo date('H:i', strtotime($evento['hora'])); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="evento-descricao">
                                    <?php 
                                    $descricao = $evento['descricao'] ?? 'Descrição não disponível';
                                    echo strlen($descricao) > 150 ? substr($descricao, 0, 150) . '...' : $descricao;
                                    ?>
                                </div>

                                <div class="evento-acoes">
                                    <a href="evento.php?id=<?php echo $evento['id_pk'] ?? $evento['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-info-circle"></i> Detalhes
                                    </a>

                                    <a href="favoritos.php?acao=remover_favorito&id=<?php echo $evento['id_pk'] ?? $evento['id']; ?>" 
                                       class="btn btn-remover"
                                       onclick="return confirm('Tem certeza que deseja remover este evento dos favoritos?')">
                                        <i class="fas fa-heart-broken"></i> Remover 
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
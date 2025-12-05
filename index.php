<?php
session_start();
require_once 'config.php';

// Buscar TODOS os eventos do MySQL
$todosEventos = getTodosEventos();

// Função para separar eventos por data
function separarEventosPorData($eventos) {
    $eventosFuturos = [];
    $eventosPassados = [];
    $hoje = date('Y-m-d');

    foreach ($eventos as $evento) {
        if ($evento['data'] >= $hoje) {
            $eventosFuturos[] = $evento;
        } else {
            $eventosPassados[] = $evento;
        }
    }

    // Ordenar futuros por data crescente, passados por data decrescente
    usort($eventosFuturos, function ($a, $b) {
        return strtotime($a['data']) - strtotime($b['data']);
    });

    return [
        'futuros' => $eventosFuturos,
        'passados' => $eventosPassados
    ];
}

$eventosSeparados = separarEventosPorData($todosEventos);
$eventosFuturos = $eventosSeparados['futuros'];
$eventosPassados = $eventosSeparados['passados'];

$usuarioAtual = getUsuarioAtual();

// Verificar se o usuário é admin
$isAdmin = isset($usuarioAtual) && ($usuarioAtual['tipo'] ?? '') === 'admin';

// Processar ações de favoritos (apenas se NÃO for admin)
if (isset($_GET['acao']) && isset($_GET['id'])) {
    $eventoId = intval($_GET['id']);
    
    if ($usuarioAtual) {
        // Se for admin, não pode favoritar
        if ($isAdmin) {
            $_SESSION['mensagem'] = '👑 Administradores não podem favoritar eventos';
            $_SESSION['mensagem_tipo'] = 'aviso';
        } else {
            if ($_GET['acao'] === 'adicionar_favorito') {
                if (adicionarFavorito($eventoId)) {
                    $_SESSION['mensagem'] = '✅ Evento adicionado aos favoritos!';
                    $_SESSION['mensagem_tipo'] = 'sucesso';
                } else {
                    $_SESSION['mensagem'] = '⚠️ Este evento já está nos seus favoritos';
                    $_SESSION['mensagem_tipo'] = 'aviso';
                }
            } elseif ($_GET['acao'] === 'remover_favorito') {
                if (removerFavorito($eventoId)) {
                    $_SESSION['mensagem'] = '🗑️ Evento removido dos favoritos!';
                    $_SESSION['mensagem_tipo'] = 'sucesso';
                } else {
                    $_SESSION['mensagem'] = '❌ Erro ao remover dos favoritos';
                    $_SESSION['mensagem_tipo'] = 'erro';
                }
            }
        }
    } else {
        $_SESSION['mensagem'] = '🔒 Faça login para favoritar eventos';
        $_SESSION['mensagem_tipo'] = 'aviso';
    }
    
    header('Location: index.php');
    exit;
}

// Buscar IDs dos favoritos do usuário atual (apenas se NÃO for admin)
$favoritosIds = [];
if ($usuarioAtual && !$isAdmin) {
    // Se não tem na sessão, carrega do banco
    if (!isset($_SESSION['favoritos'])) {
        carregarFavoritosSessao();
    }
    $favoritosIds = $_SESSION['favoritos'] ?? [];
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
    <title>Agenda Cultural - Eventos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/footer.css"> 
    <style>
       
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
        
        
        .btn-favorito {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        .btn-favorito:hover {
            background-color: #e9ecef;
            color: #495057;
        }
        .btn-remover {
            background-color: #fff5f5;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .btn-remover:hover {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        
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
        
        
        .admin-indicator {
            background: rgba(2, 65, 109, 0.1);
            color: #02416D;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            border: 1px solid #02416D;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>
    
    <?php if (isset($mensagem)): ?>
        <div class="mensagem-flash mensagem-<?php echo $tipo; ?>">
            <i class="fas fa-<?php echo $tipo === 'sucesso' ? 'check-circle' : ($tipo === 'aviso' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
            <span><?php echo htmlspecialchars($mensagem); ?></span>
        </div>
    <?php endif; ?>

    <div class="container">
        
        <?php if ($isAdmin): ?>
            <div style="margin-bottom: 20px; text-align: center;">
                <div class="admin-indicator">
                    <i class="fas fa-user-shield"></i>
                    Você está logado como Administrador
                </div>
            </div>
        <?php endif; ?>
        

        <?php if (empty($todosEventos)): ?>
            <div class="empty-message">
                <div class="empty-icon">📅</div>
                <h3>Nenhum evento cadastrado</h3>
                <p>Seja o primeiro a criar um evento cultural!</p>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <a href="cadastroEvento.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Criar Primeiro Evento
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
           
            <?php if (!empty($eventosFuturos)): ?>
                <h1 class="page-title">
                    <i class="fas fa-calendar-alt"></i> Próximos Eventos
                    <span class="badge"><?php echo count($eventosFuturos); ?></span>
                </h1>

                <div class="eventos-lista">
                    <?php foreach ($eventosFuturos as $evento):
                        $cor = getCorPorTipo($evento['tipoEvento']);
                        $icone = getIconePorTipo($evento['tipoEvento']);
                        $isFavorito = $usuarioAtual && !$isAdmin && in_array($evento['id_pk'], $favoritosIds);
                        ?>
                        <div class="evento-card" style="border-top: 4px solid <?php echo $cor; ?>">
                            <?php if ($isFavorito): ?>
                                <div class="favorito-badge">
                                    <i class="fas fa-heart"></i> Favorito
                                </div>
                            <?php endif; ?>
                            
                            <div class="evento-banner <?php echo empty($evento['banner']) ? 'no-image' : ''; ?>">
                                <?php if (!empty($evento['banner'])): ?>
                                    <img src="<?php echo $evento['banner']; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                        class="evento-banner-img"
                                        onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
                                <?php endif; ?>
                                <div class="banner-content">
                                    <div class="evento-tipo">
                                        <?php echo $icone; ?>
                                        <?php echo htmlspecialchars($evento['tipoEvento']); ?>
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
                                        <?php echo htmlspecialchars($evento['local']); ?>
                                    </div>
                                </div>

                                <div class="evento-descricao"><?php echo htmlspecialchars($evento['descricao']); ?></div>

                                <div class="evento-acoes">
                                    <a href="evento.php?id=<?php echo $evento['id_pk']; ?>" class="btn btn-primary">
                                        <i class="fas fa-info-circle"></i> Detalhes
                                    </a>

                                    <?php if ($usuarioAtual && !$isAdmin): ?>
                                        <?php if ($isFavorito): ?>
                                            <a href="index.php?acao=remover_favorito&id=<?php echo $evento['id_pk']; ?>"
                                                class="btn btn-remover">
                                                <i class="fas fa-heart-broken"></i> Remover
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?acao=adicionar_favorito&id=<?php echo $evento['id_pk']; ?>"
                                                class="btn btn-favorito">
                                                <i class="fas fa-heart"></i> Favoritar
                                            </a>
                                        <?php endif; ?>
                                    <?php elseif (!$usuarioAtual): ?>
                                        <a href="login.php?redirect=index.php" class="btn btn-favorito" title="Faça login para favoritar">
                                            <i class="fas fa-heart"></i> Favoritar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            
            <?php if (!empty($eventosPassados)): ?>
                <h2 class="page-title" style="margin-top: 40px;">
                    <i class="fas fa-history"></i> Eventos Realizados
                    <span class="badge"><?php echo count($eventosPassados); ?></span>
                </h2>

                <div class="eventos-lista">
                    <?php foreach ($eventosPassados as $evento):
                        $cor = getCorPorTipo($evento['tipoEvento']);
                        $icone = getIconePorTipo($evento['tipoEvento']);
                        $isFavorito = $usuarioAtual && !$isAdmin && in_array($evento['id_pk'], $favoritosIds);
                        ?>
                        <div class="evento-card passado">
                            <div class="status-badge">
                                <i class="fas fa-check-circle"></i> Realizado
                            </div>
                            
                            <?php if ($isFavorito): ?>
                                <div class="favorito-badge">
                                    <i class="fas fa-heart"></i> Favorito
                                </div>
                            <?php endif; ?>

                            <div class="evento-banner <?php echo empty($evento['banner']) ? 'no-image' : ''; ?>">
                                <?php if (!empty($evento['banner'])): ?>
                                    <img src="<?php echo $evento['banner']; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                        class="evento-banner-img"
                                        onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
                                <?php endif; ?>
                                <div class="banner-content">
                                    <div class="evento-tipo">
                                        <?php echo $icone; ?>
                                        <?php echo htmlspecialchars($evento['tipoEvento']); ?>
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
                                        <?php echo date('H:i', strtotime($evento['hora'])); ?>
                                    </div>
                                </div>

                                <div class="evento-descricao"><?php echo htmlspecialchars($evento['descricao']); ?></div>

                                <div class="evento-acoes">
                                    <a href="evento.php?id=<?php echo $evento['id_pk']; ?>" class="btn btn-primary">
                                        <i class="fas fa-info-circle"></i> Detalhes
                                    </a>
                                    
                                    <?php if ($usuarioAtual && !$isAdmin): ?>
                                        <?php if ($isFavorito): ?>
                                            <a href="index.php?acao=remover_favorito&id=<?php echo $evento['id_pk']; ?>"
                                                class="btn btn-remover">
                                                <i class="fas fa-heart-broken"></i> Remover
                                            </a>
                                        <?php else: ?>
                                            <a href="index.php?acao=adicionar_favorito&id=<?php echo $evento['id_pk']; ?>"
                                                class="btn btn-favorito">
                                                <i class="fas fa-heart"></i> Favoritar
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php require_once 'footer.php'; ?>
</body>

</html>
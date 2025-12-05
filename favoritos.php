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
    <link rel="stylesheet" href="css/favoritos.css">
    <link rel="stylesheet" href="css/footer.css">
    <style>
        /* CSS minimal apenas para ajustes específicos desta página */
        body {
            background-color: #f5f7fa;
        }

        .container {
            padding-top: 20px;
            padding-bottom: 40px;
        }
    </style>
</head>

<body>
    <?php require_once 'header.php'; ?>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem-flash mensagem-<?php echo $tipo; ?>">
            <i
                class="fas fa-<?php echo $tipo === 'sucesso' ? 'check-circle' : ($tipo === 'erro' ? 'times-circle' : 'exclamation-triangle'); ?>"></i>
            <span><?php echo htmlspecialchars($mensagem); ?></span>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="page-header">
        </div>


        <?php if (!empty($favoritos)): ?>
            <?php
            // Separar favoritos por status
            $hoje = date('Y-m-d');
            $futuros = array_filter($favoritos, function ($evento) use ($hoje) {
                return $evento['data'] >= $hoje;
            });
            $passados = array_filter($favoritos, function ($evento) use ($hoje) {
                return $evento['data'] < $hoje;
            });
            ?>
            <div class="stats-favoritos">



            </div>
        <?php endif; ?>

        <?php if (empty($favoritos)): ?>
            <div class="empty-message">
                <div class="empty-icon">
                    <i class="fas fa-heart"></i>
                </div>
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
                                    <img src="<?php echo htmlspecialchars($evento['banner']); ?>"
                                        alt="<?php echo htmlspecialchars($evento['titulo']); ?>" class="evento-banner-img"
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
                                    <a href="evento.php?id=<?php echo $evento['id_pk'] ?? $evento['id']; ?>"
                                        class="btn btn-primary">
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
                                    <img src="<?php echo htmlspecialchars($evento['banner']); ?>"
                                        alt="<?php echo htmlspecialchars($evento['titulo']); ?>" class="evento-banner-img"
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
                                    <a href="evento.php?id=<?php echo $evento['id_pk'] ?? $evento['id']; ?>"
                                        class="btn btn-primary">
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
     <?php require_once 'footer.php'; ?>
</body>


</html>
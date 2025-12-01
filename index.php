<?php
session_start();
require_once 'config.php'; // ADICIONE ESTA LINHA - remova o require_once 'config.php' se não existir

// Buscar eventos do MySQL
//$eventosFuturos = getEventosFuturos();
$eventosPassados = getEventosPassados();
$usuarioAtual = getUsuarioAtual();

// Processar ações de favoritos
if (isset($_GET['acao']) && $usuarioAtual) {
    $eventoId = $_GET['id'] ?? 0;

    if ($_GET['acao'] === 'adicionar_favorito') {
        adicionarFavorito($usuarioAtual['id'], $eventoId);
        header('Location: index.php');
        exit;
    } elseif ($_GET['acao'] === 'remover_favorito') {
        removerFavorito($usuarioAtual['id'], $eventoId);
        header('Location: index.php');
        exit;
    }
}

// Buscar favoritos do usuário atual
$favoritosUsuario = $usuarioAtual ? getEventosFavoritos($usuarioAtual['id']) : [];
$favoritosIds = array_column($favoritosUsuario, 'id_pk');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Cultural - Eventos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <?php require_once 'header.php'; ?>

    <div class="container">
        <h1 class="page-title">Próximos Eventos</h1>

        <?php if (empty($eventosFuturos)): ?>
            <div class="empty-message">
                <div class="empty-icon">📅</div>
                <h3>Nenhum evento futuro agendado</h3>
                <p>Fique de olho, em breve teremos novidades!</p>
            </div>
        <?php else: ?>
            <div class="eventos-lista">
                <?php foreach ($eventosFuturos as $evento):
                    $cor = getCorPorTipo($evento['tipoEvento']);
                    $icone = getIconePorTipo($evento['tipoEvento']);
                    $isFavorito = in_array($evento['id_pk'], $favoritosIds);
                ?>
                    <div class="evento-card" style="border-top: 4px solid <?php echo $cor; ?>">
                        <div class="evento-banner <?php echo empty($evento['banner']) ? 'no-image' : ''; ?>">
                            <?php if (!empty($evento['banner'])): ?>
                                <img src="<?php echo $evento['banner']; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                    class="evento-banner-img" onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
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
                                <a href="visualizar_evento.php?id=<?php echo $evento['id_pk']; ?>" class="btn btn-primary">
                                    <i class="fas fa-info-circle"></i> Detalhes
                                </a>

                                <?php if ($usuarioAtual): ?>
                                    <?php if ($isFavorito): ?>
                                        <a href="index.php?acao=remover_favorito&id=<?php echo $evento['id_pk']; ?>" class="btn btn-remover">
                                            <i class="fas fa-heart-broken"></i> Remover
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?acao=adicionar_favorito&id=<?php echo $evento['id_pk']; ?>" class="btn btn-favorito">
                                            <i class="fas fa-heart"></i> Favoritar
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-favorito">
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
            <h2 class="page-title" style="margin-top: 40px;">Eventos Realizados</h2>
            <div class="eventos-lista">
                <?php foreach ($eventosPassados as $evento):
                    $cor = getCorPorTipo($evento['tipoEvento']);
                    $icone = getIconePorTipo($evento['tipoEvento']);
                ?>
                    <div class="evento-card passado">
                        <div class="status-badge">
                            <i class="fas fa-check-circle"></i> Realizado
                        </div>

                        <div class="evento-banner <?php echo empty($evento['banner']) ? 'no-image' : ''; ?>">
                            <?php if (!empty($evento['banner'])): ?>
                                <img src="<?php echo $evento['banner']; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>"
                                    class="evento-banner-img" onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
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
                                <a href="visualizar_evento.php?id=<?php echo $evento['id_pk']; ?>" class="btn btn-primary" style="background: #999;">
                                    <i class="fas fa-info-circle"></i> Ver Detalhes
                                </a>
                                <div style="width: 120px; visibility: hidden;">
                                    Botão
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
require_once 'config.php';

// Processar ações
if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'adicionar_favorito':
            if (isset($_GET['id'])) {
                adicionarFavorito($_GET['id']);
                header('Location: index.php');
                exit;
            }
            break;
        case 'remover_favorito':
            if (isset($_GET['id'])) {
                removerFavorito($_GET['id']);
                header('Location: index.php');
                exit;
            }
            break;
    }
}

require_once 'header.php';
?>

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
                $cor = getCorPorTipo($evento['tipo']);
                $icone = getIconePorTipo($evento['tipo']);
                $hoje = date('Y-m-d');
                $destaque = $evento['destaque'] ?? false;
            ?>
                <div class="evento-card" data-tipo="<?php echo $evento['tipo']; ?>">
                    <?php if ($destaque): ?>
                        <div class="destaque-badge">
                            <i class="fas fa-star"></i> Destaque
                        </div>
                    <?php endif; ?>

                    <div class="evento-banner">
                        <img src="<?php echo $evento['banner']; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>" class="evento-banner-img" onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
                        <div class="banner-content">
                            <div class="evento-tipo">
                                <?php echo $icone; ?>
                                <?php echo ucfirst($evento['tipo']); ?>
                            </div>
                            <h3><?php echo $evento['titulo']; ?></h3>
                        </div>
                    </div>

                    <div class="evento-info">
                        <div class="evento-titulo"><?php echo $evento['titulo']; ?></div>

                        <div class="evento-detalhes">
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-day"></i>
                                <strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?></strong>
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-clock"></i>
                                <span class="detalhe-destaque">
                                    <i class="fas fa-bell"></i>
                                    <?php echo $evento['hora']; ?>
                                </span>
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo $evento['local']; ?>
                            </div>
                        </div>

                        <div class="evento-descricao"><?php echo $evento['descricao']; ?></div>

                        <div class="evento-acoes">
                            <a href="evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle"></i> Detalhes
                            </a>

                            <?php if (in_array($evento['id'], $_SESSION['favoritos'])): ?>
                                <a href="index.php?acao=remover_favorito&id=<?php echo $evento['id']; ?>" class="btn btn-remover">
                                    <i class="fas fa-heart-broken"></i> Remover
                                </a>
                            <?php else: ?>
                                <a href="index.php?acao=adicionar_favorito&id=<?php echo $evento['id']; ?>" class="btn btn-favorito">
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
        <h2 class="page-title eventos-realizados-title">Eventos Realizados</h2>
        <div class="eventos-lista">
            <?php foreach ($eventosPassados as $evento):
                $cor = getCorPorTipo($evento['tipo']);
                $icone = getIconePorTipo($evento['tipo']);
            ?>
                <div class="evento-card passado" data-tipo="<?php echo $evento['tipo']; ?>">
                    <div class="status-badge">
                        <i class="fas fa-check-circle"></i> Realizado
                    </div>

                    <div class="evento-banner">
                        <img src="<?php echo $evento['banner']; ?>" alt="<?php echo htmlspecialchars($evento['titulo']); ?>" class="evento-banner-img" onerror="this.style.display='none'; this.parentElement.classList.add('no-image')">
                        <div class="banner-content">
                            <div class="evento-tipo">
                                <?php echo $icone; ?>
                                <?php echo ucfirst($evento['tipo']); ?>
                            </div>
                            <h3><?php echo $evento['titulo']; ?></h3>
                        </div>
                    </div>

                    <div class="evento-info">
                        <div class="evento-titulo"><?php echo $evento['titulo']; ?></div>

                        <div class="evento-detalhes">
                            <div class="detalhe-item">
                                <i class="fas fa-calendar-day"></i>
                                <strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?></strong>
                            </div>
                            <div class="detalhe-item">
                                <i class="fas fa-clock"></i>
                                <?php echo $evento['hora']; ?>
                            </div>
                        </div>

                        <div class="evento-descricao"><?php echo $evento['descricao']; ?></div>

                        <div class="evento-acoes">
                            <a href="evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-primary btn-passado">
                                <i class="fas fa-info-circle"></i> Ver Detalhes
                            </a>
                            <div class="btn-spacer"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
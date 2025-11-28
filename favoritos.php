<?php
require_once 'config.php';

// Processar ações
if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'remover_favorito':
            if (isset($_GET['id'])) {
                removerFavorito($_GET['id']);
                header('Location: favoritos.php');
                exit;
            }
            break;
    }
}

require_once 'header.php';
?>

<div class="container">
    <h1 class="page-title">Meus Eventos Favoritos</h1>

    <?php if (empty($favoritos)): ?>
        <div class="empty-message">
            <div class="empty-icon">❤️</div>
            <h3>Nenhum evento favoritado</h3>
            <p>Adicione eventos aos favoritos para vê-los aqui!</p>
            <a href="index.php" class="btn btn-primary btn-voltar-feed">
                <i class="fas fa-arrow-left"></i> Voltar ao Feed
            </a>
        </div>
    <?php else: ?>
        <div class="eventos-lista">
            <?php
            // Ordenar favoritos por data
            usort($favoritos, function ($a, $b) {
                return strcmp($a['data'], $b['data']);
            });

            foreach ($favoritos as $evento):
                $cor = getCorPorTipo($evento['tipo']);
                $icone = getIconePorTipo($evento['tipo']);
                $hoje = date('Y-m-d');
                $passado = $evento['data'] < $hoje;
            ?>
                <div class="evento-card <?php echo $passado ? 'passado' : ''; ?>">
                    <?php if ($passado): ?>
                        <div class="status-badge">
                            <i class="fas fa-check-circle"></i> Realizado
                        </div>
                    <?php endif; ?>

                    <div class="evento-banner">
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
                        </div>

                        <div class="evento-descricao"><?php echo $evento['descricao']; ?></div>

                        <div class="evento-acoes">
                            <a href="evento.php?id=<?php echo $evento['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle"></i> Detalhes
                            </a>

                            <a href="favoritos.php?acao=remover_favorito&id=<?php echo $evento['id']; ?>" class="btn btn-remover">
                                <i class="fas fa-heart-broken"></i> Remover
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
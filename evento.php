<?php
require_once 'config.php';

// Verificar se o ID do evento foi passado
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$evento = getEventoById($_GET['id']);

if (!$evento) {
    header('Location: index.php');
    exit;
}

// Processar ações de favoritos
if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'adicionar_favorito':
            adicionarFavorito($evento['id']);
            break;
        case 'remover_favorito':
            removerFavorito($evento['id']);
            break;
    }
    // Redirecionar para evitar reenvio do formulário
    header("Location: evento.php?id=" . $evento['id']);
    exit;
}

require_once 'header.php';

$cor = getCorPorTipo($evento['tipo']);
$icone = getIconePorTipo($evento['tipo']);
$hoje = date('Y-m-d');
$passado = $evento['data'] < $hoje;
?>

<div class="container">
    <div class="evento-detalhe">
        <div class="detalhe-banner">
            <div class="detalhe-banner-content">
                <div class="evento-tipo">
                    <?php echo $icone; ?>
                    <?php echo ucfirst($evento['tipo']); ?>
                </div>
                <h1 class="evento-titulo"><?php echo $evento['titulo']; ?></h1>
                <div class="banner-info">
                    <span class="info-badge">
                        <i class="fas fa-calendar-day"></i>
                        <?php echo date('d/m/Y', strtotime($evento['data'])); ?>
                    </span>
                    <span class="info-badge">
                        <i class="fas fa-clock"></i>
                        <?php echo $evento['hora']; ?>
                    </span>
                    <span class="info-badge">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo $evento['local']; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="detalhe-info">
            <?php if ($passado): ?>
                <div class="status-badge evento-realizado">
                    <i class="fas fa-check-circle"></i> Evento Realizado
                </div>
            <?php endif; ?>

            <div class="info-item">
                <div class="info-label">Descrição:</div>
                <div class="info-value"><?php echo $evento['descricao']; ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Organizador:</div>
                <div class="info-value"><?php echo $evento['responsavel']; ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Data e Hora:</div>
                <div class="info-value">
                    <strong><?php echo date('d/m/Y', strtotime($evento['data'])); ?> às <?php echo $evento['hora']; ?></strong>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Local:</div>
                <div class="info-value"><?php echo $evento['local']; ?></div>
            </div>

            <div class="info-item">
                <div class="info-label">Tipo de Evento:</div>
                <div class="info-value"><?php echo ucfirst($evento['tipo']); ?></div>
            </div>

            <div class="evento-acoes">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Feed
                </a>

                <?php if (!$passado): ?>
                    <?php if (in_array($evento['id'], $_SESSION['favoritos'])): ?>
                        <a href="evento.php?acao=remover_favorito&id=<?php echo $evento['id']; ?>" class="btn btn-remover">
                            <i class="fas fa-heart-broken"></i> Remover dos Favoritos
                        </a>
                    <?php else: ?>
                        <a href="evento.php?acao=adicionar_favorito&id=<?php echo $evento['id']; ?>" class="btn btn-favorito">
                            <i class="fas fa-heart"></i> Adicionar aos Favoritos
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
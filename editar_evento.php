<?php
session_start();
require_once 'config.php';

// Verificar se é admin
if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['tipo'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit();
}

// Verificar se o ID do evento foi passado
if (!isset($_GET['id'])) {
    header('Location: admin_eventos.php');
    exit();
}

$eventoId = intval($_GET['id']);

// Buscar o evento do banco
function getEventoByIdParaEdicao($id) {
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
                e.categoria_fk,
                e.usuario_fk,
                c.titulo as categoria_titulo
            FROM evento e 
            LEFT JOIN categoria c ON e.categoria_fk = c.id_pk 
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

// Buscar todas as categorias
function getCategorias() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT id_pk, titulo FROM categoria ORDER BY titulo");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar categorias: " . $e->getMessage());
        return [];
    }
}

// Buscar o evento
$evento = getEventoByIdParaEdicao($eventoId);
$categorias = getCategorias();

if (!$evento) {
    $_SESSION['mensagem'] = 'Evento não encontrado!';
    $_SESSION['mensagem_tipo'] = 'erro';
    header('Location: admin_eventos.php');
    exit();
}

$errors = [];
$success = '';

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação e processamento dos dados
    $titulo = trim($_POST['titulo'] ?? '');
    $data = trim($_POST['data'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $local = trim($_POST['local'] ?? '');
    $tipoEvento = trim($_POST['tipoEvento'] ?? '');
    $responsavel = trim($_POST['responsavel'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $categoria_fk = intval($_POST['categoria_fk'] ?? 0);
    
    // Validações básicas
    if (empty($titulo)) {
        $errors[] = 'Título do evento é obrigatório';
    }
    if (empty($data)) {
        $errors[] = 'Data do evento é obrigatória';
    }
    if (empty($hora)) {
        $errors[] = 'Hora do evento é obrigatória';
    }
    if (empty($local)) {
        $errors[] = 'Local do evento é obrigatório';
    }
    if (empty($descricao)) {
        $errors[] = 'Descrição do evento é obrigatória';
    }
    if (empty($tipoEvento)) {
        $errors[] = 'Tipo de evento é obrigatório';
    }
    if (empty($responsavel)) {
        $errors[] = 'Responsável pelo evento é obrigatório';
    }

    // Processar upload da imagem se houver
    $banner = $evento['banner']; // Mantém o banner atual por padrão
    
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['banner'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (in_array($file['type'], $allowedTypes)) {
            if ($file['size'] <= $maxSize) {
                // Criar diretório se não existir
                $uploadDir = 'uploads/banners/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Gerar nome único para o arquivo
                $fileName = uniqid() . '_' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $banner = $filePath;
                    
                    // Remover banner antigo se existir
                    if (!empty($evento['banner']) && file_exists($evento['banner'])) {
                        unlink($evento['banner']);
                    }
                } else {
                    $errors[] = 'Erro ao fazer upload da imagem';
                }
            } else {
                $errors[] = 'Imagem muito grande. Tamanho máximo: 2MB';
            }
        } else {
            $errors[] = 'Formato de imagem inválido. Use JPG, PNG ou GIF';
        }
    } elseif ($_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Erro no upload da imagem: ' . $_FILES['banner']['error'];
    }

    // Se não há erros, atualizar no banco
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE evento 
                SET titulo = ?, 
                    data = ?, 
                    hora = ?, 
                    local = ?, 
                    descricao = ?, 
                    tipoEvento = ?, 
                    responsavel = ?, 
                    banner = ?, 
                    categoria_fk = ?
                WHERE id_pk = ?
            ");
            
            $stmt->execute([
                $titulo,
                $data,
                $hora,
                $local,
                $descricao,
                $tipoEvento,
                $responsavel,
                $banner,
                $categoria_fk > 0 ? $categoria_fk : null,
                $eventoId
            ]);
            
            $success = 'Evento atualizado com sucesso!';
            
            // Atualizar os dados do evento na variável para exibir
            $evento['titulo'] = $titulo;
            $evento['data'] = $data;
            $evento['hora'] = $hora;
            $evento['local'] = $local;
            $evento['tipoEvento'] = $tipoEvento;
            $evento['responsavel'] = $responsavel;
            $evento['descricao'] = $descricao;
            $evento['banner'] = $banner;
            $evento['categoria_fk'] = $categoria_fk;
            
            // Atualizar também a categoria_titulo
            foreach ($categorias as $categoria) {
                if ($categoria['id_pk'] == $categoria_fk) {
                    $evento['categoria_titulo'] = $categoria['titulo'];
                    break;
                }
            }
            
        } catch (PDOException $e) {
            error_log("Erro ao atualizar evento: " . $e->getMessage());
            $errors[] = 'Erro ao atualizar evento: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento - Agenda Cultural</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/cadastroEvento.css">
    <link rel="stylesheet" href="css/editar_evento.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="admin-header">
                <h1><i class="fas fa-edit"></i> Editar Evento</h1>
                <p>Atualize os dados do evento #<?php echo $evento['id_pk']; ?></p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> 
                    <strong>Erros encontrados:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form class="register-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="titulo">Título do Evento *</label>
                        <input type="text" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($evento['titulo']); ?>" 
                               placeholder="Digite o título do evento" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data">Data *</label>
                        <input type="date" id="data" name="data" 
                               value="<?php echo htmlspecialchars($evento['data']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora">Hora *</label>
                        <input type="time" id="hora" name="hora" 
                               value="<?php echo htmlspecialchars($evento['hora']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="local">Local *</label>
                        <input type="text" id="local" name="local" 
                               value="<?php echo htmlspecialchars($evento['local']); ?>" 
                               placeholder="Local do evento" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipoEvento">Tipo de Evento *</label>
                        <select id="tipoEvento" name="tipoEvento" class="form-select" required>
                            <option value="">Selecione o tipo</option>
                            <option value="Música" <?php echo ($evento['tipoEvento'] ?? '') === 'Música' ? 'selected' : ''; ?>>Música</option>
                            <option value="Educação" <?php echo ($evento['tipoEvento'] ?? '') === 'Educação' ? 'selected' : ''; ?>>Educação</option>
                            <option value="Esportes" <?php echo ($evento['tipoEvento'] ?? '') === 'Esportes' ? 'selected' : ''; ?>>Esportes</option>
                            <option value="Exposição" <?php echo ($evento['tipoEvento'] ?? '') === 'Exposição' ? 'selected' : ''; ?>>Exposição</option>
                            <option value="Show" <?php echo ($evento['tipoEvento'] ?? '') === 'Show' ? 'selected' : ''; ?>>Show</option>
                            <option value="Festival" <?php echo ($evento['tipoEvento'] ?? '') === 'Festival' ? 'selected' : ''; ?>>Festival</option>
                            <option value="Palestra" <?php echo ($evento['tipoEvento'] ?? '') === 'Palestra' ? 'selected' : ''; ?>>Palestra</option>
                            <option value="Workshop" <?php echo ($evento['tipoEvento'] ?? '') === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="teatro" <?php echo ($evento['tipoEvento'] ?? '') === 'teatro' ? 'selected' : ''; ?>>Teatro</option>
                            <option value="Feira" <?php echo ($evento['tipoEvento'] ?? '') === 'Feira' ? 'selected' : ''; ?>>Feira</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="responsavel">Responsável *</label>
                        <input type="text" id="responsavel" name="responsavel" 
                               value="<?php echo htmlspecialchars($evento['responsavel']); ?>" 
                               placeholder="Nome do responsável pelo evento" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="categoria_fk">Categoria</label>
                        <select id="categoria_fk" name="categoria_fk" class="form-select">
                            <option value="">Sem categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id_pk']; ?>" 
                                    <?php echo ($evento['categoria_fk'] ?? 0) == $categoria['id_pk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="descricao">Descrição *</label>
                        <textarea id="descricao" name="descricao" 
                                  placeholder="Descreva o evento"
                                  rows="6" required><?php echo htmlspecialchars($evento['descricao']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="banner">Banner do Evento</label>
                        <input type="file" id="banner" name="banner" 
                               accept="image/*" class="file-input">
                        <small class="file-help">Formatos: JPG, PNG, GIF (Max: 2MB)</small>
                        
                        <?php if (!empty($evento['banner'])): ?>
                            <div class="current-banner">
                                <p><strong>Banner atual:</strong></p>
                                <img src="<?php echo htmlspecialchars($evento['banner']); ?>" 
                                     alt="Banner atual" 
                                     onerror="this.style.display='none';">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="cadastrarEvento-btn">
                        <i class="fas fa-save"></i> Atualizar Evento
                    </button>
                    <a href="admin_eventos.php" class="voltar-btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="event-info">
                    <p><strong>ID do Evento:</strong> #<?php echo $evento['id_pk']; ?></p>
                    <p><strong>Categoria Atual:</strong> <?php echo htmlspecialchars($evento['categoria_titulo'] ?? 'Sem categoria'); ?></p>
                    <p><strong>Criado por:</strong> Usuário ID: <?php echo $evento['usuario_fk'] ?? 'Desconhecido'; ?></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Verifica se o usuário é admin
$usuarioAtual = $_SESSION['usuario'];
if ($usuarioAtual['tipo'] !== 'admin') {
    require_once 'header.php';
    ?>
    <div class="container">
        <div class="access-denied">
            <i class="fas fa-ban"></i>
            <h1>Acesso Restrito</h1>
            <p>Apenas administradores podem cadastrar eventos no sistema.</p>
            
            <div class="user-info">
                <p><i class="fas fa-user"></i> Você está logado como: <strong><?php echo htmlspecialchars($usuarioAtual['nome']); ?></strong></p>
                <p><i class="fas fa-user-tag"></i> Tipo de conta: <strong><?php echo htmlspecialchars(ucfirst($usuarioAtual['tipo'])); ?></strong></p>
            </div>
            
            <div class="btn-group">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Voltar para Início
                </a>
                <a href="perfil.php" class="btn btn-secondary">
                    <i class="fas fa-user"></i> Ver Meu Perfil
                </a>
            </div>
        </div>
    </div>
    <?php
    // Remova o require_once 'footer.php' se não existir
    echo '</body></html>';
    exit;
}

$errors = [];
$success = '';
$formData = [
    'titulo' => '',
    'data' => '',
    'hora' => '',
    'local' => '',
    'descricao' => '',
    'tipo_evento' => '',
    'responsavel' => ''
];

// Processa o formulário de cadastro de evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['titulo'] = htmlspecialchars(trim($_POST['titulo']));
    $formData['data'] = htmlspecialchars(trim($_POST['data']));
    $formData['hora'] = htmlspecialchars(trim($_POST['hora']));
    $formData['local'] = htmlspecialchars(trim($_POST['local']));
    $formData['descricao'] = htmlspecialchars(trim($_POST['descricao']));
    $formData['tipo_evento'] = htmlspecialchars(trim($_POST['tipo_evento']));
    $formData['responsavel'] = htmlspecialchars(trim($_POST['responsavel']));
    $categoria_fk = $_POST['categoria_fk'] ?? 1;
    
    // Validações
    if (empty($formData['titulo'])) $errors[] = 'Título do evento é obrigatório';
    if (empty($formData['data'])) {
        $errors[] = 'Data do evento é obrigatória';
    } elseif (strtotime($formData['data']) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Data do evento não pode ser no passado';
    }
    if (empty($formData['hora'])) $errors[] = 'Hora do evento é obrigatória';
    if (empty($formData['local'])) $errors[] = 'Local do evento é obrigatório';
    if (empty($formData['descricao'])) $errors[] = 'Descrição do evento é obrigatória';
    if (empty($formData['tipo_evento'])) $errors[] = 'Tipo de evento é obrigatório';
    if (empty($formData['responsavel'])) $errors[] = 'Responsável pelo evento é obrigatório';
    if (empty($categoria_fk)) $errors[] = 'Categoria é obrigatória';
    
    // Upload da imagem (AGORA OBRIGATÓRIO)
    $bannerNome = null;
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['banner']['tmp_name'];
            $fileName = $_FILES['banner']['name'];
            $fileSize = $_FILES['banner']['size'];
            
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                if ($fileSize <= 2097152) {
                    $newFileName = uniqid() . '_' . date('Ymd_His') . '.' . $fileExtension;
                    $uploadFileDir = 'uploads/banners/';
                    
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0777, true);
                    }
                    
                    $dest_path = $uploadFileDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $bannerNome = $dest_path;
                    } else {
                        $errors[] = 'Erro ao fazer upload da imagem';
                    }
                } else {
                    $errors[] = 'Tamanho da imagem excede 2MB';
                }
            } else {
                $errors[] = 'Formato não permitido. Use JPG, PNG, GIF ou WebP';
            }
        } else {
            $errors[] = 'Erro no upload do banner: ' . getUploadError($_FILES['banner']['error']);
        }
    } else {
        // Banner é obrigatório
        $errors[] = 'Banner do evento é obrigatório';
    }
    
    // Salva no banco se não houver erros
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO evento 
                (titulo, data, hora, local, descricao, tipoEvento, responsavel, banner, categoria_fk, usuario_fk) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $sucesso = $stmt->execute([
                $formData['titulo'],
                $formData['data'],
                $formData['hora'],
                $formData['local'],
                $formData['descricao'],
                $formData['tipo_evento'],
                $formData['responsavel'],
                $bannerNome,
                $categoria_fk,
                $usuarioAtual['id']
            ]);
            
            if ($sucesso) {
                $eventoId = $pdo->lastInsertId();
                // Redireciona imediatamente para o index.php com mensagem de sucesso
                $_SESSION['mensagem'] = '✅ Evento cadastrado com sucesso!';
                $_SESSION['mensagem_tipo'] = 'sucesso';
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Erro ao salvar evento no banco de dados';
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Erro no banco de dados: ' . $e->getMessage();
            error_log("Erro ao cadastrar evento: " . $e->getMessage());
        }
    }
}

// Função para mensagens de erro do upload
function getUploadError($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'O arquivo excede o tamanho máximo permitido';
        case UPLOAD_ERR_FORM_SIZE:
            return 'O arquivo excede o tamanho máximo do formulário';
        case UPLOAD_ERR_PARTIAL:
            return 'O upload do arquivo foi feito parcialmente';
        case UPLOAD_ERR_NO_FILE:
            return 'Nenhum arquivo foi enviado';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Pasta temporária não encontrada';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Falha ao escrever o arquivo no disco';
        case UPLOAD_ERR_EXTENSION:
            return 'Uma extensão do PHP interrompeu o upload';
        default:
            return 'Erro desconhecido no upload';
    }
}

// Busca categorias do banco
$categorias = [];
try {
    $stmt = $pdo->query("SELECT * FROM categoria ORDER BY titulo");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao buscar categorias: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Evento - Sistema de Eventos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="/AgendaCulturalWeb/css/cadastroEvento.css">
</head>
<body>
    
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1><i class="fas fa-calendar-plus"></i> Cadastrar Evento</h1>
                <p>Preencha os dados do evento</p>
                <p><small><i class="fas fa-user"></i> Usuário: <?php echo htmlspecialchars($usuarioAtual['nome'] ?? ''); ?></small></p>
                <p class="admin-badge"><i class="fas fa-crown"></i> Administrador</p>
                <p class="text-danger"><small><i class="fas fa-exclamation-circle"></i> Todos os campos são obrigatórios</small></p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <div class="error-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erros encontrados:</strong>
                    </div>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form class="register-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="titulo"><i class="fas fa-heading"></i> Título do Evento</label>
                        <input type="text" id="titulo" name="titulo" 
                               value="<?php echo htmlspecialchars($formData['titulo']); ?>" 
                               placeholder="Digite o título do evento" 
                               required
                               class="<?php if (!empty($errors) && empty($formData['titulo'])): ?>input-error<?php endif; ?>">
                        <?php if (!empty($errors) && empty($formData['titulo'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data"><i class="far fa-calendar-alt"></i> Data</label>
                        <input type="date" id="data" name="data" 
                               value="<?php echo htmlspecialchars($formData['data']); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" 
                               required
                               class="<?php if (!empty($errors) && empty($formData['data'])): ?>input-error<?php endif; ?>">
                        <?php if (!empty($errors) && empty($formData['data'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora"><i class="far fa-clock"></i> Hora</label>
                        <input type="time" id="hora" name="hora" 
                               value="<?php echo htmlspecialchars($formData['hora']); ?>" 
                               required
                               class="<?php if (!empty($errors) && empty($formData['hora'])): ?>input-error<?php endif; ?>">
                        <?php if (!empty($errors) && empty($formData['hora'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="local"><i class="fas fa-map-marker-alt"></i> Local</label>
                        <input type="text" id="local" name="local" 
                               value="<?php echo htmlspecialchars($formData['local']); ?>" 
                               placeholder="Local do evento" 
                               required
                               class="<?php if (!empty($errors) && empty($formData['local'])): ?>input-error<?php endif; ?>">
                        <?php if (!empty($errors) && empty($formData['local'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_evento"><i class="fas fa-tags"></i> Tipo de Evento</label>
                        <select id="tipo_evento" name="tipo_evento" class="form-select <?php if (!empty($errors) && empty($formData['tipo_evento'])): ?>input-error<?php endif; ?>" 
                                required>
                            <option value="">Selecione o tipo</option>
                            <option value="Música" <?php echo $formData['tipo_evento'] === 'Música' ? 'selected' : ''; ?>>Música</option>
                            <option value="Educação" <?php echo $formData['tipo_evento'] === 'Educação' ? 'selected' : ''; ?>>Educação</option>
                            <option value="Cultura" <?php echo $formData['tipo_evento'] === 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
                            <option value="Esportes" <?php echo $formData['tipo_evento'] === 'Esportes' ? 'selected' : ''; ?>>Esportes</option>
                            <option value="Feira" <?php echo $formData['tipo_evento'] === 'Feira' ? 'selected' : ''; ?>>Feira</option>
                            <option value="teatro" <?php echo $formData['tipo_evento'] === 'teatro' ? 'selected' : ''; ?>>Teatro</option>
                            <option value="workshop" <?php echo $formData['tipo_evento'] === 'workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="Palestra" <?php echo $formData['tipo_evento'] === 'Palestra' ? 'selected' : ''; ?>>Palestra</option>
                            <option value="Exposição" <?php echo $formData['tipo_evento'] === 'Exposição' ? 'selected' : ''; ?>>Exposição</option>
                            <option value="Show" <?php echo $formData['tipo_evento'] === 'Show' ? 'selected' : ''; ?>>Show</option>
                        </select>
                        <?php if (!empty($errors) && empty($formData['tipo_evento'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="responsavel"><i class="fas fa-user-tie"></i> Responsável</label>
                        <input type="text" id="responsavel" name="responsavel" 
                               value="<?php echo htmlspecialchars($formData['responsavel']); ?>" 
                               placeholder="Nome do responsável" 
                               required
                               class="<?php if (!empty($errors) && empty($formData['responsavel'])): ?>input-error<?php endif; ?>">
                        <?php if (!empty($errors) && empty($formData['responsavel'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_fk"><i class="fas fa-layer-group"></i> Categoria</label>
                        <select id="categoria_fk" name="categoria_fk" 
                                class="form-select <?php if (!empty($errors) && empty($categoria_fk)): ?>input-error<?php endif; ?>" 
                                required>
                            <option value="">Selecione a categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id_pk']; ?>" 
                                    <?php echo ($categoria_fk ?? '') == $categoria['id_pk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors) && empty($categoria_fk)): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                        <textarea id="descricao" name="descricao" 
                                  placeholder="Descreva o evento (local, horário, público-alvo, etc.)"
                                  rows="5" 
                                  required
                                  class="<?php if (!empty($errors) && empty($formData['descricao'])): ?>input-error<?php endif; ?>"><?php echo htmlspecialchars($formData['descricao']); ?></textarea>
                        <?php if (!empty($errors) && empty($formData['descricao'])): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Este campo é obrigatório
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="banner"><i class="fas fa-image"></i> Banner do Evento</label>
                        
                        <div class="file-upload-wrapper">
                            <span class="custom-file-button">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                Selecionar Banner
                                <input type="file" id="banner" name="banner" 
                                       accept="image/*" 
                                       class="file-input"
                                       required>
                            </span>
                        </div>
                        
                        <!-- Mostra o nome do arquivo selecionado -->
                        <?php if (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE): ?>
                            <?php 
                            $fileName = htmlspecialchars($_FILES['banner']['name']);
                            $fileSize = $_FILES['banner']['size'];
                            $fileSizeMB = round($fileSize / 1048576, 2);
                            ?>
                            <span class="file-name">
                                <i class="fas fa-file-image"></i> 
                                <?php echo $fileName; ?> 
                                <small>(<?php echo $fileSizeMB; ?> MB)</small>
                            </span>
                        <?php endif; ?>
                        
                        <?php if (in_array('Banner do evento é obrigatório', $errors)): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Banner do evento é obrigatório
                            </small>
                        <?php elseif (in_array('Formato não permitido. Use JPG, PNG, GIF ou WebP', $errors)): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Formato não permitido. Use JPG, PNG, GIF ou WebP
                            </small>
                        <?php elseif (in_array('Tamanho da imagem excede 2MB', $errors)): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Tamanho máximo: 2MB
                            </small>
                        <?php elseif (in_array('Erro ao fazer upload da imagem', $errors)): ?>
                            <small class="error-text">
                                <i class="fas fa-exclamation-circle"></i> Erro no upload da imagem
                            </small>
                        <?php endif; ?>
                        
                        <small class="file-help">
                            <i class="fas fa-info-circle"></i> Formatos permitidos: JPG, PNG, GIF, WebP (Tamanho máximo: 2MB)
                        </small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="cadastrarEvento-btn">
                        <i class="fas fa-save"></i> Cadastrar Evento
                    </button>
                    <a href="index.php" class="voltar-btn">
                        <i class="fas fa-arrow-left"></i> Voltar para Página Principal
                    </a>
                </div>
            </form>
            
        </div>
    </div>
    

</body>
</html>
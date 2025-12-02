<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado


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
    
    // Upload da imagem
    $bannerNome = null;
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
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
                $_SESSION['usuario']['id']
            ]);
            
            if ($sucesso) {
                $success = 'Evento cadastrado com sucesso!';
                // Limpa o formulário
                $formData = array_fill_keys(array_keys($formData), '');
            } else {
                $errors[] = 'Erro ao salvar evento no banco de dados';
            }
            
        } catch (PDOException $e) {
            $errors[] = 'Erro no banco de dados: ' . $e->getMessage();
            error_log("Erro ao cadastrar evento: " . $e->getMessage());
        }
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
    <link rel="stylesheet" href="css/cadastroEvento.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1><i class="fas fa-calendar-plus"></i> Cadastrar Evento</h1>
                <p>Preencha os dados do evento</p>
                <p><small><i class="fas fa-user"></i> Usuário: <?php echo htmlspecialchars($_SESSION['usuario']['nome'] ?? ''); ?></small></p>
            </div>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    <small>O evento foi salvo no banco de dados com sucesso.</small>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <strong><i class="fas fa-exclamation-triangle"></i> Erros encontrados:</strong>
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
                               placeholder="Digite o título do evento" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="data"><i class="far fa-calendar-alt"></i> Data</label>
                        <input type="date" id="data" name="data" 
                               value="<?php echo htmlspecialchars($formData['data']); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora"><i class="far fa-clock"></i> Hora</label>
                        <input type="time" id="hora" name="hora" 
                               value="<?php echo htmlspecialchars($formData['hora']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="local"><i class="fas fa-map-marker-alt"></i> Local</label>
                        <input type="text" id="local" name="local" 
                               value="<?php echo htmlspecialchars($formData['local']); ?>" 
                               placeholder="Local do evento" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo_evento"><i class="fas fa-tags"></i> Tipo de Evento</label>
                        <select id="tipo_evento" name="tipo_evento" class="form-select" required>
                            <option value="">Selecione o tipo</option>
                            <option value="Música" <?php echo $formData['tipo_evento'] === 'Música' ? 'selected' : ''; ?>>Música</option>
                            <option value="Educação" <?php echo $formData['tipo_evento'] === 'Educação' ? 'selected' : ''; ?>>Educação</option>
                            <option value="Cultura" <?php echo $formData['tipo_evento'] === 'Cultura' ? 'selected' : ''; ?>>Cultura</option>
                            <option value="Esportes" <?php echo $formData['tipo_evento'] === 'Esportes' ? 'selected' : ''; ?>>Esportes</option>
                            <option value="Feira" <?php echo $formData['tipo_evento'] === 'Feira' ? 'selected' : ''; ?>>Feira</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="responsavel"><i class="fas fa-user-tie"></i> Responsável</label>
                        <input type="text" id="responsavel" name="responsavel" 
                               value="<?php echo htmlspecialchars($formData['responsavel']); ?>" 
                               placeholder="Nome do responsável" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria_fk"><i class="fas fa-layer-group"></i> Categoria</label>
                        <select id="categoria_fk" name="categoria_fk" class="form-select" required>
                            <option value="">Selecione a categoria</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo $categoria['id_pk']; ?>" 
                                    <?php echo ($categoria_fk ?? '') == $categoria['id_pk'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($categoria['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="descricao"><i class="fas fa-align-left"></i> Descrição</label>
                        <textarea id="descricao" name="descricao" 
                                  placeholder="Descreva o evento (local, horário, público-alvo, etc.)"
                                  rows="5" required><?php echo htmlspecialchars($formData['descricao']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="banner"><i class="fas fa-image"></i> Banner do Evento (opcional)</label>
                        <input type="file" id="banner" name="banner" 
                               accept="image/*" class="file-input">
                        <small class="file-help">Formatos: JPG, PNG, GIF, WebP (Max: 2MB)</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="cadastrarEvento-btn" id="submitBtn">
                        <i class="fas fa-save"></i> Cadastrar Evento
                    </button>
                    <a href="index.php" class="voltar-btn">
                        <i class="fas fa-arrow-left"></i> Voltar para Home
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Adiciona loading ao botão de submit
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
        });

        // Validação em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.style.borderColor = '#dc3545';
                    } else {
                        this.style.borderColor = '#28a745';
                    }
                });
            });
        });
    </script>
</body>
</html>
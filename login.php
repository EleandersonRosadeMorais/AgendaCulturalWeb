<?php
session_start();



$error = '';
$username = '';


?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agenda Cultural</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Agenda Cultural</h1>
                <p>Faça login para acessar sua conta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="username">Usuário</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>" 
                           placeholder="Digite seu usuário" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" 
                           placeholder="Digite sua senha" required>
                </div>
                
                
                <button type="submit" class="login-btn">Entrar</button>
                
              
                
                <a href="cadastroUsuario.php" class="register-btn">Cadastrar nova conta</a>
            </form>
            
            <div class="login-footer">
                <p>© 2025 Agenda Cultural. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
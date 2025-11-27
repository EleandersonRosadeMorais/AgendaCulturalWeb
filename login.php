<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agenda Cultural</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/index.css">
    <!-- Firebase App e Auth -->
    <script type="module" src="js/firebase-config.js"></script>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Agenda Cultural</h1>
                <p>Faça login para acessar sua conta</p>
            </div>

            <!-- Mensagem de erro/sucesso -->
            <div id="message" class="error-message" style="display: none;"></div>

            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email"
                        placeholder="Digite seu e-mail" required>
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password"
                        placeholder="Digite sua senha" required>
                </div>

                <button type="submit" class="login-btn" id="loginButton">
                    <span id="buttonText">Entrar</span>
                    <span id="loadingSpinner" style="display: none;">Carregando...</span>
                </button>

                <div class="divider">
                    <span>ou</span>
                </div>

                <a href="cadastroUsuario.php" class="register-btn">Cadastrar nova conta</a>
            </form>

            <div class="login-footer">
                <p>© 2025 Agenda Cultural. Todos os direitos reservados.</p>
            </div>
        </div>
    </div>

    <script type="module">
        import {
            loginUsuario,
            observarEstadoAuth
        } from './js/auth.js';
        import {
            mostrarMensagem
        } from './js/utils.js';

        // Elementos do DOM
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const loginButton = document.getElementById('loginButton');
        const buttonText = document.getElementById('buttonText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const messageDiv = document.getElementById('message');

        // Verificar se já está logado
        observarEstadoAuth((estado) => {
            if (estado.logado) {
                console.log('Usuário já está logado:', estado.usuario);
                window.location.href = 'index.php';
            }
        });

        // Função para mostrar mensagem
        function showMessage(text, type = 'error') {
            messageDiv.textContent = text;
            messageDiv.className = type === 'error' ? 'error-message' : 'success-message';
            messageDiv.style.display = 'block';

            // Auto-esconder após 5 segundos
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 5000);
        }

        // Função para mostrar loading
        function showLoading(show) {
            if (show) {
                buttonText.style.display = 'none';
                loadingSpinner.style.display = 'inline';
                loginButton.disabled = true;
            } else {
                buttonText.style.display = 'inline';
                loadingSpinner.style.display = 'none';
                loginButton.disabled = false;
            }
        }

        // Submissão do formulário
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = emailInput.value.trim();
            const password = passwordInput.value;

            // Validação básica
            if (!email || !password) {
                showMessage('Por favor, preencha todos os campos.');
                return;
            }

            if (!validateEmail(email)) {
                showMessage('Por favor, insira um e-mail válido.');
                return;
            }

            showLoading(true);
            showMessage('', 'error'); // Limpar mensagens

            try {
                console.log('Tentando login com:', email);

                const resultado = await loginUsuario(email, password);

                if (resultado.success) {
                    showMessage('Login realizado com sucesso! Redirecionando...', 'success');
                    console.log('Login bem-sucedido:', resultado.user);

                    // Redirecionar após breve delay
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showMessage(getErrorMessage(resultado.error));
                    console.error('Erro no login:', resultado.error);
                }
            } catch (error) {
                showMessage('Erro inesperado. Tente novamente.');
                console.error('Erro no login:', error);
            } finally {
                showLoading(false);
            }
        });

        // Validação de e-mail
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Traduzir mensagens de erro do Firebase
        function getErrorMessage(error) {
            const errorMessages = {
                'auth/invalid-email': 'E-mail inválido.',
                'auth/user-disabled': 'Esta conta foi desativada.',
                'auth/user-not-found': 'E-mail não encontrado.',
                'auth/wrong-password': 'Senha incorreta.',
                'auth/too-many-requests': 'Muitas tentativas. Tente novamente mais tarde.',
                'auth/network-request-failed': 'Erro de conexão. Verifique sua internet.'
            };

            return errorMessages[error] || 'Erro ao fazer login. Tente novamente.';
        }

        // Limpar mensagem ao digitar
        emailInput.addEventListener('input', () => {
            messageDiv.style.display = 'none';
        });

        passwordInput.addEventListener('input', () => {
            messageDiv.style.display = 'none';
        });
    </script>
</body>

</html>
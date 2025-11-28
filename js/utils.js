import { auth } from './firebase-config.js';

// Utilitários gerais
export function formatarData(dataString) {
    const data = new Date(dataString + 'T00:00:00');
    return data.toLocaleDateString('pt-BR');
}

export function isAdmin(usuario) {
    return usuario && usuario.tipo === 'admin';
}

export function getUserUID() {
    return auth.currentUser ? auth.currentUser.uid : null;
}

export function mostrarLoading() {
    document.body.style.cursor = 'wait';
}

export function esconderLoading() {
    document.body.style.cursor = 'default';
}

export function mostrarMensagem(mensagem, tipo = 'success') {
    // Criar elemento de mensagem
    const mensagemEl = document.createElement('div');
    mensagemEl.className = `mensagem ${tipo}`;
    mensagemEl.innerHTML = `
    <span>${mensagem}</span>
    <button onclick="this.parentElement.remove()">&times;</button>
  `;

    // Adicionar estilos
    mensagemEl.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 600;
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 400px;
    ${tipo === 'success' ? 'background: #4CAF50;' : ''}
    ${tipo === 'error' ? 'background: #f44336;' : ''}
    ${tipo === 'warning' ? 'background: #ff9800;' : ''}
  `;

    mensagemEl.querySelector('button').style.cssText = `
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 0;
    width: 20px;
    height: 20px;
  `;

    document.body.appendChild(mensagemEl);

    // Auto-remover após 5 segundos
    setTimeout(() => {
        if (mensagemEl.parentElement) {
            mensagemEl.remove();
        }
    }, 5000);
}
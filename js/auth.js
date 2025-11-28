// js/auth.js - Versão atualizada
import { auth, db } from './firebase-config.js';
import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    onAuthStateChanged
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import {
    doc,
    setDoc,
    getDoc
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

// Login de usuário
export async function loginUsuario(email, senha) {
    try {
        console.log('Iniciando login para:', email);
        const userCredential = await signInWithEmailAndPassword(auth, email, senha);
        const user = userCredential.user;
        console.log('Login Firebase OK, UID:', user.uid);

        // Buscar dados adicionais do usuário no Firestore
        const userDoc = await getDoc(doc(db, "usuarios", user.uid));

        if (userDoc.exists()) {
            const userData = userDoc.data();
            console.log('Dados do usuário encontrados:', userData);

            return {
                success: true,
                user: {
                    uid: user.uid,
                    email: user.email,
                    ...userData
                }
            };
        } else {
            console.log('Usuário não encontrado no Firestore, criando perfil básico...');
            // Criar perfil básico se não existir
            await setDoc(doc(db, "usuarios", user.uid), {
                nome: user.email.split('@')[0], // Nome baseado no email
                email: user.email,
                tipo: 'usuario',
                dataCriacao: new Date()
            });

            return {
                success: true,
                user: {
                    uid: user.uid,
                    email: user.email,
                    nome: user.email.split('@')[0],
                    tipo: 'usuario'
                }
            };
        }
    } catch (error) {
        console.error("Erro detalhado no login:", error);
        return {
            success: false,
            error: error.code || error.message
        };
    }
}

// Observador de estado de autenticação
export function observarEstadoAuth(callback) {
    return onAuthStateChanged(auth, async (user) => {
        console.log('Estado de autenticação alterado:', user ? 'Logado' : 'Deslogado');
        if (user) {
            try {
                // Buscar dados adicionais do usuário
                const userDoc = await getDoc(doc(db, "usuarios", user.uid));
                const userData = userDoc.data();

                callback({
                    logado: true,
                    usuario: {
                        uid: user.uid,
                        email: user.email,
                        ...userData
                    }
                });
            } catch (error) {
                console.error('Erro ao buscar dados do usuário:', error);
                callback({
                    logado: true,
                    usuario: { uid: user.uid, email: user.email }
                });
            }
        } else {
            callback({ logado: false, usuario: null });
        }
    });
}

// Cadastro de usuário (mantido para referência)
export async function cadastrarUsuario(email, senha, dadosUsuario) {
    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, senha);
        const user = userCredential.user;

        await setDoc(doc(db, "usuarios", user.uid), {
            nome: dadosUsuario.nome,
            email: email,
            tipo: dadosUsuario.tipo || 'usuario',
            idade: dadosUsuario.idade,
            cpf: dadosUsuario.cpf,
            dataCriacao: new Date()
        });

        return { success: true, user: user };
    } catch (error) {
        console.error("Erro no cadastro:", error);
        return { success: false, error: error.message };
    }
}

// Logout
export async function logoutUsuario() {
    try {
        await signOut(auth);
        return { success: true };
    } catch (error) {
        console.error("Erro no logout:", error);
        return { success: false, error: error.message };
    }
}

// Buscar dados do usuário atual
export async function getUsuarioAtual() {
    const user = auth.currentUser;
    if (user) {
        const userDoc = await getDoc(doc(db, "usuarios", user.uid));
        return userDoc.data();
    }
    return null;
}
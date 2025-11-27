import { db, storage } from './firebase-config.js';
import {
    collection,
    addDoc,
    updateDoc,
    deleteDoc,
    doc,
    getDocs,
    getDoc,
    query,
    where,
    orderBy,
    arrayUnion,
    arrayRemove
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";
import {
    ref,
    uploadBytes,
    getDownloadURL,
    deleteObject
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-storage.js";

// ========== CRUD EVENTOS ==========

// Criar evento
export async function criarEvento(eventoData, bannerFile = null) {
    try {
        let bannerUrl = eventoData.banner || '';

        // Upload do banner se existir
        if (bannerFile) {
            const bannerRef = ref(storage, `banners/${Date.now()}_${bannerFile.name}`);
            await uploadBytes(bannerRef, bannerFile);
            bannerUrl = await getDownloadURL(bannerRef);
        }

        const eventoCompleto = {
            ...eventoData,
            banner: bannerUrl,
            dataCriacao: new Date(),
            ativo: true
        };

        const docRef = await addDoc(collection(db, "eventos"), eventoCompleto);
        return { success: true, id: docRef.id };
    } catch (error) {
        console.error("Erro ao criar evento:", error);
        return { success: false, error: error.message };
    }
}

// Buscar todos os eventos
export async function getEventos() {
    try {
        const querySnapshot = await getDocs(
            query(collection(db, "eventos"), orderBy("data", "asc"))
        );

        const eventos = [];
        querySnapshot.forEach((doc) => {
            eventos.push({ id: doc.id, ...doc.data() });
        });

        return { success: true, eventos: eventos };
    } catch (error) {
        console.error("Erro ao buscar eventos:", error);
        return { success: false, error: error.message };
    }
}

// Buscar evento por ID
export async function getEventoPorId(eventoId) {
    try {
        const docSnap = await getDoc(doc(db, "eventos", eventoId));

        if (docSnap.exists()) {
            return { success: true, evento: { id: docSnap.id, ...docSnap.data() } };
        } else {
            return { success: false, error: "Evento não encontrado" };
        }
    } catch (error) {
        console.error("Erro ao buscar evento:", error);
        return { success: false, error: error.message };
    }
}

// Atualizar evento
export async function atualizarEvento(eventoId, eventoData, bannerFile = null) {
    try {
        let atualizacoes = { ...eventoData };

        // Upload do novo banner se existir
        if (bannerFile) {
            const bannerRef = ref(storage, `banners/${Date.now()}_${bannerFile.name}`);
            await uploadBytes(bannerRef, bannerFile);
            atualizacoes.banner = await getDownloadURL(bannerRef);
        }

        await updateDoc(doc(db, "eventos", eventoId), {
            ...atualizacoes,
            dataAtualizacao: new Date()
        });

        return { success: true };
    } catch (error) {
        console.error("Erro ao atualizar evento:", error);
        return { success: false, error: error.message };
    }
}

// Deletar evento
export async function deletarEvento(eventoId) {
    try {
        await deleteDoc(doc(db, "eventos", eventoId));
        return { success: true };
    } catch (error) {
        console.error("Erro ao deletar evento:", error);
        return { success: false, error: error.message };
    }
}

// ========== FAVORITOS ==========

// Adicionar aos favoritos
export async function adicionarFavorito(usuarioId, eventoId) {
    try {
        await updateDoc(doc(db, "usuarios", usuarioId), {
            favoritos: arrayUnion(eventoId)
        });
        return { success: true };
    } catch (error) {
        console.error("Erro ao adicionar favorito:", error);
        return { success: false, error: error.message };
    }
}

// Remover dos favoritos
export async function removerFavorito(usuarioId, eventoId) {
    try {
        await updateDoc(doc(db, "usuarios", usuarioId), {
            favoritos: arrayRemove(eventoId)
        });
        return { success: true };
    } catch (error) {
        console.error("Erro ao remover favorito:", error);
        return { success: false, error: error.message };
    }
}

// Buscar eventos favoritos
export async function getEventosFavoritos(usuarioId) {
    try {
        // Buscar usuário para pegar lista de favoritos
        const userDoc = await getDoc(doc(db, "usuarios", usuarioId));
        const userData = userDoc.data();
        const favoritosIds = userData.favoritos || [];

        // Buscar eventos dos favoritos
        const eventosFavoritos = [];
        for (const eventoId of favoritosIds) {
            const eventoDoc = await getDoc(doc(db, "eventos", eventoId));
            if (eventoDoc.exists()) {
                eventosFavoritos.push({ id: eventoDoc.id, ...eventoDoc.data() });
            }
        }

        return { success: true, eventos: eventosFavoritos };
    } catch (error) {
        console.error("Erro ao buscar favoritos:", error);
        return { success: false, error: error.message };
    }
}

// ========== USUÁRIOS ==========

// Buscar todos os usuários (apenas admin)
export async function getUsuarios() {
    try {
        const querySnapshot = await getDocs(collection(db, "usuarios"));

        const usuarios = [];
        querySnapshot.forEach((doc) => {
            usuarios.push({ id: doc.id, ...doc.data() });
        });

        return { success: true, usuarios: usuarios };
    } catch (error) {
        console.error("Erro ao buscar usuários:", error);
        return { success: false, error: error.message };
    }
}

// Atualizar usuário
export async function atualizarUsuario(usuarioId, usuarioData) {
    try {
        await updateDoc(doc(db, "usuarios", usuarioId), {
            ...usuarioData,
            dataAtualizacao: new Date()
        });
        return { success: true };
    } catch (error) {
        console.error("Erro ao atualizar usuário:", error);
        return { success: false, error: error.message };
    }
}

// Deletar usuário
export async function deletarUsuario(usuarioId) {
    try {
        await deleteDoc(doc(db, "usuarios", usuarioId));
        return { success: true };
    } catch (error) {
        console.error("Erro ao deletar usuário:", error);
        return { success: false, error: error.message };
    }
}

// ========== FUNÇÕES ÚTEIS ==========

// Buscar eventos por tipo
export async function getEventosPorTipo(tipo) {
    try {
        const q = query(
            collection(db, "eventos"),
            where("tipo", "==", tipo),
            orderBy("data", "asc")
        );

        const querySnapshot = await getDocs(q);
        const eventos = [];
        querySnapshot.forEach((doc) => {
            eventos.push({ id: doc.id, ...doc.data() });
        });

        return { success: true, eventos: eventos };
    } catch (error) {
        console.error("Erro ao buscar eventos por tipo:", error);
        return { success: false, error: error.message };
    }
}

// Buscar eventos futuros
export async function getEventosFuturos() {
    try {
        const hoje = new Date().toISOString().split('T')[0];
        const q = query(
            collection(db, "eventos"),
            where("data", ">=", hoje),
            orderBy("data", "asc")
        );

        const querySnapshot = await getDocs(q);
        const eventos = [];
        querySnapshot.forEach((doc) => {
            eventos.push({ id: doc.id, ...doc.data() });
        });

        return { success: true, eventos: eventos };
    } catch (error) {
        console.error("Erro ao buscar eventos futuros:", error);
        return { success: false, error: error.message };
    }
}

// Buscar eventos passados
export async function getEventosPassados() {
    try {
        const hoje = new Date().toISOString().split('T')[0];
        const q = query(
            collection(db, "eventos"),
            where("data", "<", hoje),
            orderBy("data", "desc")
        );

        const querySnapshot = await getDocs(q);
        const eventos = [];
        querySnapshot.forEach((doc) => {
            eventos.push({ id: doc.id, ...doc.data() });
        });

        return { success: true, eventos: eventos };
    } catch (error) {
        console.error("Erro ao buscar eventos passados:", error);
        return { success: false, error: error.message };
    }
}
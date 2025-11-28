// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";
import { getStorage } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-storage.js";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyDxL05Hh-WB-dunpvm4fFOGvWeAJ4y2YhU",
    authDomain: "agendacultural-1b66f.firebaseapp.com",
    projectId: "agendacultural-1b66f",
    storageBucket: "agendacultural-1b66f.firebasestorage.app",
    messagingSenderId: "921446426670",
    appId: "1:921446426670:web:85a86e1df7c3fc920717bf"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);
const storage = getStorage(app);

// Export for use in other files
export { app, auth, db, storage };
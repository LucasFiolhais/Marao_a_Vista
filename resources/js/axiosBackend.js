// resources/js/axiosBackend.js
import axios from 'axios';

// URL base do backend (Laravel)
const BACKEND_BASE_URL = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8080';

// API interna do admin: http://localhost:8080/admin/api/...
const backend = axios.create({
    baseURL: `${BACKEND_BASE_URL}/admin/api`,
    withCredentials: true, // envia cookies da sessão (Jetstream)
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export default backend;
import axios from 'axios';

const TOKEN_KEY = 'saliksic_web_token';

const client = axios.create({
    baseURL: '/api/v1',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
});

client.interceptors.request.use((config) => {
    const token = localStorage.getItem(TOKEN_KEY);
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

client.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem(TOKEN_KEY);
            if (!window.location.pathname.startsWith('/author/login')) {
                window.location.href = '/author/login';
            }
        }
        return Promise.reject(error);
    },
);

export { client, TOKEN_KEY };

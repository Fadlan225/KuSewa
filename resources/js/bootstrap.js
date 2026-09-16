import axios from 'axios';

// Kirim CSRF token secara otomatis di setiap request Axios
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Baca XSRF-TOKEN dari cookie dan sertakan ke header
axios.interceptors.request.use((config) => {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (match) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(match[1]);
    }
    return config;
});

window.axios = axios;

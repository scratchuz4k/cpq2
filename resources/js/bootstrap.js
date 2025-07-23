import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.withCredentials = true;
window.axios.withXSRFToken = true;

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

//Redirect to login if not authenticated
window.axios.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.response && error.response.status === 401) {
            window.location.href = '/login';
        }
        if (error.response && error.response.status === 419) {
            window.location.reload();
        }
        return Promise.reject(error);
    }
);

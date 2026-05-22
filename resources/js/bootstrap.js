import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Nanti konfigurasi Laravel Echo dan Reverb (WebSocket)
 * akan ditambahkan otomatis di bagian bawah file ini.
 */

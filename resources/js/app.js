import './bootstrap';
import './theme';
import './command-palette';
import './pwa-install';
import './nav-loading';

import Alpine from 'alpinejs';
import QRCode from 'qrcode';
import { queueSale, getQueuedSales, removeQueuedSale } from './offline-queue';

window.Alpine = Alpine;
window.QRCode = QRCode;
window.PosOfflineQueue = { queueSale, getQueuedSales, removeQueuedSale };

Alpine.start();

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
}

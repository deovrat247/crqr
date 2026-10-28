import './bootstrap';
import Alpine from 'alpinejs';
import qrGenerator from './qr-generator';

Alpine.data('qrGenerator', qrGenerator);

window.Alpine = Alpine;
Alpine.start();

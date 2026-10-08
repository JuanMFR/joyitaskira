import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import cart from './cart';

Alpine.plugin(collapse);
Alpine.store('cart', cart(window.KIRA ?? {}));

window.Alpine = Alpine;
Alpine.start();

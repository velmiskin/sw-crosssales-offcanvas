import AddToCartPlugin from 'src/plugin/add-to-cart/add-to-cart.plugin.js';

export default class OffcanvasAddToCart extends AddToCartPlugin {
    static options = {
        ...AddToCartPlugin.options,
        redirectTo: 'frontend.cart.offcanvas.crossselling'
    }

    init() {
        super.init();
    }
}
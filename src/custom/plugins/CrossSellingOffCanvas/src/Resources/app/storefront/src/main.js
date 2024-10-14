const PluginManager = window.PluginManager;

PluginManager.override('AddToCart', () => import('./offcanvas-add-to-cart/offcanvas-add-to-cart.plugin'), '[data-add-to-cart]')
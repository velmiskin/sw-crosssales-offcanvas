<?php declare(strict_types=1);

namespace CrossSellingOffCanvas\Storefront\Controller;

use CrossSellingOffCanvas\Service\CrossSellingResolver;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Checkout\Cart\Error\PaymentMethodChangedError;
use Shopware\Storefront\Checkout\Cart\Error\ShippingMethodChangedError;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class CrossSellingOffCanvasController extends StorefrontController
{
    private const REDIRECTED_FROM_SAME_ROUTE = 'redirected';

    public function __construct(
        private readonly OffcanvasCartPageLoader $offcanvasCartPageLoader,
        private readonly CrossSellingResolver $crossSellingResolver
    ) {
    }

    #[Route(path: '/checkout/offcanvas-crossselling', name: 'frontend.cart.offcanvas.crossselling', options: ['seo' => false], defaults: ['XmlHttpRequest' => true], methods: ['GET'])]
    public function offCanvas(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->offcanvasCartPageLoader->load($request, $context);

        $cart = $page->getCart();
        $this->addCartErrors($cart);
        $cartErrors = $cart->getErrors();

        if (!$request->query->getBoolean(self::REDIRECTED_FROM_SAME_ROUTE) && $this->routeNeedsReload($cartErrors)) {
            $cartErrors->clear();

            // To prevent redirect loops add the identifier that the request already got redirected from the same origin
            return $this->redirectToRoute(
                'frontend.cart.offcanvas.crossselling',
                [...$request->query->all(), ...[self::REDIRECTED_FROM_SAME_ROUTE => true]],
            );
        }

        $cartErrors->clear();

        $lastLineItemId = $cart->getLineItems()->last()?->getId();

        $crossSellings = $lastLineItemId !== null ? $this->crossSellingResolver->getProductPrefferedCrossSellings(
            $lastLineItemId,
            $context->getContext()
        ) : null;

        trap($crossSellings);

        return $this->renderStorefront('@CrossSellingOffCanvas/storefront/component/checkout/crossselling-offcanvas-cart.html.twig', ['page' => $page]);
    }

    private function routeNeedsReload(ErrorCollection $cartErrors): bool
    {
        foreach ($cartErrors as $error) {
            if ($error instanceof ShippingMethodChangedError || $error instanceof PaymentMethodChangedError) {
                return true;
            }
        }

        return false;
    }
}

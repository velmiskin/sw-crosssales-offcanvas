<?php

declare(strict_types=1);


namespace CrossSellingOffCanvas\Service;

use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

readonly class CrossSellingResolver
{
    public function __construct(private EntityRepository $productRepository)
    {
    }

    public function getProductPrefferedCrossSellings(
        string $productId,
        Context $context
    ): ProductCollection|null {
        $globalCrossSellingId = '019266a65e2f71d5bfd3e0030f59e954';
        $criteria = new Criteria([$productId]);
        $criteria->addAssociation('crossSellings.assignedProducts.product')
            ->getAssociation('crossSellings')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));

        /** @var ProductEntity $product */
        $product = $this->productRepository->search($criteria, $context)->first();

        if (!$product->getCrossSellings()) {
            return null;
        }

        $defaultProductCrossSellingId = $product->getCustomFields()['cross_selling_default_value'] ?? null;

        $crossSellings = $product->getCrossSellings()->firstWhere(
            function ($element) use ($defaultProductCrossSellingId, $globalCrossSellingId) {

                if ($element->getId() === $defaultProductCrossSellingId) {
                    return true;
                }

                if ($element->getId() === $globalCrossSellingId) {
                    return true;
                }

                return false;
            }
        );


        $crossSales = $crossSellings->getAssignedProducts()?->getIterator() ?? $product->getCrossSellings()->getIterator();

        if ($crossSales === null) {
            return null;
        }


        return new ProductCollection($crossSales);
    }

}
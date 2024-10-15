<?php declare(strict_types=1);

namespace CrossSellingOffCanvas\Service;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFieldsManager
{
    private const CUSTOM_FIELDSET_NAME = 'product_preferred_cross_selling';

    private const CUSTOM_FIELDSET = [
        'name' => self::CUSTOM_FIELDSET_NAME,
        'config' => [
            'label' => [
                'en-GB' => 'Preferred cross selling',
                'de-DE' => 'Bevorzugtes Cross-Selling',
                Defaults::LANGUAGE_SYSTEM => 'Preferred cross selling',
            ],
        ],
        'customFields' => [
            [
                'name' => 'preferred_cross_selling',
                'type' => CustomFieldTypes::SELECT,
                'config' => [
                    'label' => [
                        'en-GB' => 'Cross selling',
                        'de-DE' => 'Cross-Selling',
                        Defaults::LANGUAGE_SYSTEM => 'Cross selling',
                    ],
                    'entity' => 'product_cross_selling',
                    'customFieldType' => 'entity',
                    'customFieldPosition' => 1,
                ],
            ],
        ],
    ];

    public function __construct(
        private readonly EntityRepository $customFieldSetRepository,
        private readonly EntityRepository $customFieldSetRelationRepository
    ) {
    }

    public function install(Context $context): void
    {
        $this->customFieldSetRepository->upsert([
            self::CUSTOM_FIELDSET,
        ], $context);
    }

    public function uninstall(Context $context): void
    {
        $this->customFieldSetRepository->delete([
            $this->getCustomFieldSetIds($context),
        ], $context);


    }

    public function addRelations(Context $context): void
    {
        $this->customFieldSetRelationRepository->upsert(array_map(static function (string $customFieldSetId) {
            return [
                'customFieldSetId' => $customFieldSetId,
                'entityName' => 'product',
            ];
        }, $this->getCustomFieldSetIds($context)), $context);
    }

    /**
     * @return string[]
     */
    private function getCustomFieldSetIds(Context $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('name', self::CUSTOM_FIELDSET_NAME));

        return $this->customFieldSetRepository->searchIds($criteria, $context)->getIds();
    }

    /**
     * @return string[]
     */
    private function getCustomFieldSetRelationIds(string $customFieldSetId, Context $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('customFieldSetId', $customFieldSetId));

        return $this->customFieldSetRelationRepository->searchIds($criteria, $context)->getIds();
    }


}

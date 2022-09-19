<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityGui\Communication\Table;

use Orm\Zed\Availability\Persistence\Map\SpyAvailabilityAbstractTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractLocalizedAttributesTableMap;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\DecimalObject\Decimal;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer;
use Spryker\Zed\AvailabilityGui\Communication\Helper\AvailabilityHelperInterface;
use Spryker\Zed\AvailabilityGui\Dependency\Facade\AvailabilityToStoreFacadeInterface;
use Spryker\Zed\AvailabilityGui\Persistence\AvailabilityGuiRepositoryInterface;
use Spryker\Zed\Gui\Communication\Table\AbstractTable;
use Spryker\Zed\Gui\Communication\Table\TableConfiguration;

class AvailabilityAbstractTable extends AbstractTable
{
    /**
     * @var string
     */
    public const TABLE_COL_ACTION = 'Actions';

    /**
     * @var string
     */
    public const URL_PARAM_ID_PRODUCT_ABSTRACT = 'id-product';

    /**
     * @var string
     */
    public const AVAILABLE = 'Available';

    /**
     * @var string
     */
    public const NOT_AVAILABLE = 'Not available';

    /**
     * @var string
     */
    public const IS_BUNDLE_PRODUCT = 'Is bundle product';

    /**
     * @var string
     */
    public const URL_PARAM_ID_STORE = 'id-store';

    /**
     * @var \Spryker\Zed\AvailabilityGui\Communication\Helper\AvailabilityHelperInterface
     */
    protected $availabilityHelper;

    /**
     * @var \Orm\Zed\Product\Persistence\SpyProductAbstractQuery|\Propel\Runtime\ActiveQuery\ModelCriteria
     */
    protected $queryProductAbstractAvailability;

    /**
     * @var \Spryker\Zed\AvailabilityGui\Dependency\Facade\AvailabilityToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @var int
     */
    protected $idStore;

    /**
     * @var int
     */
    protected $idLocale;

    /**
     * @var \Spryker\Zed\AvailabilityGui\Persistence\AvailabilityGuiRepositoryInterface
     */
    protected $availabilityGuiRepository;

    /**
     * @param \Spryker\Zed\AvailabilityGui\Communication\Helper\AvailabilityHelperInterface $availabilityHelper
     * @param \Spryker\Zed\AvailabilityGui\Dependency\Facade\AvailabilityToStoreFacadeInterface $storeFacade
     * @param int $idStore
     * @param int $idLocale
     * @param \Spryker\Zed\AvailabilityGui\Persistence\AvailabilityGuiRepositoryInterface $availabilityGuiRepository
     */
    public function __construct(
        AvailabilityHelperInterface $availabilityHelper,
        AvailabilityToStoreFacadeInterface $storeFacade,
        int $idStore,
        int $idLocale,
        AvailabilityGuiRepositoryInterface $availabilityGuiRepository
    ) {
        $this->availabilityHelper = $availabilityHelper;
        $this->storeFacade = $storeFacade;
        $this->idStore = $idStore;
        $this->idLocale = $idLocale;
        $this->availabilityGuiRepository = $availabilityGuiRepository;

        $this->queryProductAbstractAvailability = $this->availabilityHelper
            ->queryAvailabilityAbstractWithCurrentStockAndReservedProductsAggregated($idLocale, $idStore);
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return \Spryker\Zed\Gui\Communication\Table\TableConfiguration
     */
    protected function configure(TableConfiguration $config)
    {
        $url = Url::generate(
            '/availability-abstract-table',
            $this->getRequest()->query->all(),
        );

        $config->setUrl($url);
        $config->setHeader([
            SpyProductAbstractTableMap::COL_SKU => 'SKU',
            AvailabilityHelperInterface::PRODUCT_NAME => 'Name',
            SpyAvailabilityAbstractTableMap::COL_QUANTITY => 'Availability',
            AvailabilityHelperInterface::STOCK_QUANTITY => 'Current Stock',
            AvailabilityHelperInterface::RESERVATION_QUANTITY => 'Reserved Products',
            static::IS_BUNDLE_PRODUCT => 'Is bundle product',
            AvailabilityHelperInterface::CONCRETE_NEVER_OUT_OF_STOCK_SET => 'Is never out of stock',
            static::TABLE_COL_ACTION => 'Actions',
        ]);

        $config->setSortable([
            SpyProductAbstractTableMap::COL_SKU,
            AvailabilityHelperInterface::PRODUCT_NAME,
            AvailabilityHelperInterface::STOCK_QUANTITY,
            AvailabilityHelperInterface::RESERVATION_QUANTITY,
        ]);

        $config->setSearchable([
            SpyProductAbstractTableMap::COL_SKU,
            SpyProductAbstractLocalizedAttributesTableMap::COL_NAME,
        ]);

        $config->setDefaultSortColumnIndex(0);
        $config->addRawColumn(static::TABLE_COL_ACTION);
        $config->addRawColumn(SpyAvailabilityAbstractTableMap::COL_QUANTITY);
        $config->addRawColumn(static::IS_BUNDLE_PRODUCT);
        $config->addRawColumn(SpyProductAbstractTableMap::COL_SKU);
        $config->addRawColumn(AvailabilityHelperInterface::CONCRETE_NEVER_OUT_OF_STOCK_SET);
        $config->setDefaultSortDirection(TableConfiguration::SORT_DESC);

        return $config;
    }

    /**
     * @param \Spryker\Zed\Gui\Communication\Table\TableConfiguration $config
     *
     * @return array
     */
    protected function prepareData(TableConfiguration $config)
    {
        $result = [];

        $this->expandPropelQuery();

        /** @var \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Product\Persistence\Base\SpyProductAbstract> $productAbstractEntities */
        $productAbstractEntities = $this->runQuery($this->queryProductAbstractAvailability, $config, true);

        $productAbstractIds = $this->getProductAbstractIds($productAbstractEntities);
        $productAbstractEntities = $this->availabilityHelper
            ->getProductAbstractEntitiesWithStockByProductAbstractIds(
                $productAbstractIds,
                $this->idLocale,
                $this->idStore,
            );

        foreach ($productAbstractEntities as $productAbstractEntity) {
            $haveBundledProducts = $this->haveBundledProducts($productAbstractEntity);
            $isNeverOutOfStock = $this->isNeverOutOfStock($productAbstractEntity);
            $stockQuantity = $this->formatFloat(
                $this->getStockQuantity($productAbstractEntity)->trim()->toFloat(),
            );
            $reservationQuantity = $haveBundledProducts ? 'N/A' : $this->formatFloat(
                $this->calculateReservation($productAbstractEntity)->trim()->toFloat(),
            );

            $result[] = [
                SpyProductAbstractTableMap::COL_SKU => $this->getProductEditPageLink($productAbstractEntity->getSku(), $productAbstractEntity->getIdProductAbstract()),
                AvailabilityQueryContainer::PRODUCT_NAME => $productAbstractEntity->getVirtualColumn(AvailabilityHelperInterface::PRODUCT_NAME),
                SpyAvailabilityAbstractTableMap::COL_QUANTITY => $this->getAvailabilityLabel($productAbstractEntity, $isNeverOutOfStock),
                AvailabilityHelperInterface::STOCK_QUANTITY => $stockQuantity,
                AvailabilityHelperInterface::RESERVATION_QUANTITY => $reservationQuantity,
                static::IS_BUNDLE_PRODUCT => $this->generateLabel($haveBundledProducts ? 'Yes' : 'No', $haveBundledProducts ? 'label-primary' : ''),
                AvailabilityHelperInterface::CONCRETE_NEVER_OUT_OF_STOCK_SET => $this->generateLabel($isNeverOutOfStock ? 'Yes' : 'No', $isNeverOutOfStock ? 'label-primary' : ''),
                static::TABLE_COL_ACTION => $this->createViewButton($productAbstractEntity),
            ];
        }

        return $result;
    }

    /**
     * @param \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Product\Persistence\Base\SpyProductAbstract> $productAbstractEntities
     *
     * @return array<int>
     */
    protected function getProductAbstractIds(ObjectCollection $productAbstractEntities): array
    {
        $productAbstractIds = [];
        foreach ($productAbstractEntities as $productAbstractEntity) {
            $productAbstractIds[] = $productAbstractEntity->getIdProductAbstract();
        }

        return $productAbstractIds;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return bool
     */
    protected function isNeverOutOfStock(SpyProductAbstract $productAbstractEntity): bool
    {
        $neverOutOfStockSet = '';

        if ($productAbstractEntity->hasVirtualColumn(AvailabilityHelperInterface::CONCRETE_NEVER_OUT_OF_STOCK_SET)) {
            $neverOutOfStockSet = $productAbstractEntity->getVirtualColumn(AvailabilityHelperInterface::CONCRETE_NEVER_OUT_OF_STOCK_SET);
        }

        return $this->availabilityHelper->isNeverOutOfStock($neverOutOfStockSet);
    }

    /**
     * @param string $sku
     * @param int $idProductAbstract
     *
     * @return string
     */
    protected function getProductEditPageLink($sku, $idProductAbstract)
    {
        $pageEditUrl = Url::generate('/product-management/edit', [
            'id-product-abstract' => $idProductAbstract,
        ])->build();

        $pageEditLink = '<a target="_blank" href="' . $pageEditUrl . '">' . $sku . '</a>';

        return $pageEditLink;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     * @param bool $isNeverOutOfStock
     *
     * @return string
     */
    protected function getAvailabilityLabel(SpyProductAbstract $productAbstractEntity, bool $isNeverOutOfStock): string
    {
        if ((new Decimal($productAbstractEntity->getVirtualColumn(AvailabilityHelperInterface::AVAILABILITY_QUANTITY) ?? 0))->greaterThan(0) || $isNeverOutOfStock) {
            return $this->generateLabel(static::AVAILABLE, 'label-info');
        }

        return $this->generateLabel(static::NOT_AVAILABLE, '');
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return \Spryker\DecimalObject\Decimal
     */
    protected function getStockQuantity(SpyProductAbstract $productAbstractEntity): Decimal
    {
        return (new Decimal($productAbstractEntity->getVirtualColumn(AvailabilityHelperInterface::STOCK_QUANTITY) ?? 0));
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return string
     */
    protected function createViewButton(SpyProductAbstract $productAbstractEntity)
    {
        $viewTaxSetUrl = Url::generate(
            '/availability-gui/index/view',
            [
                static::URL_PARAM_ID_PRODUCT_ABSTRACT => $productAbstractEntity->getIdProductAbstract(),
                static::URL_PARAM_ID_STORE => $this->idStore,
            ],
        );

        return $this->generateViewButton($viewTaxSetUrl, 'View');
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return bool
     */
    protected function haveBundledProducts(SpyProductAbstract $productAbstractEntity)
    {
        foreach ($productAbstractEntity->getSpyProducts() as $productEntity) {
            if ($productEntity->getSpyProductBundlesRelatedByFkProduct()->count() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $productAbstractEntity
     *
     * @return \Spryker\DecimalObject\Decimal
     */
    protected function calculateReservation(SpyProductAbstract $productAbstractEntity): Decimal
    {
        $reservationQuantity = '';

        if ($productAbstractEntity->hasVirtualColumn(AvailabilityHelperInterface::RESERVATION_QUANTITY)) {
            $reservationQuantity = $productAbstractEntity->getVirtualColumn(AvailabilityHelperInterface::RESERVATION_QUANTITY) ?? '';
        }

        return $this->availabilityHelper->calculateReservation(
            $reservationQuantity,
            $this->storeFacade->getStoreById($this->idStore),
        );
    }

    /**
     * @return void
     */
    protected function expandPropelQuery(): void
    {
        $this->queryProductAbstractAvailability = $this->availabilityGuiRepository->expandQuery($this->queryProductAbstractAvailability);
    }
}

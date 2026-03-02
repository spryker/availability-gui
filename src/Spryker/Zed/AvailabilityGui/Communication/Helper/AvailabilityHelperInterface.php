<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityGui\Communication\Helper;

use Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Availability\Persistence\SpyAvailabilityAbstractQuery;
use Orm\Zed\Product\Persistence\SpyProductAbstractQuery;
use Spryker\DecimalObject\Decimal;

interface AvailabilityHelperInterface
{
    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::CONCRETE_NEVER_OUT_OF_STOCK_SET
     *
     * @var string
     */
    public const CONCRETE_NEVER_OUT_OF_STOCK_SET = 'concreteNeverOutOfStockSet';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::RESERVATION_QUANTITY
     *
     * @var string
     */
    public const RESERVATION_QUANTITY = 'reservationQuantity';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::STOCK_QUANTITY
     *
     * @var string
     */
    public const STOCK_QUANTITY = 'stockQuantity';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::AVAILABILITY_QUANTITY
     *
     * @var string
     */
    public const AVAILABILITY_QUANTITY = 'availabilityQuantity';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::PRODUCT_NAME
     *
     * @var string
     */
    public const PRODUCT_NAME = 'productName';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::CONCRETE_SKU
     *
     * @var string
     */
    public const CONCRETE_SKU = 'concreteSku';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::CONCRETE_AVAILABILITY
     *
     * @var string
     */
    public const CONCRETE_AVAILABILITY = 'concreteAvailability';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::CONCRETE_NAME
     *
     * @var string
     */
    public const CONCRETE_NAME = 'concreteName';

    /**
     * @uses \Spryker\Zed\Availability\Persistence\AvailabilityQueryContainer::ID_PRODUCT
     *
     * @var string
     */
    public const ID_PRODUCT = 'idProduct';

    public function findProductAbstractAvailabilityTransfer(int $idProductAbstract, int $idLocale, int $idStore): ?ProductAbstractAvailabilityTransfer;

    public function isNeverOutOfStock(string $neverOutOfStockSet): bool;

    public function calculateReservation(string $reservationAggregationSet, StoreTransfer $storeTransfer): Decimal;

    public function sumReservationsFromOtherStores(string $concreteSku, StoreTransfer $storeTransfer, Decimal $currentStoreReservationQuantity): Decimal;

    public function isBundleProduct(int $idProduct): bool;

    /**
     * @param int $idLocale
     * @param int $idStore
     *
     * @return \Orm\Zed\Availability\Persistence\SpyAvailabilityAbstractQuery|\Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryAvailabilityAbstractWithStockByIdLocale(int $idLocale, int $idStore);

    public function queryAvailabilityAbstractWithCurrentStockAndReservedProductsAggregated(int $idLocale, int $idStore): SpyProductAbstractQuery;

    /**
     * @param int|null $idProductAbstract
     * @param int $idLocale
     * @param int $idStore
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryAvailabilityWithStockByIdProductAbstractAndIdLocale(?int $idProductAbstract, int $idLocale, int $idStore);

    /**
     * @param array<int> $productAbstractIds
     * @param int $idLocale
     * @param int $idStore
     *
     * @return array<\Orm\Zed\Product\Persistence\SpyProductAbstract>
     */
    public function getProductAbstractEntitiesWithStockByProductAbstractIds(array $productAbstractIds, int $idLocale, int $idStore): array;

    public function queryAvailabilityAbstractByIdStore(int $idStore): SpyAvailabilityAbstractQuery;
}

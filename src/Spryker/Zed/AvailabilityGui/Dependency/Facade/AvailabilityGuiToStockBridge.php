<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityGui\Dependency\Facade;

use Generated\Shared\Transfer\StockProductTransfer;
use Generated\Shared\Transfer\StoreTransfer;

class AvailabilityGuiToStockBridge implements AvailabilityGuiToStockInterface
{
    /**
     * @var \Spryker\Zed\Stock\Business\StockFacadeInterface
     */
    protected $stockFacade;

    /**
     * @param \Spryker\Zed\Stock\Business\StockFacadeInterface $stockFacade
     */
    public function __construct($stockFacade)
    {
        $this->stockFacade = $stockFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\StockProductTransfer $transferStockProduct
     *
     * @return int
     */
    public function createStockProduct(StockProductTransfer $transferStockProduct): int
    {
        return $this->stockFacade->createStockProduct($transferStockProduct);
    }

    /**
     * @param \Generated\Shared\Transfer\StockProductTransfer $stockProductTransfer
     *
     * @return int
     */
    public function updateStockProduct(StockProductTransfer $stockProductTransfer): int
    {
        return $this->stockFacade->updateStockProduct($stockProductTransfer);
    }

    /**
     * @param int $idProductConcrete
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return array<\Generated\Shared\Transfer\StockProductTransfer>
     */
    public function findStockProductsByIdProductForStore($idProductConcrete, StoreTransfer $storeTransfer): array
    {
        return $this->stockFacade->findStockProductsByIdProductForStore($idProductConcrete, $storeTransfer);
    }

    /**
     * @return array<array<string>>
     */
    public function getWarehouseToStoreMapping(): array
    {
        return $this->stockFacade->getWarehouseToStoreMapping();
    }

    /**
     * @return array<array<string>>
     */
    public function getStoreToWarehouseMapping(): array
    {
        return $this->stockFacade->getStoreToWarehouseMapping();
    }

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return array<string>
     */
    public function getStockTypesForStore(StoreTransfer $storeTransfer): array
    {
        return $this->stockFacade->getStockTypesForStore($storeTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\StoreTransfer $storeTransfer
     *
     * @return array<\Generated\Shared\Transfer\StockTransfer>
     */
    public function getAvailableWarehousesForStore(StoreTransfer $storeTransfer): array
    {
        return $this->stockFacade->getAvailableWarehousesForStore($storeTransfer);
    }
}

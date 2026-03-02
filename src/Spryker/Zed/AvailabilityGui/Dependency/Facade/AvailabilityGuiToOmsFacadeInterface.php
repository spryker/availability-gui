<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityGui\Dependency\Facade;

use Generated\Shared\Transfer\StoreTransfer;
use Spryker\DecimalObject\Decimal;

interface AvailabilityGuiToOmsFacadeInterface
{
    public function getReservationsFromOtherStores(string $sku, StoreTransfer $storeTransfer): Decimal;
}

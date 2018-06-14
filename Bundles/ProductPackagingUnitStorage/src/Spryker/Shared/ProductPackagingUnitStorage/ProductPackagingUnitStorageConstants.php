<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\ProductPackagingUnitStorage;

class ProductPackagingUnitStorageConstants
{
    /**
     * Specification:
     * - Queue name as used for processing price messages
     *
     * @api
     */
    const PRODUCT_PACKAGING_UNIT_SYNC_STORAGE_QUEUE = 'sync.storage.product';

    /**
     * Specification:
     * - Queue name as used for processing price messages
     *
     * @api
     */
    const PRODUCT_PACKAGING_UNIT_SYNC_STORAGE_ERROR_QUEUE = 'sync.storage.product.error';

    /**
     * Specification:
     * - Resource name, this will use for key generating
     *
     * @api
     */
    const PRODUCT_PACKAGING_UNIT_RESOURCE_NAME = 'product_abstract_packaging';
}

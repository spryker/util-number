<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\QuickOrder;

use Generated\Shared\Transfer\QuickOrderTransfer;

interface QuickOrderClientInterface
{
    /**
     * Specification:
     * - Returns the list of ProductConcreteTransfers from QuickOrderTransfer.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuickOrderTransfer $quickOrderTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer[]
     */
    public function getProductsByQuickOrder(QuickOrderTransfer $quickOrderTransfer): array;

    /**
     * Specification:
     * - Validate QuickOrderTransfer item product.
     * - If product data not valid, QuickOrderItemTransfer will be updated with the error message
     * - Extend the QuickOrderTransfer with ProductConcreteTransfers.
     * - Expands array of ProductConcreteTransfers with additional data using pre-configured plugins.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuickOrderTransfer $quickOrderTransfer
     *
     * @return \Generated\Shared\Transfer\QuickOrderTransfer
     */
    public function addProductsToQuickOrder(QuickOrderTransfer $quickOrderTransfer): QuickOrderTransfer;

    /**
     * Specification:
     * - Expands array of ProductConcreteTransfers with additional data using pre-configured plugins.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer[] $productConcreteTransfers
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer[]
     */
    public function expandProductConcreteTransfers(array $productConcreteTransfers): array;
}

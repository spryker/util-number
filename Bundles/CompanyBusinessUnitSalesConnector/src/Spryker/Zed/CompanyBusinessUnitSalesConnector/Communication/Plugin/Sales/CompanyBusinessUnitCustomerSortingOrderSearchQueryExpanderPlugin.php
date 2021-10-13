<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CompanyBusinessUnitSalesConnector\Communication\Plugin\Sales;

use Generated\Shared\Transfer\QueryJoinCollectionTransfer;
use Spryker\Zed\CompanyBusinessUnitSalesConnector\CompanyBusinessUnitSalesConnectorConfig;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\SalesExtension\Dependency\Plugin\SearchOrderQueryExpanderPluginInterface;

/**
 * @method \Spryker\Zed\CompanyBusinessUnitSalesConnector\Business\CompanyBusinessUnitSalesConnectorFacadeInterface getFacade()
 * @method \Spryker\Zed\CompanyBusinessUnitSalesConnector\CompanyBusinessUnitSalesConnectorConfig getConfig()
 */
class CompanyBusinessUnitCustomerSortingOrderSearchQueryExpanderPlugin extends AbstractPlugin implements SearchOrderQueryExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Returns true if sorting by customer name or email could be applied, false otherwise.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\FilterFieldTransfer> $filterFieldTransfers
     *
     * @return bool
     */
    public function isApplicable(array $filterFieldTransfers): bool
    {
        return $this->getFacade()->isFilterFieldSet(
            $filterFieldTransfers,
            CompanyBusinessUnitSalesConnectorConfig::FILTER_FIELD_TYPE_COMPANY_BUSINESS_UNIT
        );
    }

    /**
     * {@inheritDoc}
     * - Expands QueryJoinCollectionTransfer with additional QueryJoinTransfers to sort by customer name or email.
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\FilterFieldTransfer> $filterFieldTransfers
     * @param \Generated\Shared\Transfer\QueryJoinCollectionTransfer $queryJoinCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\QueryJoinCollectionTransfer
     */
    public function expand(
        array $filterFieldTransfers,
        QueryJoinCollectionTransfer $queryJoinCollectionTransfer
    ): QueryJoinCollectionTransfer {
        return $this->getFacade()->expandQueryJoinCollectionWithCustomerSorting(
            $filterFieldTransfers,
            $queryJoinCollectionTransfer
        );
    }
}

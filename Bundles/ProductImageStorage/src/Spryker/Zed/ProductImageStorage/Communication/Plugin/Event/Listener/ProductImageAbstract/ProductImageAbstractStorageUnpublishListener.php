<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ProductImageStorage\Communication\Plugin\Event\Listener\ProductImageAbstract;

use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\PropelOrm\Business\Transaction\DatabaseTransactionHandlerTrait;

/**
 * @method \Spryker\Zed\ProductImageStorage\Persistence\ProductImageStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\ProductImageStorage\Communication\ProductImageStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\ProductImageStorage\Business\ProductImageStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\ProductImageStorage\ProductImageStorageConfig getConfig()
 */
class ProductImageAbstractStorageUnpublishListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    use DatabaseTransactionHandlerTrait;

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $this->preventTransaction();
        $productAbstractIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        $this->getFacade()->unpublishProductAbstractImages($productAbstractIds);
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantSalesOrder\Business\Creator;

use Generated\Shared\Transfer\MerchantOrderCollectionTransfer;
use Generated\Shared\Transfer\MerchantOrderTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\MerchantSalesOrder\Persistence\MerchantSalesOrderEntityManagerInterface;

class MerchantOrderCreator implements MerchantOrderCreatorInterface
{
    /**
     * @var \Spryker\Zed\MerchantSalesOrder\Persistence\MerchantSalesOrderEntityManagerInterface
     */
    protected $merchantSalesOrderEntityManager;

    /**
     * @var \Spryker\Zed\MerchantSalesOrder\Business\Creator\MerchantOrderItemCreatorInterface
     */
    protected $merchantOrderItemCreator;

    /**
     * @var \Spryker\Zed\MerchantSalesOrder\Business\Creator\MerchantOrderTotalsCreatorInterface
     */
    protected $merchantOrderTotalsCreator;

    /**
     * @param \Spryker\Zed\MerchantSalesOrder\Persistence\MerchantSalesOrderEntityManagerInterface $merchantSalesOrderEntityManager
     * @param \Spryker\Zed\MerchantSalesOrder\Business\Creator\MerchantOrderItemCreatorInterface $merchantOrderItemCreator
     * @param \Spryker\Zed\MerchantSalesOrder\Business\Creator\MerchantOrderTotalsCreatorInterface $merchantOrderTotalsCreator
     */
    public function __construct(
        MerchantSalesOrderEntityManagerInterface $merchantSalesOrderEntityManager,
        MerchantOrderItemCreatorInterface $merchantOrderItemCreator,
        MerchantOrderTotalsCreatorInterface $merchantOrderTotalsCreator
    ) {
        $this->merchantSalesOrderEntityManager = $merchantSalesOrderEntityManager;
        $this->merchantOrderItemCreator = $merchantOrderItemCreator;
        $this->merchantOrderTotalsCreator = $merchantOrderTotalsCreator;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantOrderCollectionTransfer
     */
    public function createMerchantOrderCollection(OrderTransfer $orderTransfer): MerchantOrderCollectionTransfer
    {
        $orderTransfer->requireIdSalesOrder()
            ->requireOrderReference()
            ->requireItems();

        $merchantOrderCollectionTransfer = new MerchantOrderCollectionTransfer();
        $orderItemsGroupedByMerchantReference = $this->getOrderItemsGroupedByMerchantReference($orderTransfer);

        foreach ($orderItemsGroupedByMerchantReference as $merchantReference => $itemTransfers) {
            $merchantOrderCollectionTransfer->addMerchantOrder(
                $this->createMerchantOrderWithItemsAndTotals($orderTransfer, $merchantReference, $itemTransfers)
            );
        }

        return $merchantOrderCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Generated\Shared\Transfer\ItemTransfer[][]
     */
    protected function getOrderItemsGroupedByMerchantReference(OrderTransfer $orderTransfer): array
    {
        $orderItemsGroupedByMerchantReference = [];

        foreach ($orderTransfer->getItems() as $itemTransfer) {
            if (!$itemTransfer->getMerchantReference()) {
                continue;
            }

            $orderItemsGroupedByMerchantReference[$itemTransfer->getMerchantReference()][] = $itemTransfer;
        }

        return $orderItemsGroupedByMerchantReference;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param string $merchantReference
     * @param \Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return \Generated\Shared\Transfer\MerchantOrderTransfer
     */
    protected function createMerchantOrderWithItemsAndTotals(
        OrderTransfer $orderTransfer,
        string $merchantReference,
        array $itemTransfers
    ): MerchantOrderTransfer {
        $merchantOrderTransfer = $this->createMerchantOrder(
            $orderTransfer,
            $merchantReference
        );
        $merchantOrderTransfer = $this->addMerchantOrderItemsToMerchantOrder(
            $merchantOrderTransfer,
            $itemTransfers
        );

        return $merchantOrderTransfer->setTotals(
            $this->merchantOrderTotalsCreator->createMerchantOrderTotals($merchantOrderTransfer)
        );
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantOrderTransfer $merchantOrderTransfer
     * @param \Generated\Shared\Transfer\ItemTransfer[] $itemTransfers
     *
     * @return \Generated\Shared\Transfer\MerchantOrderTransfer
     */
    protected function addMerchantOrderItemsToMerchantOrder(
        MerchantOrderTransfer $merchantOrderTransfer,
        array $itemTransfers
    ): MerchantOrderTransfer {
        foreach ($itemTransfers as $itemTransfer) {
            $merchantOrderTransfer->addMerchantOrderItem(
                $this->merchantOrderItemCreator->createMerchantOrderItem($itemTransfer, $merchantOrderTransfer)
            );
        }

        return $merchantOrderTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param string $merchantReference
     *
     * @return \Generated\Shared\Transfer\MerchantOrderTransfer
     */
    public function createMerchantOrder(OrderTransfer $orderTransfer, string $merchantReference): MerchantOrderTransfer
    {
        $merchantOrderTransfer = (new MerchantOrderTransfer())
            ->setMerchantReference($merchantReference)
            ->setIdOrder($orderTransfer->getIdSalesOrder())
            ->setMerchantOrderReference(
                sprintf('%s--%s', $orderTransfer->getOrderReference(), $merchantReference)
            );

        return $this->merchantSalesOrderEntityManager->createMerchantOrder($merchantOrderTransfer);
    }
}

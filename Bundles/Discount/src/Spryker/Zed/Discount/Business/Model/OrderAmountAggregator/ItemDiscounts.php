<?php
/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Discount\Business\Model\OrderAmountAggregator;

use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Orm\Zed\Sales\Persistence\SpySalesDiscount;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface;

class ItemDiscounts implements OrderAmountAggregatorInterface
{
    /**
     * @var \Spryker\Zed\Discount\Persistence\DiscountQueryContainerInterface
     */
    protected $discountQueryContainer;

    /**
     * ItemDiscountAmounts constructor.
     */
    public function __construct(DiscountQueryContainerInterface $discountQueryContainer)
    {
        $this->discountQueryContainer = $discountQueryContainer;
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    public function aggregate(OrderTransfer $orderTransfer)
    {
        $this->assertItemDiscountsRequirements($orderTransfer);

        $salesOrderDiscounts = $this->getSalesOrderDiscounts($orderTransfer);

        if (count($salesOrderDiscounts) === 0) {
            $this->setExpenseGrossPriceWithDiscountsToDefaults($orderTransfer);
            $this->setItemGrossPriceWithDiscountsToDefaults($orderTransfer);
            return;
        }

        $this->addDiscountsFromSalesOrderDiscountEntity($orderTransfer, $salesOrderDiscounts);
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Propel\Runtime\Collection\ObjectCollection|\Orm\Zed\Sales\Persistence\SpySalesDiscount[] $salesOrderDiscounts
     *
     * @return void
     */
    protected function addDiscountsFromSalesOrderDiscountEntity(
        OrderTransfer $orderTransfer,
        ObjectCollection $salesOrderDiscounts
    ) {
        foreach ($salesOrderDiscounts as $salesOrderDiscountEntity) {
            foreach ($orderTransfer->getItems() as $itemTransfer) {
                $this->assertItemRequirements($itemTransfer);
                $this->addItemCalculatedDiscounts($itemTransfer, $salesOrderDiscountEntity);
            }
            $this->addOrderExpenseCalculatedDiscounts($orderTransfer, $salesOrderDiscountEntity);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     *
     * @return void
     */
    protected function addItemCalculatedDiscounts(
        ItemTransfer $itemTransfer,
        SpySalesDiscount $salesOrderDiscountEntity
    ) {
        if ($itemTransfer->getIdSalesOrderItem() !== $salesOrderDiscountEntity->getFkSalesOrderItem() ||
            $salesOrderDiscountEntity->getFkSalesOrderItemOption() !== null
        ) {
            return;
        }

        $calculatedDiscountTransfer = $this->hydrateCalculatedDiscountTransferFromEntity(
            $salesOrderDiscountEntity,
            $itemTransfer->getQuantity()
        );

        $this->updateItemGrossPriceWithDiscounts($itemTransfer, $calculatedDiscountTransfer);

        $itemTransfer->addCalculatedDiscount($calculatedDiscountTransfer);
    }



    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     *
     * @return void
     */
    protected function addOrderExpenseCalculatedDiscounts(
        OrderTransfer $orderTransfer,
        SpySalesDiscount $salesOrderDiscountEntity
    ) {

        if ($salesOrderDiscountEntity->getFkSalesExpense() === null) {
            return;
        }

        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            if ($expenseTransfer->getIdSalesExpense() !== $salesOrderDiscountEntity->getFkSalesExpense()) {
                continue;
            }

            $calculatedDiscountTransfer = $this->hydrateCalculatedDiscountTransferFromEntity(
                $salesOrderDiscountEntity,
                $expenseTransfer->getQuantity()
            );
            $expenseTransfer->addCalculatedDiscount($calculatedDiscountTransfer);

            $this->updateExpenseGrossPriceWithDiscounts($expenseTransfer, $calculatedDiscountTransfer);
            $this->setExpenseRefundableAmount($expenseTransfer, $calculatedDiscountTransfer);
        }
    }

    /**
     * @param \Orm\Zed\Sales\Persistence\SpySalesDiscount $salesOrderDiscountEntity
     * @param int $quantity
     *
     * @return \Generated\Shared\Transfer\CalculatedDiscountTransfer
     */
    protected function hydrateCalculatedDiscountTransferFromEntity(SpySalesDiscount $salesOrderDiscountEntity, $quantity)
    {
        $quantity = !empty($quantity) ? $quantity : 1;

        $calculatedDiscountTransfer = new CalculatedDiscountTransfer();
        $calculatedDiscountTransfer->fromArray($salesOrderDiscountEntity->toArray(), true);
        $calculatedDiscountTransfer->setQuantity($quantity);
        $calculatedDiscountTransfer->setUnitGrossAmount($salesOrderDiscountEntity->getAmount());
        $calculatedDiscountTransfer->setSumGrossAmount($salesOrderDiscountEntity->getAmount() * $quantity);

        foreach ($salesOrderDiscountEntity->getDiscountCodes() as $discountCodeEntity) {
            $calculatedDiscountTransfer->setVoucherCode($discountCodeEntity->getCode());
        }

        return $calculatedDiscountTransfer;
    }


    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function updateExpenseGrossPriceWithDiscounts(
        ExpenseTransfer $expenseTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $expenseTransfer->setUnitGrossPriceWithDiscounts(
            $expenseTransfer->getUnitGrossPrice() - $calculatedDiscountTransfer->getUnitGrossAmount()
        );

        $expenseTransfer->setSumGrossPriceWithDiscounts(
            $expenseTransfer->getSumGrossPrice() - $calculatedDiscountTransfer->getSumGrossAmount()
        );
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return \Orm\Zed\Sales\Persistence\SpySalesDiscount[]|\Propel\Runtime\Collection\ObjectCollection
     */
    protected function getSalesOrderDiscounts(OrderTransfer $orderTransfer)
    {
        return $this->discountQueryContainer
            ->querySalesDisount()
            ->findByFkSalesOrder($orderTransfer->getIdSalesOrder());
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function assertItemDiscountsRequirements(OrderTransfer $orderTransfer)
    {
        $orderTransfer->requireIdSalesOrder();
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return void
     */
    protected function assertItemRequirements(ItemTransfer $itemTransfer)
    {
        $itemTransfer->requireQuantity()->requireIdSalesOrderItem();
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     *
     * @return void
     */
    protected function addExpenseDiscountAmountDefaults(ExpenseTransfer $expenseTransfer)
    {
        $expenseTransfer->setUnitGrossPriceWithDiscounts($expenseTransfer->getUnitGrossPrice());
        $expenseTransfer->setSumGrossPriceWithDiscounts($expenseTransfer->getSumGrossPrice());
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function setExpenseGrossPriceWithDiscountsToDefaults($orderTransfer)
    {
        foreach ($orderTransfer->getExpenses() as $expenseTransfer) {
            $expenseTransfer->setSumGrossPriceWithDiscounts($expenseTransfer->getSumGrossPrice());
            $expenseTransfer->setUnitGrossPriceWithDiscounts($expenseTransfer->getUnitGrossPrice());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ExpenseTransfer $expenseTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function setExpenseRefundableAmount(
        ExpenseTransfer $expenseTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $expenseTransfer->setRefundableAmount(
            $expenseTransfer->getRefundableAmount() - $calculatedDiscountTransfer->getSumGrossAmount()
        );
    }

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     *
     * @return void
     */
    protected function setItemGrossPriceWithDiscountsToDefaults(OrderTransfer $orderTransfer)
    {
        foreach ($orderTransfer->getItems() as $itemTransfer) {
            $itemTransfer->setUnitGrossPriceWithDiscounts($itemTransfer->getUnitGrossPrice());
            $itemTransfer->setSumGrossPriceWithDiscounts($itemTransfer->getSumGrossPrice());
        }
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\CalculatedDiscountTransfer $calculatedDiscountTransfer
     *
     * @return void
     */
    protected function updateItemGrossPriceWithDiscounts(
        ItemTransfer $itemTransfer,
        CalculatedDiscountTransfer $calculatedDiscountTransfer
    ) {
        $itemTransfer->setUnitGrossPriceWithDiscounts(
            $itemTransfer->getUnitGrossPrice() - $calculatedDiscountTransfer->getUnitGrossAmount()
        );

        $itemTransfer->setSumGrossPriceWithDiscounts(
            $itemTransfer->getSumGrossPrice() - $calculatedDiscountTransfer->getSumGrossAmount()
        );
    }
}

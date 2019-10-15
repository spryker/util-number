<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Stock\Persistence;

use Generated\Shared\Transfer\StockCriteriaFilterTransfer;
use Generated\Shared\Transfer\StockTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Stock\Persistence\Map\SpyStockTableMap;
use Orm\Zed\Stock\Persistence\SpyStockQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \Spryker\Zed\Stock\Persistence\StockPersistenceFactory getFactory()
 */
class StockRepository extends AbstractRepository implements StockRepositoryInterface
{
    /**
     * @return string[]
     */
    public function getStockNames(): array
    {
        $stockQuery = $this->getFactory()
            ->createStockQuery()
            ->select(SpyStockTableMap::COL_NAME);

        return $stockQuery->find()->getData();
    }

    /**
     * @param string $storeName
     *
     * @return string[]
     */
    public function getStockNamesForStore(string $storeName): array
    {
        $stockStoreQuery = $this->getFactory()
            ->createStockStoreQuery()
            ->joinWithStock()
            ->useStoreQuery()
                ->filterByName($storeName)
            ->endUse()
            ->select([SpyStockTableMap::COL_NAME]);

        return $stockStoreQuery->find()->getData();
    }

    /**
     * @param int $idStock
     *
     * @return \Generated\Shared\Transfer\StockTransfer|null
     */
    public function findStockById(int $idStock): ?StockTransfer
    {
        $stockEntity = $this->getFactory()
            ->createStockQuery()
            ->filterByIdStock($idStock)
            ->findOne();

        if ($stockEntity === null) {
            return null;
        }

        return $this->getFactory()
            ->createStockMapper()
            ->mapStockEntityToStockTransfer($stockEntity, new StockTransfer());
    }

    /**
     * @param \Generated\Shared\Transfer\StockCriteriaFilterTransfer $stockCriteriaFilterTransfer
     *
     * @return \Generated\Shared\Transfer\StockTransfer[]
     */
    public function getStocksWithRelatedStoresByCriteriaFilter(StockCriteriaFilterTransfer $stockCriteriaFilterTransfer): array
    {
        $stockQuery = $this->getFactory()
            ->createStockQuery()
            ->leftJoinWithStockStore()
            ->useStockStoreQuery(null, Criteria::LEFT_JOIN)
                ->leftJoinWithStore()
            ->endUse();
        $stockQuery = $this->applyStockQueryFilters($stockQuery, $stockCriteriaFilterTransfer);

        return $this->getFactory()
            ->createStockMapper()
            ->mapStockEntitiesToStockTransfers($stockQuery->find()->getArrayCopy());
    }

    /**
     * @param string $stockName
     *
     * @return \Generated\Shared\Transfer\StockTransfer|null
     */
    public function findStockByName(string $stockName): ?StockTransfer
    {
        $stockEntity = $this->getFactory()
            ->createStockQuery()
            ->filterByName($stockName)
            ->findOne();

        if ($stockEntity === null) {
            return null;
        }

        return $this->getFactory()
            ->createStockMapper()
            ->mapStockEntityToStockTransfer($stockEntity, new StockTransfer());
    }

    /**
     * @param int $idStock
     *
     * @return \Generated\Shared\Transfer\StockProductTransfer[]
     */
    public function getStockProductsByIdStock(int $idStock): array
    {
        $stockProductQuery = $this->getFactory()
            ->createStockProductQuery()
            ->leftJoinWithStock()
            ->leftJoinWithSpyProduct()
            ->filterByFkStock($idStock);

        return $this->getFactory()
            ->createStockProductMapper()
            ->mapStockProductEntitiesToStockProductTransfers($stockProductQuery->find()->getArrayCopy());
    }

    /**
     * @param int $idStock
     *
     * @return \Generated\Shared\Transfer\StoreRelationTransfer
     */
    public function getStoreRelationByIdStock(int $idStock): StoreRelationTransfer
    {
        $stockStoreQuery = $this->getFactory()
            ->createStockStoreQuery()
            ->leftJoinWithStore()
            ->filterByFkStock($idStock);

        return $this->getFactory()
            ->createStockStoreRelationMapper()
            ->mapStockStoreEntitiesToStoreRelationTransfer(
                $idStock,
                $stockStoreQuery->find()->getArrayCopy(),
                new StoreRelationTransfer()
            );
    }

    /**
     * @param \Orm\Zed\Stock\Persistence\SpyStockQuery $stockQuery
     * @param \Generated\Shared\Transfer\StockCriteriaFilterTransfer $stockCriteriaFilterTransfer
     *
     * @return \Orm\Zed\Stock\Persistence\SpyStockQuery
     */
    protected function applyStockQueryFilters(SpyStockQuery $stockQuery, StockCriteriaFilterTransfer $stockCriteriaFilterTransfer): SpyStockQuery
    {
        if ($stockCriteriaFilterTransfer->getIsActive()) {
            $stockQuery->filterByIsActive(true);
        }

        if ($stockCriteriaFilterTransfer->getStoreNames()) {
            $stockQuery->useStockStoreQuery(null, Criteria::LEFT_JOIN)
                ->useStoreQuery(null, Criteria::LEFT_JOIN)
                    ->filterByName_In($stockCriteriaFilterTransfer->getStoreNames())
                ->endUse()
                ->endUse();
        }

        return $stockQuery;
    }

    /**
     * @module Store
     * @module Product
     *
     * @param string $sku
     *
     * @return \Generated\Shared\Transfer\StoreTransfer[]
     */
    public function getStoresWhereProductStockIsDefined(string $sku): array
    {
        $query = $this->getFactory()
            ->getStoreQuery()
            ->leftJoinWithStockStore()
            ->useStockStoreQuery()
                ->leftJoinWithStock()
                ->useStockQuery()
                    ->leftJoinStockProduct()
                        ->useStockProductQuery()
                            ->leftJoinWithSpyProduct()
                            ->useSpyProductQuery()
                                ->filterBySku($sku)
                            ->endUse()
                        ->endUse()
                ->endUse()
            ->endUse();

        return $this->getFactory()
            ->createStoreMapper()
            ->mapStoreEntitiesToStoreTransfers($query->find()->getArrayCopy());
    }
}

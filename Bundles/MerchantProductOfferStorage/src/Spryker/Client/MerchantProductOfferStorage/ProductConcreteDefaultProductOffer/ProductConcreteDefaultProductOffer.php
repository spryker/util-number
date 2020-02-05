<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Client\MerchantProductOfferStorage\ProductConcreteDefaultProductOffer;

use Generated\Shared\Transfer\ProductOfferStorageCriteriaTransfer;
use Spryker\Client\MerchantProductOfferStorage\Storage\ProductOfferStorageReaderInterface;
use Spryker\Client\MerchantProductOfferStorageExtension\Dependency\Plugin\ProductOfferProviderPluginInterface;

class ProductConcreteDefaultProductOffer implements ProductConcreteDefaultProductOfferInterface
{
    /**
     * @var \Spryker\Client\MerchantProductOfferStorage\Storage\ProductOfferStorageReaderInterface
     */
    protected $productOfferStorageReader;

    /**
     * @var \Spryker\Client\MerchantProductOfferStorageExtension\Dependency\Plugin\ProductOfferProviderPluginInterface
     */
    protected $defaultProductOfferPlugin;

    /**
     * @param \Spryker\Client\MerchantProductOfferStorage\Storage\ProductOfferStorageReaderInterface $productOfferStorageReader
     * @param \Spryker\Client\MerchantProductOfferStorageExtension\Dependency\Plugin\ProductOfferProviderPluginInterface $defaultProductOfferPlugin
     */
    public function __construct(ProductOfferStorageReaderInterface $productOfferStorageReader, ProductOfferProviderPluginInterface $defaultProductOfferPlugin)
    {
        $this->productOfferStorageReader = $productOfferStorageReader;
        $this->defaultProductOfferPlugin = $defaultProductOfferPlugin;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductOfferStorageCriteriaTransfer $productOfferStorageCriteriaTransfer
     *
     * @return string|null
     */
    public function findProductOfferReference(ProductOfferStorageCriteriaTransfer $productOfferStorageCriteriaTransfer): ?string
    {
        $productOfferReferences = $this->productOfferStorageReader->getProductOfferReferences($productOfferStorageCriteriaTransfer->getSku());

        if (!$productOfferReferences) {
            return null;
        }

        if ($productOfferStorageCriteriaTransfer->getProductOfferReference()
            && in_array($productOfferStorageCriteriaTransfer->getProductOfferReference(), $productOfferReferences)
        ) {
            return $productOfferStorageCriteriaTransfer->getProductOfferReference();
        }

        return $this->defaultProductOfferPlugin->provideDefaultProductOfferReference($productOfferReferences);
    }
}

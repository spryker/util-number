<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\ProductMeasurementUnitTransfer;
use Generated\Shared\Transfer\RestProductMeasurementUnitsAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Mapper\ProductMeasurementUnitMapperInterface;
use Spryker\Glue\ProductMeasurementUnitsRestApi\ProductMeasurementUnitsRestApiConfig;

class ProductMeasurementUnitRestResponseBuilder implements ProductMeasurementUnitRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Mapper\ProductMeasurementUnitMapperInterface
     */
    protected $productMeasurementUnitMapper;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\ProductMeasurementUnitsRestApi\Processor\Mapper\ProductMeasurementUnitMapperInterface $productMeasurementUnitMapper
     */
    public function __construct(
        RestResourceBuilderInterface $restResourceBuilder,
        ProductMeasurementUnitMapperInterface $productMeasurementUnitMapper
    ) {
        $this->restResourceBuilder = $restResourceBuilder;
        $this->productMeasurementUnitMapper = $productMeasurementUnitMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductMeasurementUnitTransfer $productMeasurementUnitTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createProductMeasurementUnitRestResponse(ProductMeasurementUnitTransfer $productMeasurementUnitTransfer): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        return $restResponse->addResource($this->createProductMeasurementUnitRestResource($productMeasurementUnitTransfer));
    }

    /**
     * @param \Generated\Shared\Transfer\ProductMeasurementUnitTransfer $productMeasurementUnitTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    public function createProductMeasurementUnitRestResource(ProductMeasurementUnitTransfer $productMeasurementUnitTransfer): RestResourceInterface
    {
        $restProductMeasurementUnitsAttributesTransfer = $this->productMeasurementUnitMapper
            ->mapProductMeasurementUnitTransferToRestProductMeasurementUnitsAttributesTransfer(
                $productMeasurementUnitTransfer,
                new RestProductMeasurementUnitsAttributesTransfer()
            );

        $resourceId = (string)$productMeasurementUnitTransfer->getCode();

        return $this->restResourceBuilder->createRestResource(
            ProductMeasurementUnitsRestApiConfig::RESOURCE_PRODUCT_MEASUREMENT_UNITS,
            $resourceId,
            $restProductMeasurementUnitsAttributesTransfer
        );
    }
}

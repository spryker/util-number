<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SalesProductConnector;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class SalesProductConnectorConfig extends AbstractBundleConfig
{
    protected const POPULARITY_DAYS_INTERVAL = 90;
    protected const PRODUCT_PAGE_DATA_REFRESH_DAYS_INTERVAL = 1;

    /**
     * Specification:
     * - Defines the interval in days which uses for calculate popularity.
     *
     * @api
     *
     * @return int
     */
    public function getPopularityDaysInterval(): int
    {
        return static::POPULARITY_DAYS_INTERVAL;
    }

    /**
     * Specification:
     * - Defines the interval in days which uses for retrieving productAbstractIds that are need refresh.
     *
     * @api
     *
     * @return int
     */
    public function getProductPageDataRefreshDaysInterval(): int
    {
        return static::PRODUCT_PAGE_DATA_REFRESH_DAYS_INTERVAL;
    }
}

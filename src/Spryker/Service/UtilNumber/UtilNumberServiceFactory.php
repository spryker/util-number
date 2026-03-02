<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Service\UtilNumber;

use Spryker\Service\Kernel\AbstractServiceFactory;
use Spryker\Service\UtilNumber\ConfigurationProvider\NumberFormatConfigurationProvider;
use Spryker\Service\UtilNumber\ConfigurationProvider\NumberFormatConfigurationProviderInterface;
use Spryker\Service\UtilNumber\Formatter\NumberFormatter;
use Spryker\Service\UtilNumber\Formatter\NumberFormatterFactory;
use Spryker\Service\UtilNumber\Formatter\NumberFormatterFactoryInterface;
use Spryker\Service\UtilNumber\Formatter\NumberFormatterInterface;

/**
 * @method \Spryker\Service\UtilNumber\UtilNumberConfig getConfig()
 */
class UtilNumberServiceFactory extends AbstractServiceFactory
{
    public function createNumberFormatter(): NumberFormatterInterface
    {
        return new NumberFormatter($this->createNumberFormatterFactory());
    }

    public function createNumberFormatConfigurationProvider(): NumberFormatConfigurationProviderInterface
    {
        return new NumberFormatConfigurationProvider($this->createNumberFormatterFactory());
    }

    public function createNumberFormatterFactory(): NumberFormatterFactoryInterface
    {
        return new NumberFormatterFactory($this->getConfig());
    }
}

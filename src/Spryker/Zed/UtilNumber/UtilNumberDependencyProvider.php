<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\UtilNumber;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\UtilNumber\UtilNumberConfig getConfig()
 */
class UtilNumberDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const SERVICE_UTIL_NUMBER = 'SERVICE_UTIL_NUMBER';

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addUtilNumberService($container);

        return $container;
    }

    protected function addUtilNumberService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_NUMBER, function (Container $container) {
            return $container->getLocator()->utilNumber()->service();
        });

        return $container;
    }
}

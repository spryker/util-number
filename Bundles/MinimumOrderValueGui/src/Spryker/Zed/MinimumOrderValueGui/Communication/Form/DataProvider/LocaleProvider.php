<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MinimumOrderValueGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\LocaleTransfer;
use Spryker\Shared\MinimumOrderValueGui\MinimumOrderValueGuiConstants;
use Spryker\Zed\MinimumOrderValueGui\Dependency\Facade\MinimumOrderValueGuiToLocaleInterface;

class LocaleProvider
{
    /**
     * @var \Spryker\Zed\MinimumOrderValueGui\Dependency\Facade\MinimumOrderValueGuiToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @param \Spryker\Zed\MinimumOrderValueGui\Dependency\Facade\MinimumOrderValueGuiToLocaleInterface $localeFacade
     */
    public function __construct(MinimumOrderValueGuiToLocaleInterface $localeFacade)
    {
        $this->localeFacade = $localeFacade;
    }

    /**
     * @param bool $includeDefault
     *
     * @return \Generated\Shared\Transfer\LocaleTransfer[]
     */
    public function getLocaleCollection($includeDefault = false)
    {
        $result = [];

        if ($includeDefault) {
            $result[] = (new LocaleTransfer())
                ->setLocaleName(MinimumOrderValueGuiConstants::MINIMUM_ORDER_VALUE_DEFAULT_LOCALE);
        }

        foreach ($this->localeFacade->getLocaleCollection() as $localeCode => $localeTransfer) {
            $result[] = $localeTransfer;
        }

        return $result;
    }

    /**
     * @param string $localeCode
     *
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    public function getLocaleTransfer($localeCode)
    {
        return $this->localeFacade->getLocale($localeCode);
    }

    /**
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    public function getCurrentLocale()
    {
        return $this->localeFacade->getCurrentLocale();
    }
}

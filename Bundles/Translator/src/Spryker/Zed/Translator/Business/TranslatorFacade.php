<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Translator\Business;

use Spryker\Shared\Kernel\Store;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\Translator\Business\TranslatorBusinessFactory getFactory()
 */
class TranslatorFacade extends AbstractFacade implements TranslatorFacadeInterface
{
    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return void
     */
    public function generateTranslationCache(): void
    {
        $translator = $this->getFactory()->createTranslator();
        $locales = Store::getInstance()->getLocales();

        $translator->generateCache($locales);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @return void
     */
    public function clearTranslationCache(): void
    {
        $this->getFactory()->createCacheClearer()->clearCache();
    }
}

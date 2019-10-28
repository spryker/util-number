<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ConfigurableBundle\Business\Reader;

use ArrayObject;
use Generated\Shared\Transfer\ConfigurableBundleTemplateSlotFilterTransfer;
use Generated\Shared\Transfer\ConfigurableBundleTemplateSlotTransfer;
use Generated\Shared\Transfer\ConfigurableBundleTemplateSlotCollectionTransfer;
use Generated\Shared\Transfer\ConfigurableBundleTemplateSlotResponseTransfer;
use Generated\Shared\Transfer\MessageTransfer;
use Spryker\Zed\ConfigurableBundle\Business\Expander\ConfigurableBundleTemplateSlotProductListExpanderInterface;
use Spryker\Zed\ConfigurableBundle\Business\Expander\ConfigurableBundleTranslationExpanderInterface;
use Spryker\Zed\ConfigurableBundle\Persistence\ConfigurableBundleRepositoryInterface;

class ConfigurableBundleTemplateSlotReader implements ConfigurableBundleTemplateSlotReaderInterface
{
    protected const GLOSSARY_KEY_CONFIGURABLE_BUNDLE_TEMPLATE_SLOT_NOT_EXISTS = 'configurable-bundle.slot.validation.error.not_exists';

    /**
     * @var \Spryker\Zed\ConfigurableBundle\Persistence\ConfigurableBundleRepositoryInterface
     */
    protected $configurableBundleRepository;

    /**
     * @var \Spryker\Zed\ConfigurableBundle\Business\Expander\ConfigurableBundleTranslationExpanderInterface
     */
    protected $configurableBundleTranslationExpander;

    /**
     * @var \Spryker\Zed\ConfigurableBundle\Business\Expander\ConfigurableBundleTemplateSlotProductListExpanderInterface
     */
    protected $configurableBundleTemplateSlotProductListExpander;

    /**
     * @param \Spryker\Zed\ConfigurableBundle\Persistence\ConfigurableBundleRepositoryInterface $configurableBundleRepository
     * @param \Spryker\Zed\ConfigurableBundle\Business\Expander\ConfigurableBundleTranslationExpanderInterface $configurableBundleTranslationExpander
     * @param \Spryker\Zed\ConfigurableBundle\Business\Expander\ConfigurableBundleTemplateSlotProductListExpanderInterface $configurableBundleTemplateSlotProductListExpander
     */
    public function __construct(
        ConfigurableBundleRepositoryInterface $configurableBundleRepository,
        ConfigurableBundleTranslationExpanderInterface $configurableBundleTranslationExpander,
        ConfigurableBundleTemplateSlotProductListExpanderInterface $configurableBundleTemplateSlotProductListExpander
    ) {
        $this->configurableBundleRepository = $configurableBundleRepository;
        $this->configurableBundleTranslationExpander = $configurableBundleTranslationExpander;
        $this->configurableBundleTemplateSlotProductListExpander = $configurableBundleTemplateSlotProductListExpander;
    }

    /**
     * @param \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
     *
     * @return \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotResponseTransfer
     */
    public function getConfigurableBundleTemplateSlot(
        ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
    ): ConfigurableBundleTemplateSlotResponseTransfer {
        $configurableBundleTemplateSlotTransfer = $this->configurableBundleRepository
            ->findConfigurableBundleTemplateSlot($configurableBundleTemplateSlotFilterTransfer);

        if (!$configurableBundleTemplateSlotTransfer) {
            return $this->getErrorResponse(static::GLOSSARY_KEY_CONFIGURABLE_BUNDLE_TEMPLATE_SLOT_NOT_EXISTS);
        }

        $configurableBundleTemplateSlotTransfer = $this->expandConfigurableBundleTemplateSlot(
            $configurableBundleTemplateSlotTransfer,
            $configurableBundleTemplateSlotFilterTransfer
        );

        return (new ConfigurableBundleTemplateSlotResponseTransfer())
            ->setConfigurableBundleTemplateSlot($configurableBundleTemplateSlotTransfer)
            ->setIsSuccessful(true);
    }

    /**
     * @param \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
     *
     * @return \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotCollectionTransfer
     */
    public function getConfigurableBundleTemplateSlotCollection(
        ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
    ): ConfigurableBundleTemplateSlotCollectionTransfer {
        $configurableBundleTemplateSlotCollectionTransfer = $this->configurableBundleRepository
            ->getConfigurableBundleTemplateSlotCollection($configurableBundleTemplateSlotFilterTransfer);

        foreach ($configurableBundleTemplateSlotTransfers as $configurableBundleTemplateSlotTransfer) {
            $this->expandConfigurableBundleTemplateSlot($configurableBundleTemplateSlotTransfer, $configurableBundleTemplateSlotFilterTransfer);
        }

        return (new ConfigurableBundleTemplateSlotCollectionTransfer())
            ->setConfigurableBundleTemplateSlots($configurableBundleTemplateSlotTransfers);
    }

    /**
     * @param \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotTransfer $configurableBundleTemplateSlotTransfer
     * @param \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
     *
     * @return \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotTransfer
     */
    protected function expandConfigurableBundleTemplateSlot(
        ConfigurableBundleTemplateSlotTransfer $configurableBundleTemplateSlotTransfer,
        ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
    ): ConfigurableBundleTemplateSlotTransfer {
        $configurableBundleTemplateSlotTransfer = $this->configurableBundleTemplateSlotProductListExpander
            ->expandConfigurableBundleTemplateSlotWithProductList($configurableBundleTemplateSlotTransfer);

        $configurableBundleTemplateSlotTransfer = $this->configurableBundleTranslationExpander
            ->expandConfigurableBundleTemplateSlotWithTranslations(
                $configurableBundleTemplateSlotTransfer,
                $configurableBundleTemplateSlotFilterTransfer->getTranslationLocales()
            );

        $configurableBundleTemplateTransfer = $this->configurableBundleTranslationExpander
            ->expandConfigurableBundleTemplateWithTranslations(
                $configurableBundleTemplateSlotTransfer->getConfigurableBundleTemplate(),
                $configurableBundleTemplateSlotFilterTransfer->getTranslationLocales()
            );

        $configurableBundleTemplateSlotTransfer->setConfigurableBundleTemplate($configurableBundleTemplateTransfer);

        return $configurableBundleTemplateSlotTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotTransfer[] $configurableBundleTemplateSlotTransfers
     * @param \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
     *
     * @return \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotTransfer[]
     */
    protected function expandConfigurableBundleTemplateSlotTransfersWithTranslations(
        array $configurableBundleTemplateSlotTransfers,
        ConfigurableBundleTemplateSlotFilterTransfer $configurableBundleTemplateSlotFilterTransfer
    ): array {
        foreach ($configurableBundleTemplateSlotTransfers as $configurableBundleTemplateSlotTransfer) {
            $this->expandConfigurableBundleTemplateSlot($configurableBundleTemplateSlotTransfer, $configurableBundleTemplateSlotFilterTransfer);
        }

        return $configurableBundleTemplateSlotTransfers;
    }

    /**
     * @param string $message
     *
     * @return \Generated\Shared\Transfer\ConfigurableBundleTemplateSlotResponseTransfer
     */
    protected function getErrorResponse(string $message): ConfigurableBundleTemplateSlotResponseTransfer
    {
        $messageTransfer = (new MessageTransfer())
            ->setValue($message);

        return (new ConfigurableBundleTemplateSlotResponseTransfer())
            ->setIsSuccessful(false)
            ->addMessage($messageTransfer);
    }
}

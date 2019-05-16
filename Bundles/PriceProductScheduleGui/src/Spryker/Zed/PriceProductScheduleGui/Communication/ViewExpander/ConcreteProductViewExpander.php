<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PriceProductScheduleGui\Communication\ViewExpander;

use Generated\Shared\Transfer\PriceTypeTransfer;
use Generated\Shared\Transfer\TabItemTransfer;
use Generated\Shared\Transfer\TabsViewTransfer;
use Spryker\Zed\PriceProductScheduleGui\Communication\Formatter\RowFormatterInterface;
use Spryker\Zed\PriceProductScheduleGui\Communication\Table\PriceProductScheduleConcreteTable;
use Spryker\Zed\PriceProductScheduleGui\Dependency\Facade\PriceProductScheduleGuiToPriceProductFacadeInterface;
use Spryker\Zed\PriceProductScheduleGui\Dependency\Facade\PriceProductScheduleGuiToTranslatorFacadeInterface;

class ConcreteProductViewExpander implements ConcreteProductViewExpanderInterface
{
    protected const PRICE_TYPE_TEMPLATE = '@PriceProductScheduleGui/_partials/price-type-tabs/price-type-tab.twig';
    protected const DEFAULT_TITLE = 'Price type: ';

    /**
     * @var \Spryker\Zed\PriceProductScheduleGui\Dependency\Facade\PriceProductScheduleGuiToPriceProductFacadeInterface
     */
    protected $priceProductFacade;

    /**
     * @var \Spryker\Zed\PriceProductScheduleGui\Dependency\Facade\PriceProductScheduleGuiToTranslatorFacadeInterface
     */
    protected $translatorFacade;

    /**
     * @var \Spryker\Zed\PriceProductScheduleGui\Communication\Formatter\RowFormatterInterface
     */
    protected $rowFormatter;

    /**
     * @param \Spryker\Zed\PriceProductScheduleGui\Dependency\Facade\PriceProductScheduleGuiToPriceProductFacadeInterface $priceProductFacade
     * @param \Spryker\Zed\PriceProductScheduleGui\Dependency\Facade\PriceProductScheduleGuiToTranslatorFacadeInterface $translatorFacade
     * @param \Spryker\Zed\PriceProductScheduleGui\Communication\Formatter\RowFormatterInterface $rowFormatter
     */
    public function __construct(
        PriceProductScheduleGuiToPriceProductFacadeInterface $priceProductFacade,
        PriceProductScheduleGuiToTranslatorFacadeInterface $translatorFacade,
        RowFormatterInterface $rowFormatter
    ) {
        $this->priceProductFacade = $priceProductFacade;
        $this->translatorFacade = $translatorFacade;
        $this->rowFormatter = $rowFormatter;
    }

    /**
     * @param array $viewData
     *
     * @return array
     */
    public function expandProductConcreteEditViewData(array $viewData): array
    {
        $priceTypeTransfers = $this->priceProductFacade
            ->getPriceTypeValues();
        $priceTypeTabsViewTransfer = new TabsViewTransfer();
        $tablesByPriceType = [];

        foreach ($priceTypeTransfers as $priceTypeTransfer) {
            $priceTypeTabItemTransfer = $this->createPriceTypeTab($priceTypeTransfer);
            $priceTypeTabsViewTransfer->addTab($priceTypeTabItemTransfer);
            $tablesByPriceType[$priceTypeTransfer->getName()] = $this->createTableByPriceType(
                $viewData,
                $priceTypeTransfer
            )->render();
        }

        if ($priceTypeTabsViewTransfer->getTabs()->count() > 0) {
            $priceTypeTabsViewTransfer->setActiveTabName($priceTypeTabsViewTransfer->getTabs()[0]->getName());
        }

        $viewData['priceTypeTabs'] = $priceTypeTabsViewTransfer;
        $viewData['tablesByPriceType'] = $tablesByPriceType;

        return $viewData;
    }

    /**
     * @param \Generated\Shared\Transfer\PriceTypeTransfer $priceTypeTransfer
     *
     * @return \Generated\Shared\Transfer\TabItemTransfer
     */
    protected function createPriceTypeTab(PriceTypeTransfer $priceTypeTransfer): TabItemTransfer
    {
        $tabItemTransfer = new TabItemTransfer();

        return $tabItemTransfer->setName($priceTypeTransfer->getName())
            ->setTitle($this->translate(static::DEFAULT_TITLE) . $priceTypeTransfer->getName())
            ->setTemplate(static::PRICE_TYPE_TEMPLATE);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    protected function translate(string $text): string
    {
        return $this->translatorFacade->trans($text);
    }

    /**
     * @param array $viewData
     * @param \Generated\Shared\Transfer\PriceTypeTransfer $priceTypeTransfer
     *
     * @return \Spryker\Zed\PriceProductScheduleGui\Communication\Table\PriceProductScheduleConcreteTable
     */
    protected function createTableByPriceType(array $viewData, PriceTypeTransfer $priceTypeTransfer): PriceProductScheduleConcreteTable
    {
        return (
            new PriceProductScheduleConcreteTable(
                $viewData['idProduct'],
                $priceTypeTransfer->getIdPriceType(),
                $this->rowFormatter
            )
        );
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ProductManagement\Communication\Table;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Spryker\Zed\Product\Persistence\ProductQueryContainer;
use Spryker\Zed\Product\Persistence\ProductQueryContainerInterface;
use Spryker\Zed\ProductManagement\Communication\Helper\ProductTypeHelper;
use Spryker\Zed\ProductManagement\Dependency\Facade\ProductManagementToProductBridge;
use Spryker\Zed\ProductManagement\Persistence\ProductManagementRepository;
use Spryker\Zed\ProductManagement\Persistence\ProductManagementRepositoryInterface;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ProductManagement
 * @group Communication
 * @group Table
 * @group ProductTableTest
 * Add your own group annotations below this line
 */
class ProductTableTest extends Unit
{
    /**
     * @uses \Spryker\Zed\Twig\Communication\Plugin\Application\TwigApplicationPlugin::SERVICE_TWIG
     *
     * @var string
     */
    protected const SERVICE_TWIG = 'twig';

    /**
     * @var array<string, string>
     */
    protected const PRODUCT_NAME = [
        self::LOCALE_NAME_EN => 'Product name en_US',
        self::LOCALE_NAME_DE => 'Product name de_DE',
    ];

    /**
     * @var string
     */
    protected const LOCALE_NAME_DE = 'de_DE';

    /**
     * @var string
     */
    protected const LOCALE_NAME_EN = 'en_US';

    /**
     * @var string
     */
    protected const RENDERED_STRING = 'output';

    /**
     * @var \SprykerTest\Zed\ProductManagement\ProductManagementCommunicationTester
     */
    protected $tester;

    /**
     * @var array<\Generated\Shared\Transfer\LocaleTransfer>
     */
    protected $localeTransfers;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensureProductAbstractTableIsEmpty();
        $this->setupLocales();
        $this->registerTwigServiceMock();
    }

    /**
     * @return void
     */
    public function testFetchDataShouldReturnProductsWithDefaultLocale(): void
    {
        // Arrange
        $productAbstractTransfer1 = $this->tester->haveProductAbstract();
        $productAbstractTransfer2 = $this->tester->haveProductAbstract();

        $localizedAttributeDE1 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_DE], $this->localeTransfers[static::LOCALE_NAME_DE]);
        $localizedAttributeDE2 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_DE], $this->localeTransfers[static::LOCALE_NAME_DE]);
        $localizedAttributeEN1 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_EN], $this->localeTransfers[static::LOCALE_NAME_EN]);
        $localizedAttributeEN2 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_EN], $this->localeTransfers[static::LOCALE_NAME_EN]);

        $this->tester->addLocalizedAttributesToProductAbstract($productAbstractTransfer1, [$localizedAttributeDE1, $localizedAttributeEN1]);
        $this->tester->addLocalizedAttributesToProductAbstract($productAbstractTransfer2, [$localizedAttributeDE2, $localizedAttributeEN2]);

        $productTable = $this->createProductTableMock($this->localeTransfers[static::LOCALE_NAME_DE]);

        // Act
        $productTableData = $productTable->fetchData();

        // Assert
        $expectedproductTableData = [
            $this->buildExpectedRow($productAbstractTransfer1->getIdProductAbstract(), $productAbstractTransfer1->getSku(), static::PRODUCT_NAME[static::LOCALE_NAME_DE]),
            $this->buildExpectedRow($productAbstractTransfer2->getIdProductAbstract(), $productAbstractTransfer2->getSku(), static::PRODUCT_NAME[static::LOCALE_NAME_DE]),
        ];
        $this->assertEqualsCanonicalizing($expectedproductTableData, $productTableData);
    }

    /**
     * @return void
     */
    public function testFetchDataShouldReturnProductsWithNotDefaultLocaleWhenDefaultLocaleDoesNotPresentForTheProduct(): void
    {
        // Arrange
        $productAbstractTransfer1 = $this->tester->haveProductAbstract();
        $productAbstractTransfer2 = $this->tester->haveProductAbstract();

        $localizedAttributeDE1 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_DE], $this->localeTransfers[static::LOCALE_NAME_DE]);
        $localizedAttributeEN1 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_EN], $this->localeTransfers[static::LOCALE_NAME_EN]);
        $localizedAttributeEN2 = $this->createLocalizedAttributesTransfer(static::PRODUCT_NAME[static::LOCALE_NAME_EN], $this->localeTransfers[static::LOCALE_NAME_EN]);

        $this->tester->addLocalizedAttributesToProductAbstract($productAbstractTransfer1, [$localizedAttributeEN1]);
        $this->tester->addLocalizedAttributesToProductAbstract($productAbstractTransfer2, [$localizedAttributeDE1, $localizedAttributeEN2]);

        $productTable = $this->createProductTableMock($this->localeTransfers[static::LOCALE_NAME_DE]);

        // Act
        $productTableData = $productTable->fetchData();

        // Assert
        $expectedproductTableData = [
            $this->buildExpectedRow($productAbstractTransfer1->getIdProductAbstract(), $productAbstractTransfer1->getSku(), static::PRODUCT_NAME[static::LOCALE_NAME_EN]),
            $this->buildExpectedRow($productAbstractTransfer2->getIdProductAbstract(), $productAbstractTransfer2->getSku(), static::PRODUCT_NAME[static::LOCALE_NAME_DE]),
        ];

        $this->assertEqualsCanonicalizing($expectedproductTableData, $productTableData);
    }

    /**
     * @return void
     */
    protected function registerTwigServiceMock(): void
    {
        $this->tester->getContainer()
            ->set(static::SERVICE_TWIG, $this->getTwigMock());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Twig\Environment
     */
    protected function getTwigMock(): Environment
    {
        $twigMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $twigMock->method('render')
            ->willReturn(static::RENDERED_STRING);
        $twigMock->method('getLoader')
            ->willReturn($this->createChainLoader());

        return $twigMock;
    }

    /**
     * @return \Twig\Loader\LoaderInterface
     */
    protected function createChainLoader(): LoaderInterface
    {
        return new ChainLoader();
    }

    /**
     * @return void
     */
    protected function setupLocales(): void
    {
        if (!isset($this->localeTransfers[static::LOCALE_NAME_DE])) {
            $this->localeTransfers[static::LOCALE_NAME_DE] = $this->tester->haveLocale([LocaleTransfer::LOCALE_NAME => static::LOCALE_NAME_DE]);
        }

        if (!isset($this->localeTransfers[static::LOCALE_NAME_EN])) {
            $this->localeTransfers[static::LOCALE_NAME_EN] = $this->tester->haveLocale([LocaleTransfer::LOCALE_NAME => static::LOCALE_NAME_EN]);
        }
    }

    /**
     * @param int $idLocale
     * @param string $localeName
     *
     * @return \Generated\Shared\Transfer\LocaleTransfer
     */
    protected function createLocaleTransfer(int $idLocale, string $localeName): LocaleTransfer
    {
        return (new LocaleTransfer())
            ->setIdLocale($idLocale)
            ->setIsActive(true)
            ->setLocaleName($localeName);
    }

    /**
     * @param string $localizedAttributeName
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\LocalizedAttributesTransfer
     */
    protected function createLocalizedAttributesTransfer(string $localizedAttributeName, LocaleTransfer $localeTransfer): LocalizedAttributesTransfer
    {
        return (new LocalizedAttributesTransfer())
            ->setName($localizedAttributeName)
            ->setLocale($localeTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \SprykerTest\Zed\ProductManagement\Communication\Table\ProductTableMock
     */
    protected function createProductTableMock(LocaleTransfer $localeTransfer): ProductTableMock
    {
        $productQueryContainer = $this->createProductQueryContainer();
        $productTypeHelper = $this->createProductTypeHelper($productQueryContainer);
        $productManagementRepository = $this->createProductManagementRepository();
        $productManagementToProductBridge = $this->createProductManagementToProductBridge();

        return new ProductTableMock(
            $productQueryContainer,
            $localeTransfer,
            $productTypeHelper,
            $productManagementRepository,
            [],
            $productManagementToProductBridge,
        );
    }

    /**
     * @return \Spryker\Zed\ProductManagement\Dependency\Facade\ProductManagementToProductBridge
     */
    protected function createProductManagementToProductBridge(): ProductManagementToProductBridge
    {
        return new ProductManagementToProductBridge(
            $this->tester->getLocator()->product()->facade(),
        );
    }

    /**
     * @return \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface
     */
    protected function createProductQueryContainer(): ProductQueryContainerInterface
    {
        return new ProductQueryContainer();
    }

    /**
     * @param \Spryker\Zed\Product\Persistence\ProductQueryContainer $productQueryContainer
     *
     * @return \Spryker\Zed\ProductManagement\Communication\Helper\ProductTypeHelper
     */
    protected function createProductTypeHelper(ProductQueryContainer $productQueryContainer): ProductTypeHelper
    {
        return new ProductTypeHelper($productQueryContainer);
    }

    /**
     * @return \Spryker\Zed\ProductManagement\Persistence\ProductManagementRepositoryInterface
     */
    protected function createProductManagementRepository(): ProductManagementRepositoryInterface
    {
        return new ProductManagementRepository();
    }

    /**
     * @param int $idProductAbstract
     * @param string $sku
     * @param string $name
     *
     * @return array
     */
    protected function buildExpectedRow(int $idProductAbstract, string $sku, string $name): array
    {
        return [
            ProductTableMock::COL_ID_PRODUCT_ABSTRACT => $idProductAbstract,
            ProductTableMock::COL_SKU => $sku,
            ProductTableMock::COL_NAME => $name,
            ProductTableMock::COL_TAX_SET => null,
            ProductTableMock::COL_VARIANT_COUNT => 0,
            ProductTableMock::COL_STATUS => static::RENDERED_STRING,
            ProductTableMock::COL_PRODUCT_TYPES => 'Product',
            ProductTableMock::COL_STORE_RELATION => '',
            ProductTableMock::COL_ACTIONS => sprintf('%s %s %s', static::RENDERED_STRING, static::RENDERED_STRING, static::RENDERED_STRING),
        ];
    }
}

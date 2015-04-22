<?php

namespace SprykerFeature\Zed\CategoryExporter\Communication\Plugin;

use SprykerFeature\Zed\CategoryExporter\Communication\CategoryExporterDependencyContainer;
use SprykerEngine\Zed\Kernel\Communication\AbstractPlugin;
use SprykerEngine\Zed\Kernel\Locator;
use SprykerFeature\Zed\FrontendExporter\Dependency\Plugin\DataProcessorPluginInterface;
use SprykerFeature\Zed\FrontendExporter\Business\Model\BatchResultInterface;
use SprykerFeature\Shared\Category\CategoryResourceSettings;

/**
 * @method CategoryExporterDependencyContainer getDependencyContainer()
 */
class CategoryNodeProcessorPlugin extends AbstractPlugin implements DataProcessorPluginInterface
{
    /**
     * @return string
     */
    public function getProcessableType()
    {
        return CategoryResourceSettings::ITEM_TYPE;
    }

    /**
     * @param BatchResultInterface $results
     *
     * @return bool
     */
    public function isFailed(BatchResultInterface $results)
    {
        return $results->getFailedCount() > 0;
    }

    /**
     * @param array $resultSet
     * @param array $processedResultSet
     * @param string $locale
     *
     * @return array
     */
    public function processData(array &$resultSet, array $processedResultSet, $locale)
    {
        $facade = $this->getDependencyContainer()->getCategoryExporterFacade();

        return $facade->processCategoryNodes($resultSet, $locale);
    }
}

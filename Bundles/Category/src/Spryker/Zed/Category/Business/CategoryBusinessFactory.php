<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Category\Business;

use Spryker\Zed\Category\Business\Creator\CategoryAttributeCreator;
use Spryker\Zed\Category\Business\Creator\CategoryAttributeCreatorInterface;
use Spryker\Zed\Category\Business\Creator\CategoryClosureTableCreator;
use Spryker\Zed\Category\Business\Creator\CategoryClosureTableCreatorInterface;
use Spryker\Zed\Category\Business\Creator\CategoryCreator;
use Spryker\Zed\Category\Business\Creator\CategoryCreatorInterface;
use Spryker\Zed\Category\Business\Creator\CategoryNodeCreator;
use Spryker\Zed\Category\Business\Creator\CategoryNodeCreatorInterface;
use Spryker\Zed\Category\Business\Creator\CategoryRelationshipCreator;
use Spryker\Zed\Category\Business\Creator\CategoryRelationshipCreatorInterface;
use Spryker\Zed\Category\Business\Creator\CategoryStoreCreator;
use Spryker\Zed\Category\Business\Creator\CategoryStoreCreatorInterface;
use Spryker\Zed\Category\Business\Creator\CategoryUrlCreator;
use Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface;
use Spryker\Zed\Category\Business\Generator\TransferGenerator;
use Spryker\Zed\Category\Business\Generator\TransferGeneratorInterface;
use Spryker\Zed\Category\Business\Generator\UrlPathGenerator;
use Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface;
use Spryker\Zed\Category\Business\Model\Category;
use Spryker\Zed\Category\Business\Model\Category\Category as CategoryEntityModel;
use Spryker\Zed\Category\Business\Model\Category\CategoryHydrator;
use Spryker\Zed\Category\Business\Model\Category\CategoryHydratorInterface;
use Spryker\Zed\Category\Business\Model\Category\CategoryInterface;
use Spryker\Zed\Category\Business\Model\CategoryAttribute\CategoryAttribute;
use Spryker\Zed\Category\Business\Model\CategoryAttribute\CategoryAttributeInterface;
use Spryker\Zed\Category\Business\Model\CategoryExtraParents\CategoryExtraParents;
use Spryker\Zed\Category\Business\Model\CategoryExtraParents\CategoryExtraParentsInterface;
use Spryker\Zed\Category\Business\Model\CategoryNode\CategoryNode;
use Spryker\Zed\Category\Business\Model\CategoryReader;
use Spryker\Zed\Category\Business\Model\CategoryReaderInterface;
use Spryker\Zed\Category\Business\Model\CategoryTemplate\CategoryTemplateSync;
use Spryker\Zed\Category\Business\Model\CategoryTemplate\CategoryTemplateSyncInterface;
use Spryker\Zed\Category\Business\Model\CategoryToucher;
use Spryker\Zed\Category\Business\Model\CategoryToucherInterface;
use Spryker\Zed\Category\Business\Model\CategoryTree\CategoryTree;
use Spryker\Zed\Category\Business\Model\CategoryTree\CategoryTreeInterface;
use Spryker\Zed\Category\Business\Model\CategoryUrl\CategoryUrl;
use Spryker\Zed\Category\Business\Model\CategoryUrl\CategoryUrlInterface;
use Spryker\Zed\Category\Business\PluginExecutor\CategoryPluginExecutor;
use Spryker\Zed\Category\Business\PluginExecutor\CategoryPluginExecutorInterface;
use Spryker\Zed\Category\Business\Publisher\CategoryNodePublisher;
use Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface;
use Spryker\Zed\Category\Business\Tree\CategoryTreeReader;
use Spryker\Zed\Category\Business\Tree\ClosureTableWriter;
use Spryker\Zed\Category\Business\Tree\ClosureTableWriterInterface;
use Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter;
use Spryker\Zed\Category\Business\Tree\NodeWriter;
use Spryker\Zed\Category\Business\Tree\NodeWriterInterface;
use Spryker\Zed\Category\CategoryDependencyProvider;
use Spryker\Zed\Category\Dependency\Facade\CategoryToEventFacadeInterface;
use Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface;
use Spryker\Zed\Category\Dependency\Facade\CategoryToTouchInterface;
use Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface;
use Spryker\Zed\Graph\Communication\Plugin\GraphPlugin;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\Category\Persistence\CategoryEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\Category\Persistence\CategoryRepositoryInterface getRepository()
 * @method \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Category\CategoryConfig getConfig()
 */
class CategoryBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryCreatorInterface
     */
    public function createCategoryCreator(): CategoryCreatorInterface
    {
        return new CategoryCreator(
            $this->getEntityManager(),
            $this->createCategoryRelationshipCreator(),
            $this->getEventFacade(),
            $this->getCategoryPostCreatePlugins()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryNodeCreatorInterface
     */
    public function createCategoryNodeCreator(): CategoryNodeCreatorInterface
    {
        return new CategoryNodeCreator(
            $this->getEntityManager(),
            $this->createCategoryNodePublisher(),
            $this->createCategoryClosureTableCreator(),
            $this->createCategoryUrlCreator(),
            $this->createCategoryToucher()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryUrlCreatorInterface
     */
    public function createCategoryUrlCreator(): CategoryUrlCreatorInterface
    {
        return new CategoryUrlCreator(
            $this->getUrlFacade(),
            $this->getRepository(),
            $this->createUrlPathGenerator(),
            $this->getCategoryUrlPathPlugins()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryAttributeCreatorInterface
     */
    public function createCategoryAttributeCreator(): CategoryAttributeCreatorInterface
    {
        return new CategoryAttributeCreator($this->getEntityManager());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryClosureTableCreatorInterface
     */
    public function createCategoryClosureTableCreator(): CategoryClosureTableCreatorInterface
    {
        return new CategoryClosureTableCreator($this->getEntityManager());
    }

    /**
     * @param array $category
     *
     * @return \Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter
     */
    public function createCategoryTreeStructure(array $category): CategoryTreeFormatter
    {
        return new CategoryTreeFormatter($category);
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\CategoryTreeReader
     */
    public function createCategoryTreeReader(): CategoryTreeReader
    {
        return new CategoryTreeReader(
            $this->getQueryContainer(),
            $this->getRepository()
        );
    }

    /**
     * @return \Spryker\Zed\Graph\Communication\Plugin\GraphPlugin
     */
    public function getGraph(): GraphPlugin
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_GRAPH);
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\Category
     */
    public function createCategory(): Category
    {
        return new Category(
            $this->createCategoryCategory(),
            $this->createCategoryNode(),
            $this->createCategoryAttribute(),
            $this->createCategoryUrl(),
            $this->createCategoryExtraParents(),
            $this->getRelationDeletePluginStack(),
            $this->getRelationUpdatePluginStack(),
            $this->createPluginExecutor(),
            $this->getEventFacade()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\Category\CategoryInterface
     */
    public function createCategoryCategory(): CategoryInterface
    {
        return new CategoryEntityModel(
            $this->getQueryContainer(),
            $this->getRepository(),
            $this->createCategoryHydrator()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryNode\CategoryNodeInterface|\Spryker\Zed\Category\Business\Model\CategoryNode\CategoryNodeDeleterInterface
     */
    public function createCategoryNode()
    {
        return new CategoryNode(
            $this->createClosureTableWriter(),
            $this->getQueryContainer(),
            $this->createCategoryTransferGenerator(),
            $this->createCategoryToucher(),
            $this->createCategoryTree(),
            $this->createCategoryNodePublisher()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryToucherInterface
     */
    public function createCategoryToucher(): CategoryToucherInterface
    {
        return new CategoryToucher($this->getTouchFacade(), $this->getQueryContainer());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryTree\CategoryTreeInterface
     */
    public function createCategoryTree(): CategoryTreeInterface
    {
        return new CategoryTree(
            $this->getQueryContainer(),
            $this->createFacade()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\CategoryFacadeInterface
     */
    public function createFacade(): CategoryFacadeInterface
    {
        return new CategoryFacade();
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryAttribute\CategoryAttributeInterface
     */
    public function createCategoryAttribute(): CategoryAttributeInterface
    {
        return new CategoryAttribute($this->getQueryContainer());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryUrl\CategoryUrlInterface
     */
    public function createCategoryUrl(): CategoryUrlInterface
    {
        return new CategoryUrl(
            $this->getQueryContainer(),
            $this->getUrlFacade(),
            $this->createUrlPathGenerator(),
            $this->getCategoryUrlPathPlugins()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryExtraParents\CategoryExtraParentsInterface
     */
    public function createCategoryExtraParents(): CategoryExtraParentsInterface
    {
        return new CategoryExtraParents(
            $this->getQueryContainer(),
            $this->createClosureTableWriter(),
            $this->createCategoryToucher(),
            $this->createCategoryTree(),
            $this->createCategoryUrl(),
            $this->createCategoryTransferGenerator()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Publisher\CategoryNodePublisherInterface
     */
    public function createCategoryNodePublisher(): CategoryNodePublisherInterface
    {
        return new CategoryNodePublisher(
            $this->getRepository(),
            $this->getEventFacade()
        );
    }

    /**
     * @return \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryRelationDeletePluginInterface[]
     */
    public function getRelationDeletePluginStack(): array
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_STACK_RELATION_DELETE);
    }

    /**
     * @return \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryRelationUpdatePluginInterface[]
     */
    public function getRelationUpdatePluginStack(): array
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_STACK_RELATION_UPDATE);
    }

    /**
     * @return \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryUrlPathPluginInterface[]
     */
    public function getCategoryUrlPathPlugins(): array
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGINS_CATEGORY_URL_PATH);
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\NodeWriterInterface
     */
    public function createNodeWriter(): NodeWriterInterface
    {
        return new NodeWriter($this->getQueryContainer(), $this->createCategoryToucher());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\ClosureTableWriterInterface
     */
    public function createClosureTableWriter(): ClosureTableWriterInterface
    {
        return new ClosureTableWriter($this->getQueryContainer());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface
     */
    public function createUrlPathGenerator(): UrlPathGeneratorInterface
    {
        return new UrlPathGenerator();
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToEventFacadeInterface
     */
    public function getEventFacade(): CategoryToEventFacadeInterface
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_EVENT);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToTouchInterface
     */
    public function getTouchFacade(): CategoryToTouchInterface
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_TOUCH);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface
     */
    public function getLocaleFacade(): CategoryToLocaleInterface
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface
     */
    public function getUrlFacade(): CategoryToUrlInterface
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::FACADE_URL);
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter
     */
    public function createCategoryTreeFormatter(): CategoryTreeFormatter
    {
        return new CategoryTreeFormatter();
    }

    /**
     * @return \Spryker\Zed\Category\Business\Generator\TransferGeneratorInterface
     */
    public function createCategoryTransferGenerator(): TransferGeneratorInterface
    {
        return new TransferGenerator();
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryTemplate\CategoryTemplateSyncInterface
     */
    public function createCategoryTemplateSync(): CategoryTemplateSyncInterface
    {
        return new CategoryTemplateSync(
            $this->getQueryContainer(),
            $this->getConfig()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\Category\CategoryHydratorInterface
     */
    public function createCategoryHydrator(): CategoryHydratorInterface
    {
        return new CategoryHydrator($this->getRepository());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Model\CategoryReaderInterface
     */
    public function createCategoryReader(): CategoryReaderInterface
    {
        return new CategoryReader(
            $this->getRepository(),
            $this->createPluginExecutor(),
            $this->createCategoryTreeReader()
        );
    }

    /**
     * @return \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryCreateAfterPluginInterface[]
     */
    public function getCategoryPostCreatePlugins(): array
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_CATEGORY_POST_CREATE);
    }

    /**
     * @return \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryUpdateAfterPluginInterface[]
     */
    public function getCategoryPostUpdatePlugins(): array
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_CATEGORY_POST_UPDATE);
    }

    /**
     * @return \Spryker\Zed\CategoryExtension\Dependency\Plugin\CategoryTransferExpanderPluginInterface[]
     */
    public function getCategoryPostReadPlugins(): array
    {
        return $this->getProvidedDependency(CategoryDependencyProvider::PLUGIN_CATEGORY_POST_READ);
    }

    /**
     * @return \Spryker\Zed\Category\Business\PluginExecutor\CategoryPluginExecutorInterface
     */
    public function createPluginExecutor(): CategoryPluginExecutorInterface
    {
        return new CategoryPluginExecutor(
            $this->getCategoryPostCreatePlugins(),
            $this->getCategoryPostUpdatePlugins(),
            $this->getCategoryPostReadPlugins()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryStoreCreatorInterface
     */
    public function createCategoryStoreCreator(): CategoryStoreCreatorInterface
    {
        return new CategoryStoreCreator($this->getEntityManager());
    }

    /**
     * @return \Spryker\Zed\Category\Business\Creator\CategoryRelationshipCreatorInterface
     */
    public function createCategoryRelationshipCreator(): CategoryRelationshipCreatorInterface
    {
        return new CategoryRelationshipCreator(
            $this->createCategoryNodeCreator(),
            $this->createCategoryAttributeCreator(),
            $this->createCategoryUrlCreator(),
            $this->createCategoryStoreCreator(),
            $this->getRelationUpdatePluginStack()
        );
    }
}

<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\CategoryPageSearch;

/**
 * Declares global environment configuration keys. Do not use it for other class constants.
 */
class CategoryPageSearchConstants
{
    /**
     * Specification:
     * - Queue name as used for processing category messages
     *
     * @api
     */
    public const CATEGORY_SYNC_SEARCH_QUEUE = 'sync.search.category';

    /**
     * Specification:
     * - Queue name as used for error category messages
     *
     * @api
     */
    public const CATEGORY_SYNC_SEARCH_ERROR_QUEUE = 'sync.search.category.error';

    /**
     * Specification:
     * - Resource name, this will use for key generating
     *
     * @api
     */
    public const CATEGORY_NODE_RESOURCE_NAME = 'category_node';

    /**
     * Specification
     * - This events will be used for category_store publishing.
     *
     * @api
     */
    public const CATEGORY_STORE_PUBLISH = 'Category.category_store.publish';

    /**
     * Specification
     * - This events will be used for category_store un-publishing.
     *
     * @api
     */
    public const CATEGORY_STORE_UNPUBLISH = 'Category.category_store.unpublish';

    /**
     * Specification:
     * - This events will be used for spy_category_store entity creation.
     *
     * @api
     */
    public const ENTITY_SPY_CATEGORY_STORE_CREATE = 'Entity.spy_category_store.create';

    /**
     * Specification:
     * - This events will be used for spy_category_store entity changes.
     *
     * @api
     */
    public const ENTITY_SPY_CATEGORY_STORE_UPDATE = 'Entity.spy_category_store.update';

    /**
     * Specification:
     * - This events will be used for spy_category_store entity deletion.
     *
     * @api
     */
    public const ENTITY_SPY_CATEGORY_STORE_DELETE = 'Entity.spy_category_store.delete';
}

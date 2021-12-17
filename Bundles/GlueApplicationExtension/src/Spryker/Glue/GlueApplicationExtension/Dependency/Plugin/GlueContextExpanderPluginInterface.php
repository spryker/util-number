<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueApplicationExtension\Dependency\Plugin;

use Generated\Shared\Transfer\GlueApiContextTransfer;

interface GlueContextExpanderPluginInterface
{
    /**
     * Specification:
     * - Expands the context with data (e.g. HTTP host) that will be used to filter Glue API application bootstrap to serve the context
     *
     * @api
     *
     * @see {@link \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\GlueApplicationBootstrapPluginInterface::isServing()}
     *
     * @param \Generated\Shared\Transfer\GlueApiContextTransfer $glueApiContextTransfer
     *
     * @return \Generated\Shared\Transfer\GlueApiContextTransfer
     */
    public function expand(GlueApiContextTransfer $glueApiContextTransfer): GlueApiContextTransfer;
}

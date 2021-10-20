<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Development\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Development\Business\DevelopmentFacadeInterface getFacade()
 * @method \Spryker\Zed\Development\Communication\DevelopmentCommunicationFactory getFactory()
 */
class GenerateServiceIdeAutoCompletionConsole extends Console
{
    /**
     * @var string
     */
    protected const OLD_COMMAND_NAME = 'dev:ide:generate-service-auto-completion';

    /**
     * @var string
     */
    public const COMMAND_NAME = 'dev:ide-auto-completion:service:generate';

    /**
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this->setName(static::COMMAND_NAME);
        $this->setDescription('Generate IDE auto completion files for Service.');
        $this->setAliases([static::OLD_COMMAND_NAME]);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getFacade()->generateServiceIdeAutoCompletion();

        $this->info('Generated Service IDE auto-completion files');

        return static::CODE_SUCCESS;
    }
}

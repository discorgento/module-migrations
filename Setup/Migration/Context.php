<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration;

use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface as ModuleDataSetup;
use Psr\Log\LoggerInterface as Logger;

final class Context
{
    public ModuleDataSetup $moduleDataSetup;
    public Logger $logger;
    public State $state;

    public function __construct(
        ModuleDataSetup $moduleDataSetup,
        Logger $logger,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->state = $state;
    }
}

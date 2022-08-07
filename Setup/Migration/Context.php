<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration;

use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface as ModuleDataSetup;

final class Context
{
    public ModuleDataSetup $moduleDataSetup;
    public State $state;

    public function __construct(
        ModuleDataSetup $moduleDataSetup,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->state = $state;
    }
}

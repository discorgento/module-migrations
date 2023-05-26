<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration;

use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleDataSetupInterface as ModuleDataSetup;
use Magento\Framework\Setup\Patch\PatchHistory;
use Psr\Log\LoggerInterface;

class Context
{
    /** @var ModuleDataSetup */
    public $moduleDataSetup;

    /** @var LoggerInterface */
    public $logger;

    /** @var PatchHistory */
    public $patchHistory;

    /** @var State */
    public $state;

    // phpcs:ignore
    public function __construct(
        ModuleDataSetup $moduleDataSetup,
        LoggerInterface $logger,
        PatchHistory $patchHistory,
        State $state
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->patchHistory = $patchHistory;
        $this->state = $state;
    }
}

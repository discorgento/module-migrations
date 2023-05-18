<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common\EavAttribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;

class Context
{
    /**
     * @var Config
     */
    public $config;

    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;

    /**
     * @var ResourceConnection
     */
    public $resourceConnection;

    // phpcs:ignore
    public function __construct(
        Config $config,
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->config = $config;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resourceConnection = $resourceConnection;
    }
}

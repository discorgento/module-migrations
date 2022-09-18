<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common\EavAttribute;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;

final class Context
{
    public EavSetupFactory $eavSetupFactory;
    public ResourceConnection $resourceConnection;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resourceConnection = $resourceConnection;
    }
}

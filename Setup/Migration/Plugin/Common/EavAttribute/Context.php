<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Plugin\Common\EavAttribute;

use Magento\Eav\Setup\EavSetupFactory;

final class Context
{
    public EavSetupFactory $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }
}

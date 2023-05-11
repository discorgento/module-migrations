<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Facade;

use Discorgento\Migrations\Common\EavAttribute;
use Magento\Customer\Api\CustomerMetadataInterface;

class CustomerAttribute extends EavAttribute
{
    public const ENTITY_TYPE = CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER;
}

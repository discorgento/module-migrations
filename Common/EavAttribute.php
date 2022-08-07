<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;

abstract class EavAttribute implements ScopedAttributeInterface
{
    public const ENTITY_TYPE = 'OVERRIDE THIS IS CHILD CLASSES';

    protected EavSetupFactory $eavSetupFactory;

    public function __construct(
        EavAttribute\Context $context
    ) {
        $this->eavSetupFactory = $context->eavSetupFactory;
    }

    /**
     * Create a new attribute
     *
     * @param string $code
     * @param array $data
     * @return \Magento\Eav\Setup\EavSetup
     */
    public function create($code, $data)
    {
        $this->getEavSetup()->addAttribute(
            static::ENTITY_TYPE,
            $code,
            $data
        );
    }

    /**
     * Update an existing attribute
     *
     * @param string $code
     * @param array $data
     * @return \Magento\Eav\Setup\EavSetup
     */
    public function update($code, $data)
    {
        $this->getEavSetup()->updateAttribute(
            static::ENTITY_TYPE,
            $code,
            $data
        );
    }

    /**
     * Mass update attributes of current entity
     *
     * @param array $entityIds
     * @param array $data
     */
    abstract public function massUpdate($entityIds, $data);

    /**
     * Retrieve a fresh instance of the EavSetup
     * @return \Magento\Eav\Setup\EavSetup
     */
    public function getEavSetup()
    {
        return $this->eavSetupFactory->create();
    }
}

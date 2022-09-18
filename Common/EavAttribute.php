<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;

abstract class EavAttribute implements ScopedAttributeInterface
{
    public const ENTITY_TYPE = 'OVERRIDE THIS IS CHILD CLASSES';

    protected EavSetupFactory $eavSetupFactory;
    protected ResourceConnection $resourceConnection;

    public function __construct(
        EavAttribute\Context $context
    ) {
        $this->eavSetupFactory = $context->eavSetupFactory;
        $this->resourceConnection = $context->resourceConnection;
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

    /**
     * Database facade for quick operations
     */
    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Retrieve given table name on database,
     * with preffix and etc if applicable
     *
     * @param string $rawTableName
     * @return string
     */
    public function getTableName($rawTableName)
    {
        return $this->resourceConnection->getTableName($rawTableName);
    }

    /**
     * Retrieve entity type if
     * @return int
     */
    public function getEntityTypeId()
    {
        $tableName = $this->getTableName('eav_entity_type');

        return (int) $this->getConnection()->fetchOne(<<<SQL
            SELECT entity_type_id FROM $tableName WHERE entity_type_code = :entity_type_code
        SQL, ['entity_type_code' => static::ENTITY_TYPE]);
    }
}

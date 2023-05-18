<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;

abstract class EavAttribute implements ScopedAttributeInterface
{
    public const ENTITY_TYPE = 'OVERRIDE THIS IS CHILD CLASSES';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    // phpcs:ignore
    public function __construct(
        EavAttribute\Context $context
    ) {
        $this->config = $context->config;
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
     * Check if given attribute exists
     *
     * @param string $code
     * @return bool
     */
    public function exists($code)
    {
        return (bool) $this->config->getAttribute(static::ENTITY_TYPE, $code)->getId();
    }

    /**
     * Retrieve a fresh instance of the EavSetup
     *
     * @return \Magento\Eav\Setup\EavSetup
     */
    protected function getEavSetup()
    {
        return $this->eavSetupFactory->create();
    }

    /**
     * Database facade for quick operations
     */
    protected function getConnection()
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
    protected function getTableName($rawTableName)
    {
        return $this->resourceConnection->getTableName($rawTableName);
    }

    /**
     * Retrieve entity type if
     *
     * @return int
     */
    protected function getEntityTypeId()
    {
        $tableName = $this->getTableName('eav_entity_type');
        $select = $this->getConnection()->select()
            ->from($tableName, 'entity_type_id')
            ->where('entity_type_code=?', static::ENTITY_TYPE);

        return (int) $this->getConnection()->fetchOne($select);
    }
}

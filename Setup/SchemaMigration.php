<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

abstract class SchemaMigration implements UpgradeSchemaInterface, InstallSchemaInterface
{
    private SchemaMigration\Context $context;

    public function __construct(
        SchemaMigration\Context $context
    ) {
        $this->context = $context;
    }

    abstract protected function execute();

    private function apply(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // initiate some classes here to avoid duplication on constructor
        $this->context->context = $context;
        $this->context->setup = $setup;

        $this->execute();

        $setup->endSetup();
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->apply($setup, $context);
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->apply($setup, $context);
    }

    /**
     * Get database connection
     * @return AdapterInterface
     */
    protected function getConnection()
    {
        return $this->context->setup->getConnection();
    }

    /**
     * Check if it's a upgrade to given version
     *
     * @param string $version
     * @return boolean
     */
    protected function isUpgradingTo($version)
    {
        return version_compare($this->getContext()->getVersion(), $version, '<');
    }

    /**
     * Validate and retrieve the preffixed table name
     *
     * @param string $rawName
     * @return string
     */
    protected function getTableName($rawName)
    {
        return $this->getInstaller()->getTable($rawName);
    }

    /**
     * Retrieve current module context
     * @return ModuleContextInterface
     */
    protected function getContext()
    {
        return $this->context->context;
    }

    /**
     * Retrieve current setup installer
     * @return SchemaSetupInterface
     */
    protected function getSetup()
    {
        return $this->context->setup;
    }

    /**
     * Retrieve current setup installer
     * @return SchemaSetupInterface
     */
    protected function getInstaller()
    {
        return $this->context->setup;
    }
}

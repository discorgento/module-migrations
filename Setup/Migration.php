<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

abstract class Migration implements DataPatchInterface, PatchRevertableInterface
{
    /** @var Migration\Context */
    protected $context;

    /**
     * DO NOT modify this constructor, if need add common new dependencies
     * to the Discorgento\Migrations\Setup\Migration\Context::class instead,
     * or specific dependencies to your concrete migration class.
     * (this prevents already existant custom class that extends this one from breaking)
     */
    public function __construct(Migration\Context $context)
    {
        $this->context = $context;
    }

    /**
     * Your migration logic goes here
     */
    abstract protected function execute();

    /**
     * Undo/revert the migration (optional)
     */
    protected function rollback()
    {
        // optional, override to implement an undo feature in your migration
    }

    /** @inheritDoc */
    final public function apply()
    {
        $this->getConnection()->startSetup();

        try {
            $this->context->state->setAreaCode('adminhtml');
        } catch (\Throwable $e) {
            // area code already set
        }

        $this->execute();
        $this->getConnection()->endSetup();
    }

    /** @inheritDoc */
    final public function revert()
    {
        $this->getConnection()->startSetup();
        $this->rollback();
        $this->getConnection()->endSetup();
    }

    /**
     * Shorthand for getting the database connection
     * @return AdapterInterface
     */
    final protected function getConnection()
    {
        return $this->context->moduleDataSetup->getConnection();
    }

    /**
     * Get given table name in database
     * (including prefix and etc)
     *
     * @param $rawName
     * @return string
     */
    final protected function getTableName($rawName)
    {
        return $this->context->moduleDataSetup->getTable($rawName);
    }

    /** @inheritdoc */
    public function getAliases()
    {
        return [];
    }

    /** @inheritdoc */
    public static function getDependencies()
    {
        return [];
    }
}

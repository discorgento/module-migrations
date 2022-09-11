<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

abstract class Migration implements
    DataPatchInterface,
    PatchRevertableInterface
{
    protected const AREA_CODE = null;

    private Migration\Context $context;

    public function __construct(
        Migration\Context $context
    ) {
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

        if ($areaCode = static::AREA_CODE) {
            try {
                $this->context->state->setAreaCode($areaCode);
            } catch (\Throwable $th) {
                // already set
            }
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
     */
    protected function getConnection() : AdapterInterface
    {
        return $this->context->moduleDataSetup->getConnection();
    }

    /**
     * Get given table final name in database,
     * including prefix and etc
     */
    protected function getTableName(string $rawName) : string
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

<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

abstract class Migration implements DataPatchInterface, PatchRevertableInterface
{
    protected const AREA_CODE = null;

    // phpcs:ignore
    public function __construct(
        private readonly Migration\Context $context,
    ) {
    }

    /**
     * Your migration logic goes here
     */
    abstract protected function execute();

    /**
     * [Optional] Insert your rollback logic here.
     * It will be executed when you run `bin/magento setup:db-data:rollback` command.
     *
     * @return void
     */
    // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    protected function rollback()
    {
    }

    /**
     * @inheritDoc
     *
     * ⚠ Keep this method untouched in child patches.
     * Put patch logic in execute() instead.
     */
    // phpcs:ignore Magento2.PHP.FinalImplementation
    final public function apply()
    {
        if ($appliedAlias = $this->hasAlreadyAppliedAlias()) {
            $this->context->logger->info(\sprintf(
                'Patch data "%s" skipped because it was already applied under the old name "%s"',
                static::class,
                $appliedAlias
            ));

            return;
        }

        $this->getConnection()->startSetup();

        if (static::AREA_CODE) {
            $this->context->state->setAreaCode(static::AREA_CODE);
        }

        $this->execute();

        $this->getConnection()->endSetup();
    }

    /**
     * @inheritDoc
     *
     * ⚠ Keep this method untouched in child patches.
     * Put revert logic in rollback() instead.
     */
    // phpcs:ignore Magento2.PHP.FinalImplementation
    final public function revert()
    {
        $this->getConnection()->startSetup();
        $this->rollback();
        $this->getConnection()->endSetup();
    }

    /**
     * Workaround for Magento core bug
     *
     * Return false if there's no previous applied alias,
     * otherwise return the applied alias name
     *
     * @see https://github.com/magento/magento2/issues/31396
     *
     * @return string|false
     */
    private function hasAlreadyAppliedAlias()
    {
        foreach ($this->getAliases() as $alias) {
            if ($this->context->patchHistory->isApplied($alias)) {
                return $alias;
            }
        }

        return false;
    }

    /**
     * Shorthand for getting the database connection
     */
    protected function getConnection(): AdapterInterface
    {
        return $this->getModuleDataSetup()->getConnection();
    }

    /**
     * Get given table name in database including prefix
     *
     * @param string $rawName
     * @return string
     */
    protected function getTableName(string $rawName): string
    {
        return $this->getModuleDataSetup()->getTable($rawName);
    }

    /**
     * Module Setup Data getter
     *
     * @return ModuleDataSetupInterface
     */
    protected function getModuleDataSetup()
    {
        return $this->context->moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }
}

<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Test\Integration\Setup\Migration\Facade;

use Discorgento\Migrations\Setup\Migration\Facade\AdminConfig;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AdminConfigTest extends TestCase
{
    private const PATH_PREFIX = 'discorgento_migrations_test/admin_config/';

    /** @var AdminConfig */
    private ?AdminConfig $adminConfig = null;

    /** @var ReinitableConfigInterface */
    private ?ReinitableConfigInterface $reinitableConfig = null;

    /** @var AdapterInterface */
    private ?AdapterInterface $connection = null;

    /** @var string */
    private ?string $coreConfigDataTable = null;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->adminConfig = $objectManager->get(AdminConfig::class);
        $this->reinitableConfig = $objectManager->get(ReinitableConfigInterface::class);

        $resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection();
        $this->coreConfigDataTable = $resourceConnection->getTableName('core_config_data');

        $this->deleteTestRows();
        $this->reinitConfig();
    }

    protected function tearDown(): void
    {
        $this->deleteTestRows();
        $this->reinitConfig();
    }

    public function testSetAndGetSinglePath(): void
    {
        $path = $this->path('set-and-get');

        $this->adminConfig->set($path, 'alpha');
        $this->reinitConfig();

        self::assertSame('alpha', $this->adminConfig->get($path));
        self::assertSame('alpha', $this->getStoredValue($path));
    }

    public function testSetMapWithScopeOverrides(): void
    {
        $defaultPath = $this->path('map-default');
        $storePath = $this->path('map-store');

        $this->adminConfig->set([
            $defaultPath => 'default-value',
            $storePath => [
                'value' => 'store-value',
                'scope' => 'stores',
                'scope_id' => 9,
            ],
        ]);

        self::assertSame('default-value', $this->getStoredValue($defaultPath));
        self::assertSame('store-value', $this->getStoredValue($storePath, 'stores', 9));
    }

    public function testRestoreDeletesSinglePath(): void
    {
        $path = $this->path('restore');
        $this->adminConfig->set($path, 'to-delete');

        self::assertSame('to-delete', $this->getStoredValue($path));

        $this->adminConfig->restore($path);
        $this->reinitConfig();

        self::assertNull($this->getStoredValue($path));
    }

    public function testResetDeletesConfiguredPath(): void
    {
        $path = $this->path('reset');
        $this->adminConfig->set($path, 'legacy');

        self::assertSame('legacy', $this->getStoredValue($path));

        $this->adminConfig->reset($path);
        $this->reinitConfig();

        self::assertNull($this->getStoredValue($path));
    }

    public function testAppendConcatenatesCurrentValue(): void
    {
        $path = $this->path('append');
        $this->adminConfig->set($path, 'head');
        $this->reinitConfig();

        $this->adminConfig->append($path, '-tail');

        self::assertSame('head-tail', $this->getStoredValue($path));
    }

    public function testAppendOptionAddsUniqueOptions(): void
    {
        $path = $this->path('append-option');
        $this->adminConfig->set($path, 'one,two');
        $this->reinitConfig();

        $this->adminConfig->appendOption($path, ['two', 'three']);

        self::assertSame('one,two,three', $this->getStoredValue($path));
    }

    public function testAppendOptionSupportsCustomSeparator(): void
    {
        $path = $this->path('append-option-separator');
        $this->adminConfig->set($path, 'a|b');
        $this->reinitConfig();

        $this->adminConfig->appendOption($path, 'c', '|');

        self::assertSame('a|b|c', $this->getStoredValue($path));
    }

    public function testRestoreSupportsMultiplePaths(): void
    {
        $firstPath = $this->path('restore-multiple-1');
        $secondPath = $this->path('restore-multiple-2');

        $this->adminConfig->set($firstPath, 'first');
        $this->adminConfig->set($secondPath, 'second');

        $this->adminConfig->restore([$firstPath, $secondPath]);

        self::assertNull($this->getStoredValue($firstPath));
        self::assertNull($this->getStoredValue($secondPath));
    }

    private function getStoredValue(
        string $path,
        string $scope = ScopeConfig::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): ?string {
        $select = $this->connection->select()
            ->from($this->coreConfigDataTable, ['value'])
            ->where('path = ?', $path)
            ->where('scope = ?', $scope)
            ->where('scope_id = ?', $scopeId)
            ->order('config_id DESC')
            ->limit(1);

        $value = $this->connection->fetchOne($select);
        if ($value === false) {
            return null;
        }

        return (string) $value;
    }

    private function deleteTestRows(): void
    {
        $this->connection->delete(
            $this->coreConfigDataTable,
            ['path LIKE ?' => self::PATH_PREFIX . '%']
        );
    }

    private function reinitConfig(): void
    {
        $this->reinitableConfig->reinit();
    }

    private function path(string $suffix): string
    {
        return self::PATH_PREFIX . $suffix;
    }
}

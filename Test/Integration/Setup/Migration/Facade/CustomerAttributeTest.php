<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Test\Integration\Setup\Migration\Facade;

use Discorgento\Migrations\Setup\Migration\Facade\CustomerAttribute;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CustomerAttributeTest extends TestCase
{
    /** @var CustomerAttribute */
    private ?CustomerAttribute $customerAttribute = null;

    /** @var Config */
    private ?Config $eavConfig = null;

    /** @var EavSetupFactory */
    private ?EavSetupFactory $eavSetupFactory = null;

    /** @var AdapterInterface */
    private ?AdapterInterface $connection = null;

    /** @var string */
    private ?string $eavAttributeTable = null;

    /** @var array */
    private ?array $createdCodes = [];

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->customerAttribute = $objectManager->get(CustomerAttribute::class);
        $this->eavConfig = $objectManager->get(Config::class);
        $this->eavSetupFactory = $objectManager->get(EavSetupFactory::class);

        $resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection();
        $this->eavAttributeTable = $resourceConnection->getTableName('eav_attribute');
    }

    protected function tearDown(): void
    {
        $this->removeCreatedAttributes();
    }

    public function testCreateExistsAndUpdate(): void
    {
        $code = $this->attributeCode('base');

        $this->customerAttribute->create($code, [
            'type' => 'varchar',
            'label' => 'Discorgento Customer Attribute',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'system' => 0,
            'position' => 999,
        ]);

        self::assertTrue($this->customerAttribute->exists($code));

        $this->customerAttribute->update($code, ['is_required' => 1]);

        $attribute = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $code);
        $attributeId = (int) $attribute->getId();

        $select = $this->connection->select()
            ->from($this->eavAttributeTable, ['is_required'])
            ->where('attribute_id = ?', $attributeId)
            ->limit(1);

        $isRequired = $this->connection->fetchOne($select);
        self::assertSame('1', (string) $isRequired);
    }

    public function testCreateIfNotExistsDoesNotOverwriteExistingAttribute(): void
    {
        $code = $this->attributeCode('create_if_not_exists');

        $this->customerAttribute->createIfNotExists($code, [
            'type' => 'varchar',
            'label' => 'Initial Customer Label',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'system' => 0,
            'position' => 999,
        ]);

        $initialAttribute = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $code);
        $initialAttributeId = (int) $initialAttribute->getId();
        self::assertGreaterThan(0, $initialAttributeId);

        $this->customerAttribute->createIfNotExists($code, [
            'type' => 'varchar',
            'label' => 'Overwritten Customer Label',
            'input' => 'text',
            'required' => true,
            'visible' => true,
            'user_defined' => true,
            'system' => 0,
            'position' => 999,
        ]);

        $attribute = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $code);
        $attributeId = (int) $attribute->getId();
        self::assertSame($initialAttributeId, $attributeId);

        $select = $this->connection->select()
            ->from($this->eavAttributeTable, ['cnt' => new \Zend_Db_Expr('COUNT(*)')])
            ->where('attribute_code = ?', $code)
            ->limit(1);

        $count = $this->connection->fetchOne($select);
        self::assertSame('1', (string) $count);
    }

    private function removeCreatedAttributes(): void
    {
        $eavSetup = $this->eavSetupFactory->create();

        foreach ($this->createdCodes ?? [] as $code) {
            if (!$this->customerAttribute->exists($code)) {
                continue;
            }

            $eavSetup->removeAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $code);
        }
    }

    private function attributeCode(string $suffix): string
    {
        $code = 'dsg_customer_' . $suffix . '_' . (string) \random_int(1000, 9999);
        $this->createdCodes[] = $code;

        return $code;
    }
}

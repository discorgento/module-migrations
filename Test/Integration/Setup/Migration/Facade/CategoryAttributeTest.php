<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Test\Integration\Setup\Migration\Facade;

use Discorgento\Migrations\Setup\Migration\Facade\CategoryAttribute;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CategoryAttributeTest extends TestCase
{
    /** @var CategoryAttribute */
    private ?CategoryAttribute $categoryAttribute = null;

    /** @var Config */
    private ?Config $eavConfig = null;

    /** @var EavSetupFactory */
    private ?EavSetupFactory $eavSetupFactory = null;

    /** @var AdapterInterface */
    private ?AdapterInterface $connection = null;

    /** @var string */
    private ?string $eavAttributeTable = null;

    /** @var string */
    private ?string $categoryVarcharTable = null;

    /** @var array */
    private ?array $createdCodes = [];

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->categoryAttribute = $objectManager->get(CategoryAttribute::class);
        $this->eavConfig = $objectManager->get(Config::class);
        $this->eavSetupFactory = $objectManager->get(EavSetupFactory::class);

        $resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection();
        $this->eavAttributeTable = $resourceConnection->getTableName('eav_attribute');
        $this->categoryVarcharTable = $resourceConnection->getTableName('catalog_category_entity_varchar');
    }

    protected function tearDown(): void
    {
        $this->removeCreatedAttributes();
    }

    public function testCreateExistsAndUpdate(): void
    {
        $code = $this->attributeCode('base');

        $this->categoryAttribute->create($code, [
            'type' => 'varchar',
            'label' => 'Discorgento Category Attribute',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'global' => ScopedAttributeInterface::SCOPE_STORE,
            'group' => 'General Information',
        ]);

        self::assertTrue($this->categoryAttribute->exists($code));

        $this->categoryAttribute->update($code, ['is_required' => 1]);

        $attributeId = $this->getAttributeId($code);
        self::assertNotNull($attributeId);

        $select = $this->connection->select()
            ->from($this->eavAttributeTable, ['is_required'])
            ->where('attribute_id = ?', $attributeId)
            ->limit(1);

        $isRequired = $this->connection->fetchOne($select);
        self::assertSame('1', (string) $isRequired);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     */
    public function testMassUpdateRunsForExistingCategories(): void
    {
        $this->categoryAttribute->massUpdate([333], ['meta_title' => 'Mass Updated Value']);

        $select = $this->connection->select()
            ->from('catalog_category_entity', ['entity_id'])
            ->where('entity_id = ?', 333)
            ->limit(1);

        self::assertSame('333', (string) $this->connection->fetchOne($select));
    }

    private function getAttributeId(string $code): ?int
    {
        $select = $this->connection->select()
            ->from($this->eavAttributeTable, ['attribute_id'])
            ->where('attribute_code = ?', $code)
            ->limit(1);

        $attributeId = $this->connection->fetchOne($select);
        if ($attributeId === false) {
            return null;
        }

        return (int) $attributeId;
    }

    private function removeCreatedAttributes(): void
    {
        $eavSetup = $this->eavSetupFactory->create();

        foreach ($this->createdCodes ?? [] as $code) {
            if (!$this->categoryAttribute->exists($code)) {
                continue;
            }

            $eavSetup->removeAttribute(CategoryAttributeInterface::ENTITY_TYPE_CODE, $code);
        }
    }

    private function attributeCode(string $suffix): string
    {
        $code = 'dsg_category_' . $suffix . '_' . (string) \random_int(1000, 9999);
        $this->createdCodes[] = $code;

        return $code;
    }
}

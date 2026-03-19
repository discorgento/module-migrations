<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Test\Integration\Setup\Migration\Facade;

use Discorgento\Migrations\Setup\Migration\Facade\ProductAttribute;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
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
class ProductAttributeTest extends TestCase
{
    /** @var ProductAttribute */
    private ?ProductAttribute $productAttribute = null;

    /** @var Config */
    private ?Config $eavConfig = null;

    /** @var EavSetupFactory */
    private ?EavSetupFactory $eavSetupFactory = null;

    /** @var ProductRepositoryInterface */
    private ?ProductRepositoryInterface $productRepository = null;

    /** @var AdapterInterface */
    private ?AdapterInterface $connection = null;

    /** @var string */
    private ?string $eavAttributeTable = null;

    /** @var string */
    private ?string $eavEntityAttributeTable = null;

    /** @var int */
    private ?int $entityTypeId = null;

    /** @var int */
    private ?int $defaultAttributeSetId = null;

    /** @var array */
    private ?array $createdCodes = [];

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->productAttribute = $objectManager->get(ProductAttribute::class);
        $this->eavConfig = $objectManager->get(Config::class);
        $this->eavSetupFactory = $objectManager->get(EavSetupFactory::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);

        $resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->connection = $resourceConnection->getConnection();
        $this->eavAttributeTable = $resourceConnection->getTableName('eav_attribute');
        $this->eavEntityAttributeTable = $resourceConnection->getTableName('eav_entity_attribute');

        $eavSetup = $this->eavSetupFactory->create();
        $this->entityTypeId = (int) $eavSetup->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $this->defaultAttributeSetId = (int) $eavSetup->getDefaultAttributeSetId(
            ProductAttributeInterface::ENTITY_TYPE_CODE
        );
    }

    protected function tearDown(): void
    {
        $this->removeCreatedAttributes();
    }

    public function testCreateExistsAndUpdate(): void
    {
        $code = $this->attributeCode('base');

        $this->productAttribute->create($code, [
            'type' => 'int',
            'label' => 'Discorgento Product Attribute',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => 'General',
        ]);

        self::assertTrue($this->productAttribute->exists($code));

        $this->productAttribute->update($code, ['is_required' => 1]);

        $attributeId = $this->getAttributeId($code);
        self::assertNotNull($attributeId);

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

        $this->productAttribute->createIfNotExists($code, [
            'type' => 'varchar',
            'label' => 'Initial Product Label',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => 'General',
        ]);

        $initialAttributeId = $this->getAttributeId($code);
        self::assertNotNull($initialAttributeId);

        $this->productAttribute->createIfNotExists($code, [
            'type' => 'varchar',
            'label' => 'Overwritten Product Label',
            'input' => 'text',
            'required' => true,
            'visible' => true,
            'user_defined' => true,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => 'General',
        ]);

        $attributeId = $this->getAttributeId($code);
        self::assertNotNull($attributeId);
        self::assertSame($initialAttributeId, $attributeId);

        $select = $this->connection->select()
            ->from($this->eavAttributeTable, ['cnt' => new \Zend_Db_Expr('COUNT(*)')])
            ->where('entity_type_id = ?', $this->entityTypeId)
            ->where('attribute_code = ?', $code)
            ->limit(1);

        $count = $this->connection->fetchOne($select);
        self::assertSame('1', (string) $count);
    }

    public function testCreateDropdownCreatesSelectableAttribute(): void
    {
        $code = $this->attributeCode('dropdown');

        $this->productAttribute->createDropdown(
            $code,
            'Discorgento Dropdown Attribute',
            ['Option A', 'Option B'],
            ['global' => ScopedAttributeInterface::SCOPE_GLOBAL]
        );

        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $code);

        self::assertTrue($this->productAttribute->exists($code));
        self::assertSame('select', $attribute->getFrontendInput());
    }

    public function testCreateDropdownIfNotExistsDoesNotOverwriteExistingAttribute(): void
    {
        $code = $this->attributeCode('dropdown_if_not_exists');

        $this->productAttribute->createDropdownIfNotExists(
            $code,
            'Initial Dropdown Label',
            ['Initial Option'],
            ['global' => ScopedAttributeInterface::SCOPE_GLOBAL]
        );

        $initialAttributeId = $this->getAttributeId($code);
        self::assertNotNull($initialAttributeId);

        $this->productAttribute->createDropdownIfNotExists(
            $code,
            'Overwritten Dropdown Label',
            ['Different Option'],
            ['global' => ScopedAttributeInterface::SCOPE_GLOBAL]
        );

        $attributeId = $this->getAttributeId($code);
        self::assertNotNull($attributeId);
        self::assertSame($initialAttributeId, $attributeId);

        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $code);
        self::assertSame('Initial Dropdown Label', (string) $attribute->getFrontendLabel());
        self::assertSame('select', $attribute->getFrontendInput());
    }

    public function testAssignToAttributeSetAndUnassign(): void
    {
        $code = $this->attributeCode('assign');

        $this->productAttribute->create($code, [
            'type' => 'varchar',
            'label' => 'Discorgento Assign Attribute',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
        ]);

        $this->productAttribute->assignToAttributeSet($code, $this->defaultAttributeSetId);
        self::assertTrue($this->isAttributeAssignedToDefaultSet($code));

        $this->productAttribute->unassignFromAttributeSet($code, $this->defaultAttributeSetId);
        self::assertFalse($this->isAttributeAssignedToDefaultSet($code));
    }

    public function testAssignToAttributeSetSupportsLegacyOptions(): void
    {
        $code = $this->attributeCode('legacy');

        $this->productAttribute->create($code, [
            'type' => 'varchar',
            'label' => 'Discorgento Legacy Assign Attribute',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
        ]);

        $this->productAttribute->assignToAttributeSet($code, ['attribute_set_id' => $this->defaultAttributeSetId]);

        self::assertTrue($this->isAttributeAssignedToDefaultSet($code));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testMassUpdateUpdatesExistingProductAttributeValue(): void
    {
        $product = $this->productRepository->get('simple', false, null, true);
        $productId = (int) $product->getId();

        $this->productAttribute->massUpdate([$productId], ['meta_title' => 'discorgento updated meta title']);

        $reloaded = $this->productRepository->get('simple', false, null, true);
        $metaTitle = $reloaded->getCustomAttribute('meta_title');

        self::assertNotNull($metaTitle);
        self::assertSame('discorgento updated meta title', (string) $metaTitle->getValue());
    }

    private function isAttributeAssignedToDefaultSet(string $attributeCode): bool
    {
        $attributeId = $this->getAttributeId($attributeCode);
        if ($attributeId === null) {
            return false;
        }

        $select = $this->connection->select()
            ->from($this->eavEntityAttributeTable, ['attribute_id'])
            ->where('entity_type_id = ?', $this->entityTypeId)
            ->where('attribute_set_id = ?', $this->defaultAttributeSetId)
            ->where('attribute_id = ?', $attributeId)
            ->limit(1);

        return $this->connection->fetchOne($select) !== false;
    }

    private function getAttributeId(string $attributeCode): ?int
    {
        $select = $this->connection->select()
            ->from($this->eavAttributeTable, ['attribute_id'])
            ->where('entity_type_id = ?', $this->entityTypeId)
            ->where('attribute_code = ?', $attributeCode)
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
            if (!$this->productAttribute->exists($code)) {
                continue;
            }

            $eavSetup->removeAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $code);
        }
    }

    private function attributeCode(string $suffix): string
    {
        $code = 'dsg_product_' . $suffix . '_' . (string) \random_int(1000, 9999);
        $this->createdCodes[] = $code;

        return $code;
    }
}

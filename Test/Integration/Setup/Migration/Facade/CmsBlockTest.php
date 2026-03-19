<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Test\Integration\Setup\Migration\Facade;

use Discorgento\Migrations\Setup\Migration\Facade\CmsBlock;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CmsBlockTest extends TestCase
{
    private const IDENTIFIER_PREFIX = 'discorgento_migrations_test_cms_block_';

    /** @var CmsBlock */
    private ?CmsBlock $cmsBlock = null;

    /** @var BlockCollectionFactory */
    private ?BlockCollectionFactory $collectionFactory = null;

    /** @var BlockRepository */
    private ?BlockRepository $repository = null;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->cmsBlock = $objectManager->get(CmsBlock::class);
        $this->collectionFactory = $objectManager->get(BlockCollectionFactory::class);
        $this->repository = $objectManager->get(BlockRepository::class);

        $this->deleteTestBlocks();
    }

    protected function tearDown(): void
    {
        $this->deleteTestBlocks();
    }

    public function testCreateAndGet(): void
    {
        $identifier = $this->identifier('create-and-get');

        $createdBlock = $this->cmsBlock->create($identifier, $this->blockData('Initial title'), 0);
        $loadedBlock = $this->cmsBlock->get($identifier);

        self::assertSame((int) $createdBlock->getId(), (int) $loadedBlock->getId());
        self::assertSame($identifier, $loadedBlock->getIdentifier());
        self::assertSame('Initial title', $loadedBlock->getTitle());
    }

    public function testSafeCreateReturnsExistingBlock(): void
    {
        $identifier = $this->identifier('safe-create');

        $first = $this->cmsBlock->create($identifier, $this->blockData('Original title'), 0);
        $second = $this->cmsBlock->safeCreate($identifier, $this->blockData('Should not overwrite'), 0);

        self::assertSame((int) $first->getId(), (int) $second->getId());
        self::assertSame('Original title', $second->getTitle());
    }

    public function testUpdateChangesBlockData(): void
    {
        $identifier = $this->identifier('update');

        $this->cmsBlock->create($identifier, $this->blockData('Before update'), 0);
        $this->cmsBlock->update($identifier, [
            'title' => 'After update',
            'content' => 'Updated content',
        ]);

        $updated = $this->cmsBlock->get($identifier);

        self::assertSame('After update', $updated->getTitle());
        self::assertSame('Updated content', $updated->getContent());
    }

    public function testSafeUpdateReturnsNullWhenMissing(): void
    {
        $identifier = $this->identifier('safe-update-missing');

        $result = $this->cmsBlock->safeUpdate($identifier, ['title' => 'No-op']);

        self::assertNull($result);
    }

    public function testSafeUpdateChangesDataWhenBlockExists(): void
    {
        $identifier = $this->identifier('safe-update-existing');

        $this->cmsBlock->create($identifier, $this->blockData('Before safe update'), 0);
        $result = $this->cmsBlock->safeUpdate($identifier, ['title' => 'After safe update']);

        self::assertInstanceOf(BlockInterface::class, $result);
        self::assertSame('After safe update', $this->cmsBlock->get($identifier)->getTitle());
    }

    public function testCreateOrUpdateCreatesWhenMissing(): void
    {
        $identifier = $this->identifier('create-or-update-create');

        $block = $this->cmsBlock->createOrUpdate($identifier, $this->blockData('Created by createOrUpdate'), 0);

        self::assertSame($identifier, $block->getIdentifier());
        self::assertTrue($this->cmsBlock->exists($identifier));
    }

    public function testCreateOrUpdateUpdatesWhenExisting(): void
    {
        $identifier = $this->identifier('create-or-update-update');

        $created = $this->cmsBlock->create($identifier, $this->blockData('Before createOrUpdate'), 0);
        $updated = $this->cmsBlock->createOrUpdate($identifier, ['title' => 'After createOrUpdate']);

        self::assertSame((int) $created->getId(), (int) $updated->getId());
        self::assertSame('After createOrUpdate', $updated->getTitle());
    }

    public function testDeleteRemovesBlock(): void
    {
        $identifier = $this->identifier('delete');

        $this->cmsBlock->create($identifier, $this->blockData('To delete'), 0);

        self::assertTrue($this->cmsBlock->exists($identifier));
        self::assertTrue($this->cmsBlock->delete($identifier));
        self::assertFalse($this->cmsBlock->exists($identifier));
    }

    public function testExistsReturnsFalseForMissingIdentifier(): void
    {
        $identifier = $this->identifier('exists-missing');

        self::assertFalse($this->cmsBlock->exists($identifier));
    }

    private function blockData(string $title): array
    {
        return [
            'title' => $title,
            'content' => 'Test content',
            'is_active' => 1,
        ];
    }

    private function deleteTestBlocks(): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('identifier', ['like' => self::IDENTIFIER_PREFIX . '%']);

        foreach ($collection as $block) {
            $this->repository->deleteById((int) $block->getId());
        }
    }

    private function identifier(string $suffix): string
    {
        return self::IDENTIFIER_PREFIX . $suffix;
    }
}

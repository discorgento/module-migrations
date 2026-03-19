<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Test\Integration\Setup\Migration\Facade;

use Discorgento\Migrations\Setup\Migration\Facade\CmsPage;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class CmsPageTest extends TestCase
{
    private const IDENTIFIER_PREFIX = 'discorgento_migrations_test_cms_page_';

    /** @var CmsPage */
    private ?CmsPage $cmsPage = null;

    /** @var PageCollectionFactory */
    private ?PageCollectionFactory $collectionFactory = null;

    /** @var PageRepository */
    private ?PageRepository $repository = null;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        $this->cmsPage = $objectManager->get(CmsPage::class);
        $this->collectionFactory = $objectManager->get(PageCollectionFactory::class);
        $this->repository = $objectManager->get(PageRepository::class);

        $this->deleteTestPages();
    }

    protected function tearDown(): void
    {
        $this->deleteTestPages();
    }

    public function testCreateAndGet(): void
    {
        $identifier = $this->identifier('create-and-get');

        $createdPage = $this->cmsPage->create($identifier, $this->pageData('Initial title'), 0);
        $loadedPage = $this->cmsPage->get($identifier);

        self::assertSame((int) $createdPage->getId(), (int) $loadedPage->getId());
        self::assertSame($identifier, $loadedPage->getIdentifier());
        self::assertSame('Initial title', $loadedPage->getTitle());
    }

    public function testCreateIfNotExistsReturnsExistingPage(): void
    {
        $identifier = $this->identifier('safe-create');

        $first = $this->cmsPage->create($identifier, $this->pageData('Original title'), 0);
        $second = $this->cmsPage->createIfNotExists($identifier, $this->pageData('Should not overwrite'), 0);

        self::assertSame((int) $first->getId(), (int) $second->getId());
        self::assertSame('Original title', $second->getTitle());
    }

    public function testSafeCreateAliasStillWorks(): void
    {
        $identifier = $this->identifier('safe-create-alias');

        $first = $this->cmsPage->create($identifier, $this->pageData('Alias title'), 0);
        $second = $this->cmsPage->safeCreate($identifier, $this->pageData('Should not overwrite alias'), 0);

        self::assertSame((int) $first->getId(), (int) $second->getId());
        self::assertSame('Alias title', $second->getTitle());
    }

    public function testUpdateChangesPageData(): void
    {
        $identifier = $this->identifier('update');

        $this->cmsPage->create($identifier, $this->pageData('Before update'), 0);
        $this->cmsPage->update($identifier, [
            'title' => 'After update',
            'content' => 'Updated content',
        ]);

        $updated = $this->cmsPage->get($identifier);

        self::assertSame('After update', $updated->getTitle());
        self::assertSame('Updated content', $updated->getContent());
    }

    public function testCreateOrUpdateCreatesWhenMissing(): void
    {
        $identifier = $this->identifier('create-or-update-create');

        $page = $this->cmsPage->createOrUpdate($identifier, $this->pageData('Created by createOrUpdate'), 0);

        self::assertSame($identifier, $page->getIdentifier());
        self::assertTrue($this->cmsPage->exists($identifier));
    }

    public function testCreateOrUpdateUpdatesWhenExisting(): void
    {
        $identifier = $this->identifier('create-or-update-update');

        $created = $this->cmsPage->create($identifier, $this->pageData('Before createOrUpdate'), 0);
        $updated = $this->cmsPage->createOrUpdate($identifier, ['title' => 'After createOrUpdate']);

        self::assertSame((int) $created->getId(), (int) $updated->getId());
        self::assertSame('After createOrUpdate', $updated->getTitle());
    }

    public function testDeleteRemovesPage(): void
    {
        $identifier = $this->identifier('delete');

        $this->cmsPage->create($identifier, $this->pageData('To delete'), 0);

        self::assertTrue($this->cmsPage->exists($identifier));
        self::assertTrue($this->cmsPage->delete($identifier));
        self::assertFalse($this->cmsPage->exists($identifier));
    }

    public function testExistsReturnsFalseForMissingIdentifier(): void
    {
        $identifier = $this->identifier('exists-missing');

        self::assertFalse($this->cmsPage->exists($identifier));
    }

    private function pageData(string $title): array
    {
        return [
            'title' => $title,
            'content' => 'Test content',
            'is_active' => 1,
            'page_layout' => '1column',
        ];
    }

    private function deleteTestPages(): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('identifier', ['like' => self::IDENTIFIER_PREFIX . '%']);

        foreach ($collection as $page) {
            $this->repository->deleteById((int) $page->getId());
        }
    }

    private function identifier(string $suffix): string
    {
        return self::IDENTIFIER_PREFIX . $suffix;
    }
}

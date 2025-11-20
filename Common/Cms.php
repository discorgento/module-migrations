<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common;

use Magento\Cms\Api\Data\BlockInterface as Block;
use Magento\Cms\Api\Data\PageInterface as Page;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Exception\LocalizedException;

abstract class Cms
{
    /** @var BlockCollectionFactory|PageCollectionFactory */
    protected $collectionFactory;

    /** @var BlockFactory|PageFactory */
    protected $factory;

    /** @var BlockRepository|PageRepository */
    protected $repository;

    // phpcs:ignore
    public function __construct(
        $collectionFactory,
        $factory,
        $repository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * Load cms content data by given identifier
     *
     * @param string $identifier
     * @param int $storeId
     * @return Block|Page
     */
    public function get(string $identifier, $storeId = null)
    {
        $id = $this->findId($identifier, $storeId);

        return $this->repository->getById($id);
    }

    /**
     * Create a new content on database through corresponding cms repository
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return Block|Page
     */
    public function create($identifier, $data, $storeId = null)
    {
        $model = $this->factory->create()
            ->setIdentifier($identifier)
            ->addData($data)
            ->setStoreId($storeId);

        return $this->repository->save($model);
    }

    /**
     * Create a new content only if it not exists yet
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return Block|Page
     */
    public function safeCreate($identifier, $data, $storeId = null)
    {
        if ($this->exists($identifier, $storeId)) {
            return $this->get($identifier, $storeId);
        }

        return $this->create($identifier, $data, $storeId);
    }

    /**
     * Update cms content based on given identifier
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return Block|Page
     */
    public function update($identifier, $data, $storeId = null)
    {
        $model = $this->get($identifier, $storeId);
        $model->addData($data);

        return $this->repository->save($model);
    }

    /**
     * Update cms content only if it already exists
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return Block|Page|null
     */
    public function safeUpdate($identifier, $data, $storeId = null)
    {
        if (!$this->exists($identifier, $storeId)) {
            return null;
        }

        return $this->update($identifier, $data, $storeId);
    }

    /**
     * Create content with given identifier, or update it if already exists
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return Block|Page
     */
    public function createOrUpdate($identifier, $data, $storeId = null)
    {
        if ($this->exists($identifier, $storeId)) {
            return $this->update($identifier, $data, $storeId);
        }

        return $this->create($identifier, $data, $storeId);
    }

    /**
     * Delete cms content by given id
     * (row_id on database)
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return bool
     */
    public function delete($identifier, $storeId = null)
    {
        $id = $this->findId($identifier, $storeId);

        return $this->repository->deleteById($id);
    }

    /**
     * Check if given block exists
     *
     * @param string $identifier
     * @param int|null $storeId
     * @return bool
     */
    public function exists($identifier, $storeId = null)
    {
        try {
            $this->findId($identifier, $storeId);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    /**
     * Find a cms model by given identifier
     * (and optionally storeId)
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    protected function findId($identifier, $storeId = null)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('identifier', $identifier);

        if ($storeId) {
            $collection->addStoreFilter($storeId);
        }

        if (!$collection->count()) {
            throw new LocalizedException(
                __("CMS content with identifier '$identifier' not found")
            );
        }

        return $collection->getFirstItem()->getId();
    }
}

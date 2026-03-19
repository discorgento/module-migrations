<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Common;

use Magento\Framework\Exception\LocalizedException;

abstract class Cms
{
    /** @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory|\Magento\Cms\Model\ResourceModel\Page\CollectionFactory */
    protected $collectionFactory;

    /** @var \Magento\Cms\Model\BlockFactory|\Magento\Cms\Model\PageFactory */
    protected $factory;

    /** @var \Magento\Cms\Model\BlockRepository|\Magento\Cms\Model\PageRepository */
    protected $repository;

    /** @var \Magento\Cms\Api\GetBlockByIdentifierInterface|\Magento\Cms\Api\GetPageByIdentifierInterface */
    protected $getByIdentifier;

    // phpcs:ignore
    public function __construct(
        $collectionFactory,
        $factory,
        $repository,
        $getByIdentifier
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->getByIdentifier = $getByIdentifier;
    }

    /**
     * Load cms content data by given identifier
     *
     * @param string $identifier
     * @param int $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface
     */
    public function get(string $identifier, $storeId = null)
    {
        return $this->getByIdentifier->execute(
            $identifier,
            (int) ($storeId ?? 0)
        );
    }

    /**
     * Create a new content on database through corresponding cms repository
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     *   When a CMS content with the same identifier already exists.
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface
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
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface
     */
    public function createIfNotExists($identifier, $data, $storeId = null)
    {
        if ($this->exists($identifier, $storeId)) {
            return $this->get($identifier, $storeId);
        }

        return $this->create($identifier, $data, $storeId);
    }

    /**
     * Deprecated alias of createIfNotExists for backward compatibility.
     *
     * @deprecated 2.9.0 safe prefix is ambiguous; use createIfNotExists for explicit behavior.
     * @see \Discorgento\Migrations\Common\Cms::createIfNotExists
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface
     */
    public function safeCreate($identifier, $data, $storeId = null)
    {
        return $this->createIfNotExists($identifier, $data, $storeId);
    }

    /**
     * Update cms content based on given identifier
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface
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
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface|null
     */
    public function updateIfExists($identifier, $data, $storeId = null)
    {
        if (!$this->exists($identifier, $storeId)) {
            return null;
        }

        return $this->update($identifier, $data, $storeId);
    }

    /**
     * Deprecated alias of updateIfExists for backward compatibility.
     *
     * @deprecated 2.9.0 safe prefix is ambiguous; use updateIfExists for explicit behavior.
     * @see \Discorgento\Migrations\Common\Cms::updateIfExists
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface|null
     */
    public function safeUpdate($identifier, $data, $storeId = null)
    {
        return $this->updateIfExists($identifier, $data, $storeId);
    }

    /**
     * Create content with given identifier, or update it if already exists
     *
     * @param string $identifier
     * @param array $data
     * @param int|null $storeId
     * @return \Magento\Cms\Api\Data\BlockInterface|\Magento\Cms\Api\Data\PageInterface
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

<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Plugin;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;

class CategoryAttribute extends Common\EavAttribute
{
    public const ENTITY_TYPE = CategoryAttributeInterface::ENTITY_TYPE_CODE;

    protected ReposAndCollections $reposAndCollections;

    public function __construct(
        Common\EavAttribute\Context $context,
        ReposAndCollections $reposAndCollections
    ) {
        parent::__construct($context);
        $this->reposAndCollections = $reposAndCollections;
    }

    /**
     * @inheritDoc
     * @todo Find a faster way of doing this, like with the products
     */
    public function massUpdate($entityIds, $data)
    {
        $categoryRepository = $this->reposAndCollections->getCategoryRepository();
        foreach ($entityIds as $categoryId) {
            $category = $categoryRepository->get($categoryId);
            $category->addData($data);
            $categoryRepository->save($category);
        }
    }
}

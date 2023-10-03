<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Facade;

use Discorgento\Migrations\Common\EavAttribute;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;

class CategoryAttribute extends EavAttribute
{
    public const ENTITY_TYPE = CategoryAttributeInterface::ENTITY_TYPE_CODE;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    public function __construct(
        EavAttribute\Context $context,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Update given attribute value for multiple categories at once
     *
     * @todo Find a faster way of doing this, like with the products
     *
     * @param array $entityIds
     * @param array $data
     * @return void
     */
    public function massUpdate($entityIds, $data)
    {
        foreach ($entityIds as $categoryId) {
            /** @var \Magento\Catalog\Model\Category */
            $category = $this->categoryRepository->get($categoryId);
            $category->addData($data);

            $this->categoryRepository->save($category);
        }
    }
}

<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Facade;

use Discorgento\Migrations\Common\EavAttribute;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\AttributeSetRepository;

class ProductAttribute extends EavAttribute
{
    public const ENTITY_TYPE = ProductAttributeInterface::ENTITY_TYPE_CODE;

    protected ProductAction $productAction;
    protected AttributeManagement $attributeManagement;
    protected AttributeSetRepository $attributeSetRepository;

    public function __construct(
        EavAttribute\Context $context,
        ProductAction $productAction,
        AttributeManagement $attributeManagement,
        AttributeSetRepository $attributeSetRepository
    ) {
        parent::__construct($context);
        $this->productAction = $productAction;
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * Shorthand to quickly create a dropdown-type attribute
     *
     * @param string $code
     * @param string $label
     * @param array $values
     * @param array $config
     */
    public function createDropdown($code, $label, $values, $config = [])
    {
        $this->create(
            $code,
            array_merge([
                'label' => $label,
                'input' => 'select',
                'type' => 'int',
                'source_model' => 'Magento\\Eav\\Model\\Entity\\Attribute\\Source\\Table',
                'filterable' => 1,
                'required' => false,
                'option' => ['values' => $values],
                'user_defined' => true,
            ], $config)
        );
    }

    /**
     * Assign an attribute to some attribute set (or default if none specified)
     *
     * @param string $attributeCode
     * @param array $options
     */
    public function assignToAttributeSet($attributeCode, $options = [])
    {
        $attributeSetId = $options['attribute_set_id'] ??
            $this->getEavSetup()->getDefaultAttributeSetId(self::ENTITY_TYPE);

        /** @var \Magento\Eav\Model\Entity\Attribute\Set */
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);
        $attributeGroupId = $options['group_id'] ?? $attributeSet->getDefaultGroupId();
        $sortOrder = $options['sort_order'] ?? 999;

        $this->attributeManagement->assign(
            self::ENTITY_TYPE,
            $attributeSetId,
            $attributeGroupId,
            $attributeCode,
            $sortOrder
        );
    }

    /**
     * Remove given attribute from given attribute set
     *
     * @param string $attributeCode
     * @param int $attributeSetId
     */
    public function unassignFromAttributeSet($attributeCode, $attributeSetId = null)
    {
        $attributeSetId = $attributeSetId ?: $this->getEavSetup()
            ->getDefaultAttributeSetId(self::ENTITY_TYPE);

        $this->attributeManagement->unassign($attributeSetId, $attributeCode);
    }

    public function massUpdate($entityIds, $data)
    {
        $this->productAction->updateAttributes(
            $entityIds,
            $data,
            self::SCOPE_STORE
        );
    }
}

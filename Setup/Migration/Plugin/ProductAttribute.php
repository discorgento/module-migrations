<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Plugin;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\AttributeSetRepository;

class ProductAttribute extends Common\EavAttribute
{
    public const ENTITY_TYPE = ProductAttributeInterface::ENTITY_TYPE_CODE;

    protected ProductAction $productAction;
    protected AttributeManagement $attributeManagement;
    protected AttributeSetRepository $attributeSetRepository;

    public function __construct(
        Common\EavAttribute\Context $context,
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
     * @param string $code
     * @param array $options
     */
    public function assignToAttributeSet($code, $options = [])
    {
        $attributeSetId = $options['attribute_set_id'] ??
            $this->getEavSetup()->getDefaultAttributeSetId(self::ENTITY_TYPE);

        /** @var \Magento\Eav\Model\Entity\Attribute\Set */
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);
        $this->attributeManagement->assign(
            self::ENTITY_TYPE,
            $attributeSet->getId(),
            $options['group_id'] ?? $attributeSet->getDefaultGroupId(),
            $code,
            $options['sort_order'] ?? 99
        );
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

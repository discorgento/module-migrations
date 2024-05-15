<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Facade;

use Discorgento\Migrations\Common\EavAttribute;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Eav\Model\Entity\Attribute\Group as AttributeGroup;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductAttribute extends EavAttribute
{
    public const ENTITY_TYPE = ProductAttributeInterface::ENTITY_TYPE_CODE;

    /** @var AttributeManagement */
    private $attributeManagement;

    /** @var AttributeSetRepository */
    private $attributeSetRepository;

    /** @var ProductAction */
    private $productAction;

    // phpcs:ignore
    public function __construct(
        EavAttribute\Context $context,
        AttributeManagement $attributeManagement,
        AttributeSetRepository $attributeSetRepository,
        ProductAction $productAction
    ) {
        parent::__construct($context);
        $this->attributeManagement = $attributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->productAction = $productAction;
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
                'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
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
     * @param int|string|AttributeSet $attributeSet
     * @param int|string|AttributeGroup $group
     * @param string $after
     */
    public function assignToAttributeSet($attributeCode, $attributeSet = null, $group = null, $after = null)
    {
        if (is_array($attributeSet)) {
            return $this->assignToAttributeSetLegacy($attributeCode, $attributeSet);
        }

        $attributeSetId = $this->resolveAttributeSetId($attributeSet);
        $attributeGroupId = $this->resolveGroupId($group, $attributeSet);
        $sortOrder = $after ? $this->getSortOrder($after, $attributeSet) + 1 : 999;

        $this->attributeManagement->assign(
            self::ENTITY_TYPE,
            $attributeSetId,
            $attributeGroupId,
            $attributeCode,
            $sortOrder
        );
    }

    /**
     * Old implementation of attribute set assign
     * for retrocompatibility purposes only
     *
     * @param string $attributeCode
     * @param array $options
     */
    private function assignToAttributeSetLegacy($attributeCode, $options = [])
    {
        $attributeSetId = (int) ($options['attribute_set_id'] ?? $this->getEavSetup()
            ->getDefaultAttributeSetId(self::ENTITY_TYPE));

        $attributeGroupId = $options['group_id'] ?? $this->getDefaultGroupId($attributeSetId);
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

    /**
     * Update given attribute value for multiple products at once
     *
     * @param array $entityIds
     * @param array $data
     * @return void
     */
    public function massUpdate($entityIds, $data)
    {
        $this->productAction->updateAttributes(
            $entityIds,
            $data,
            self::SCOPE_STORE
        );
    }

    /**
     * Return the id of given attribute set
     *
     * @param int|string|AttributeSet $attributeSet
     */
    private function resolveAttributeSetId($attributeSet = null) : int
    {
        if ($attributeSet === null) {
            return $this->getDefaultAttributeSetId();
        }

        if (is_object($attributeSet)) {
            return (int) $attributeSet->getId();
        }

        if (!is_int($attributeSet)) {
            $select = $this->getConnection()->select()
                ->from($this->getTableName('eav_attribute_set'), 'attribute_set_id')
                ->where('attribute_set_name = ?', $attributeSet)
                ->where('entity_type_id = ?', $this->getEntityTypeId());
            $attributeSetId = $this->getConnection()->fetchOne($select);

            if (empty($attributeSetId)) {
                throw new NoSuchEntityException(__("Attribute Set with name $attributeSet not found"));
            }

            return (int) $attributeSetId;
        }

        return $attributeSet;
    }

    /**
     * Resolve given attribute set group into its id
     *
     * @param int|string|AttributeGroup $group
     * @param int|string|AttributeSet $attributeSet
     */
    private function resolveGroupId($group, $attributeSet = null) : int
    {
        if ($group === null) {
            return $this->getDefaultGroupId($attributeSet);
        }

        if (is_object($group)) {
            return (int) $group->getId();
        }

        if (!is_int($group)) {
            $select = $this->getConnection()->select()
                ->from($this->getTableName('eav_attribute_group'), 'attribute_group_id')
                ->where('attribute_group_name = ?', $group)
                ->where('attribute_set_id = ?', $this->resolveAttributeSetId($attributeSet));
            $groupId = $this->getConnection()->fetchOne($select);

            if (empty($groupId)) {
                throw new NoSuchEntityException(__("Attribute Group with name $attributeSet not found"));
            }

            return (int) $groupId;
        }

        return $group;
    }

    /**
     * Retrieve default product attribute set id
     */
    private function getDefaultAttributeSetId() : int
    {
        return (int) $this->getEavSetup()->getDefaultAttributeSetId(self::ENTITY_TYPE);
    }

    /**
     * Retrieve default group id of given attribute set
     *
     * @param int|string|AttributeSet $attributeSet
     */
    private function getDefaultGroupId($attributeSet = null) : int
    {
        $attributeSetId = $this->resolveAttributeSetId($attributeSet);
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);

        return (int) $attributeSet->getDefaultGroupId();
    }

    /**
     * Retrieve current sort position of given attribute
     *
     * @param string $attributeCode
     * @param string|int|AttributeSet $attributeSet
     * @return int
     */
    private function getSortOrder($attributeCode, $attributeSet = null)
    {
        $entityTypeId = $this->getEntityTypeId();
        $attributeSetId = $this->resolveAttributeSetId($attributeSet);

        $attributeIdSelect = $this->getConnection()->select()
            ->from($this->getTableName('eav_attribute'), 'attribute_id')
            ->where('attribute_code = ?', $attributeCode);

        $select = $this->getConnection()->select()
            ->from($this->getTableName('eav_entity_attribute'), 'sort_order')
            ->where('entity_type_id = ?', $entityTypeId)
            ->where('attribute_set_id = ?', $attributeSetId)
            ->where('attribute_id = ?', $attributeIdSelect);

        return (int) $this->getConnection()->fetchOne($select) ?: 999;
    }
}

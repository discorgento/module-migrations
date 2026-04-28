# 🎭 Facades

- [📊 Admin Config](#-admin-config)
- [🎨 CMS Content](#-cms-content)
- [🧩 EAV Attributes](#-eav-attributes)
  - [🗂️ Category](#-category-attributes)
  - [👤 Customer](#-customer-attributes)
  - [🛍️ Product](#-product-attributes)

### 📊 Admin Config

Use this helper to manage `core_config_data` entries inside patches.

Import it as follows:
```php
public function __construct(
    Migration\Context $context,
    private Migration\Facade\AdminConfig $adminConfig
) {
    parent::__construct($context);
}
```

#### `get($path, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = null)`
Retrieve existing config for a given path:
```php
$username = $this->adminConfig->get('my/module/user');
```

#### `set($path, $value = null, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = 0)`
Override an existing entry, or create a new one if needed. You can also pass an array of paths, and each item may override `scope` and `scope_id`:
```php
$this->adminConfig->set('payment/creditcard/identifier', 'My Store');

$this->adminConfig->set([
    'catalog/frontend/flat_catalog_category' => '1',
    'web/secure/use_in_frontend' => [
        'value' => '1',
        'scope' => 'stores',
        'scope_id' => 2,
    ],
]);
```

#### `restore($path, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = 0)`
Equivalent to checking the "restore" checkbox in admin. Force the setting to fallback to its default value defined in some `config.xml`. This method accepts either a single path or an array of paths:
```php
$this->adminConfig->restore('shipping/carrier/show_foo_in_checkout');

$this->adminConfig->restore([
    'web/secure/use_in_frontend',
    'design/head/includes',
]);
```

#### `append($path, $value, $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = null)`
Append a value to a given setting. Useful for managing the HTML scripts theme field and similar ones.
```php
$this->adminConfig->append('design/head/includes', <<<HTML
    <script src="https://another.slow.chat/script.js" async></script>
HTML);
```

#### `appendOption($path, $option, $separator = ',', $scope = ScopeConfig::SCOPE_TYPE_DEFAULT, $scopeId = null)`
Append one or multiple options to list-like config values, avoiding duplicates:
```php
$this->adminConfig->appendOption(
    'catalog/frontend/flat_catalog_category',
    'new_option'
);
```

### 🎨 CMS Content

Use these helpers to create, read, update, and delete CMS pages or blocks.

To import them:
```php
public function __construct(
    Migration\Context $context,
    private Migration\Facade\CmsBlock $cmsBlock,
    private Migration\Facade\CmsPage $cmsPage,
) {
    parent::__construct($context);
}
```

Both `CmsPage` and `CmsBlock` share the same methods below. Methods that read or write store assignments accept `$storeId = null` to use the default/global scope.

#### `get($identifier, $storeId = null)`
Load a page or block by its identifier:
```php
$footerBlock = $this->cmsBlock->get('footer');
```

#### `create($identifier, $data, $storeId = null)`
Create a new entry on the database for the given content:
```php
$this->cmsPage->create('my-new-page', [
    'title' => 'Lorem Ipsum',
    'content' => <<<HTML
        <span>Hello World!</span>
    HTML,
]);
```

#### `createIfNotExists($identifier, $data, $storeId = null)`
Similar to `create()`, but if the content already exists it leaves it unchanged and returns that existing page or block instance.

#### `update($identifier, $data, $storeId = null)`
Update the content of the existing CMS content:
```php
$this->cmsBlock->update('footer', [
    'title' => 'Lorem Ipsum',
    'content' => <<<HTML
        <ul><!-- page builder generated stuff --></ul>
    HTML,
]);
```

#### `updateIfExists($identifier, $data, $storeId = null)`
Similar to `update()`, but only applies changes when the content already exists. Returns the updated page or block instance, or `null` when nothing matches.

#### `createOrUpdate($identifier, $data, $storeId = null)`
If content with the given identifier already exists, update it with the provided data. Otherwise, create it:
```php
$this->cmsPage->createOrUpdate('my-page', [
    'title' => 'Lorem Ipsum',
    'content' => <<<HTML
        <span>Hello World!</span>
    HTML,
]);
```

#### `delete($identifier)`
Delete the respective CMS entity:
```php
$this->cmsBlock->delete('seasonal-offers');
```

#### `deleteIfExists($identifier)`
Similar to `delete()`, but skips deletion when the CMS entity already doesn't exists.

#### `exists($identifier, $storeId = null)`
Check if a page or block with the given identifier already exists on the database:
```php
if (!$this->cmsBlock->exists('seasonal-offers')) {
    $this->cmsBlock->create('seasonal-offers', [
        'title' => 'Seasonal Offers',
        'content' => '<p>Seasonal offers</p>',
    ]);
}
```

> [!TIP]  
> Use Page Builder normally and copy the `content` field directly from your database to fill the HTML `content`.

### 🧩 EAV Attributes
These methods are shared by `CategoryAttribute`, `CustomerAttribute`, and `ProductAttribute` facades.

#### `create($code, $data)`
Create a new EAV attribute:
```php
$attribute = $this->productAttribute->create('brand_badge', [
    'label' => 'Brand Badge',
    'input' => 'text',
    'type' => 'varchar',
]);
```

#### `createIfNotExists($code, $data)`
Similar to `create()`, but if the attribute already exists it leaves it unchanged and returns that existing attribute instance.

#### `update($code, $data)`
Update an existing attribute definition:
```php
$this->productAttribute->update('brand_badge', [
    'label' => 'Brand Highlight',
]);
```

#### `updateIfExists($code, $data)`
Similar to `update()`, but skips the update when the attribute does not exist.

#### `delete($code)`
Delete an existing attribute definition.

#### `deleteIfExists($code)`
Similar to `delete()`, but skips deletion when the attribute does not exist.

#### `exists($code)`
Check whether an attribute already exists:
```php
if (!$this->productAttribute->exists('brand_badge')) {
    $this->productAttribute->create('brand_badge', [
        'label' => 'Brand Badge',
        'input' => 'text',
        'type' => 'varchar',
    ]);
}
```

### 🗂️ Category Attributes

Use this helper to manage category attributes and mass update category attribute values.

Import it as follows:
```php
public function __construct(
    Migration\Context $context,
    private Migration\Facade\CategoryAttribute $categoryAttribute
) {
    parent::__construct($context);
}
```

#### `massUpdate($entityIds, $data)`
Mass update given categories. This changes entity attribute values, not the attribute definition itself:
```php
$categoryIds = [4, 5];
$this->categoryAttribute->massUpdate($categoryIds, [
    'description' => 'Clothes',
]);
```

### 👤 Customer Attributes

Use this helper to manage customer attributes.

Use it as follows:
```php
public function __construct(
    Migration\Context $context,
    private Migration\Facade\CustomerAttribute $customerAttribute
) {
    parent::__construct($context);
}
```

### 🛍️ Product Attributes

Use this helper to manage product attributes, assign them to attribute sets, and mass update product attribute values.

Import it as follows:
```php
public function __construct(
    Migration\Context $context,
    private Migration\Facade\ProductAttribute $productAttribute
) {
    parent::__construct($context);
}
```

#### `createDropdown($code, $label, $values, $config = [])`
Create a new dropdown attribute:
```php
$attribute = $this->productAttribute->createDropdown('sizes', 'Clothes Size', ['P', 'M', 'G']);
```

#### `createDropdownIfNotExists($code, $label, $values, $config = [])`
Similar to `createDropdown()`, but returns the existing attribute when it already exists.

#### `assignToAttributeSet($attributeCode, $attributeSet = null, $group = null, $after = null)`
Simulates the process of navigating to `Stores -> Attribute Set` and assigning an attribute there:
```php
$this->productAttribute->assignToAttributeSet(
    'size',
    'Clothes',
    'Tech Specs',
    'gender'
);
```

#### `unassignFromAttributeSet($attributeCode, $attributeSetId = null)`
Remove an attribute from the given attribute set. When `$attributeSetId` is omitted, the default product attribute set is used:
```php
$this->productAttribute->unassignFromAttributeSet(
    'size',
    12
);
```

#### `massUpdate($entityIds, $data)`
Mass update given products without changing the attribute definition itself:
```php
$productIds = [1, 2, 3];
$this->productAttribute->massUpdate($productIds, [
    'brand_badge' => 'Featured',
]);
```

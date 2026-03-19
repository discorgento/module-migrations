# Discorgento Migrations
Magento 2 module that simplifies Magento data patches via a base `Migration` class and task-specific facades.

## What AI agents should know first
- Use this package when you need **repeatable, deploy-safe data changes** (instead of manual admin/database edits).
- Typical patch location is `Vendor/Module/Setup/Patch/Data/*`.
- Patches run through `bin/magento setup:upgrade`.
- Prefer facade methods over raw SQL when a facade already supports the operation.

## Patch structure (standard pattern)
1. Create a data patch class extending `Discorgento\Migrations\Setup\Migration`.
2. Inject `Migration\Context` plus required facades in the constructor.
3. Put main logic in `protected function execute()`.
4. Optionally implement `protected function rollback()` for revert support.
5. Add `getDependencies()` / `getAliases()` when ordering or renaming patches is needed.

## Runtime behavior that affects agent decisions
- `apply()` and `revert()` are wrapped by the base class; implement `execute()` / `rollback()`, not raw `apply()` / `revert()`.
- Setup start/end is already handled by the base class.
- Alias handling is built in (old patch names in `getAliases()` can prevent duplicate execution).
- `AREA_CODE` can be set in the patch class when logic depends on a specific area.

## Facade map (from wiki)
### Admin Config (`Migration\Facade\AdminConfig`)
- `get($path, $scope, $scopeId)` — retrieve the current value for a config path
- `set($path, $value, $scope, $scopeId)` — override an existing entry, or create a new one if needed
- `restore($path, $scope, $scopeId)` — equivalent to checking the "restore" checkbox in admin; forces the setting to fall back to its `config.xml` default
- `append($path, $value, $scope, $scopeId)` — append a raw string to an existing config value (useful for fields like `design/head/includes`)

### CMS Content (`Migration\Facade\CmsPage`, `Migration\Facade\CmsBlock`)
- `create($identifier, $data, $storeId)` — create a new page/block entry in the database
- `update($identifier, $data, $storeId)` — update the content of an existing page/block
- `createOrUpdate($identifier, $data, $storeId)` — update if already exists, otherwise create
- `delete($identifier, $storeId)` — delete a page/block by identifier, optionally scoped to a store
- `exists($identifier, $storeId)` — check if a page/block with the given identifier already exists

### EAV Attributes (base capabilities)
Shared by category/customer/product attribute facades:
- `create($code, $data)` — create a new attribute with the given data (same interface as native `addAttribute()`)
- `update($code, $data)` — update an existing attribute's properties
- `exists($code)` — check if an attribute with the given code already exists for the entity

### Category Attributes (`Migration\Facade\CategoryAttribute`)
- Inherits all EAV methods above
- `massUpdate($entityIds, $data)` — update attribute *values* on multiple existing categories at once

### Customer Attributes (`Migration\Facade\CustomerAttribute`)
- Inherits EAV methods (`create`, `update`, `exists`)

### Product Attributes (`Migration\Facade\ProductAttribute`)
- Inherits EAV methods (`create`, `update`, `exists`)
- `createDropdown($code, $label, $values, $config)` — shorthand to create a dropdown/select attribute with the given options
- `assignToAttributeSet($code, $attributeSet, $group, $after)` — assign an attribute to an attribute set/group, simulating the admin "Stores → Attribute Sets" flow
- `unassignFromAttributeSet($attributeCode, $attributeSetId)` — remove an attribute from an attribute set
- `massUpdate($entityIds, $data)` — update attribute *values* on multiple existing products at once

## Agent implementation guidelines
- Write idempotent logic whenever possible (`exists` checks or `createOrUpdate`).
- For CMS content, prefer stable identifiers and explicit `storeId` when scope matters.
- For EAV, separate “attribute definition changes” from “entity value changes” (`massUpdate`).
- Keep one clear responsibility per patch; chain with `getDependencies()` when needed.
- After changes, run:
  - `bin/magento setup:upgrade`
  - verify outcome in admin/storefront for the touched entity/config.

## References
- Wiki home: `https://github.com/discorgento/module-migrations/wiki`
- Admin Config: `https://github.com/discorgento/module-migrations/wiki/Admin-Config`
- CMS Content: `https://github.com/discorgento/module-migrations/wiki/CMS-Content`
- EAV Attributes: `https://github.com/discorgento/module-migrations/wiki/Eav-Attributes`
- Category Attributes: `https://github.com/discorgento/module-migrations/wiki/Category-Attributes`
- Customer Attributes: `https://github.com/discorgento/module-migrations/wiki/Customer-Attributes`
- Product Attributes: `https://github.com/discorgento/module-migrations/wiki/Product-Attributes`


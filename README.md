# ⚠ WIP - DO NOT USE IT YET ⚠

# Discorgento Migrations ⚙️
Tools to speed up A LOT the migration (aka. data patch) creation process.

## Overview
Instead of the [very verbose native approach](https://devdocs.magento.com/guides/v2.4/extension-dev-guide/declarative-schema/data-patches.html), new migrations can be created easily by simply extending the `\Discorgento\Migrations\Setup\Migration` class, and implementing your logic on the `execute()` method, like this:
```php
<?php namespace Discorgento\ThemeHelper\Setup\Patch\Data;

use Discorgento\Migrations\Setup\Migration;

class YourMigration extends Migration
{
    protected function execute()
    {
        // update something in database
    }

    protected function rollback()
    {
        // undo the change (optional)
    }
}
```
> 💡 You can quickly test your just-created migration using `bin/magerun2 dev:con '$di->create(Discorgento\ThemeHelper\Setup\Patch\Data\YourMigration::class)->apply()'` instead of wasting time with multiple setup:upgrade

## "Plugins"
Do not confuse with Magento plugins. "Plugins" here are used for naming plug-and-play classes specialized on specific types of migrations:

@todo docs plugins available [here](Setup/Migration/Plugin)

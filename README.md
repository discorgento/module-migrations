![Discorgento Migrations](docs/header.png)

<p align="center">A dev-friendly approach to keep track of database changes in Magento 2</p>
<p align="center">
    <a href="https://github.com/discorgento/module-migrations/stargazers" target="_blank"><img alt="GitHub Stars" src="https://img.shields.io/github/stars/discorgento/module-migrations?style=social"/></a>
    <a href="https://packagist.org/packages/discorgento/module-migrations/stats" target="_blank"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/discorgento/module-migrations"/></a>
    <a target="_blank" href="https://packagist.org/packages/discorgento/module-migrations"><img src="https://img.shields.io/packagist/v/discorgento/module-migrations" alt="Latest Version on Packagist"></a>
    <a target="_blank" href="https://discord.io/Discorgento"><img alt="Join our Discord" src="https://img.shields.io/discord/768653248902332428?color=%237289d9&label=Discord"/></a>
</p>

<p align="center">Our Sponsors</p>
<p align="center">
    <a href="https://www.caravelx.com/"><img src="docs/sponsors/caravelx.svg" alt="Caravel X"></a>
</p>

## Overview 💭
Just changed something on the admin panel or on the database and now you need to replicate it again in staging and production? No worries, [we](https://discord.io/Discorgento) got you covered.

Probably you already heard about [data patches](https://developer.adobe.com/commerce/php/development/components/declarative-schema/patches/), the Magento way of writing database migrations (at least I hope so; you're not redoing those changes manually in each environment, right? [Right??](https://i.imgflip.com/4/5c7lwq.jpg)). Although as always with Magento things, they are just too ridiculously verbose to write without checking the docs every single time.

But what if I say that it can be really, really simplified?  
![FMAB Sloth](docs/such-a-pain.gif)

## Install 🔧
This module is compatible with both Magento 2.3 and 2.4, from PHP 7.3 to 8.1.
```
composer require discorgento/module-migrations:^2 && bin/magento setup:upgrade
```

## Demo 🎥
Quick demo/tutorial on how to use it:  
<a href="https://odysee.com/@discorgento:8/Introduction-to-Module-Migrations-Magento-discorgento-module-migrations:a"><img src="https://user-images.githubusercontent.com/4603111/202745678-d9960d66-4618-4100-aee1-50a4cc728829.png" height="200"/></a>  
There's also an extended version in Brazillian Portuguese including CMS content management overview available [here](https://odysee.com/@discorgento:8/Introdu%C3%A7%C3%A3o-ao-Modulo-Migrations-Magento-discorgento-module-migrations:9).

## Usage ⚙️
Let's take a look at the basic structure of a native data patch:
(let's say, _app/code/YourCompany/YourModule/Setup/Patch/Data/DoSomething.php_)
```php
<?php declare(strict_types=1);
/** Copyright © Your Company. All rights reserved. */

namespace YourCompany\YourModule\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class DoSomething implements DataPatchInterface, PatchRevertableInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /** @inheritdoc */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // do stuff

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        // undo stuff (actually nobody cares about this)

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /** @inheritdoc */
    public function getAliases()
    {
        return [];
    }

    /** @inheritdoc */
    public static function getDependencies()
    {
        return [];
    }
}
```

That's just the skeleton of a native data patch. Insane.  
Now using this module, the skeleton drops to just this small snippet:

```php
<?php declare(strict_types=1);
/** Copyright © Your Company. All rights reserved. */

namespace YourCompany\YourModule\Setup\Patch\Data;

use Discorgento\Migrations\Setup\Migration;

class DoSomething extends Migration
{
    /** @inheritdoc */
    protected function execute()
    {
        // do something
    }
}
```
From 50 lines to just 15, or simply 70% less code. SEVENTY percent fewer lines.
But we're just getting started.

## Facades 🥤
There's some common stuff when it comes to migrations: changing admin config settings, managing cms content, create product attributes, etc. So for this, we've created some [Facades](https://refactoring.guru/design-patterns/facade) to speed up the repetitive parts.

For example, if you need to create a cms page, instead of writting [all of this](https://magento.stackexchange.com/questions/127495/how-to-add-a-cms-block-programmatically-in-magento-2), you can simply use our CmsPage facade:

```php
<?php declare(strict_types=1);
/** Copyright © Your Company. All rights reserved. */

namespace YourCompany\YourModule\Setup\Patch\Data;

use Discorgento\Migrations\Setup\Migration;

class CmsPageMyNewPage extends Migration
{
    private Migration\Facade\CmsPage $cmsPage;

    public function __construct(
        Migration\Context $context,
        Migration\Facade\CmsPage $cmsPage
    ) {
        parent::__construct($context);
        $this->cmsPage = $cmsPage;
    }

    protected function execute()
    {
        $this->cmsPage->create('my-new-page', [
            'title' => 'Lorem Ipsum',
            'content' => <<<HTML
                <span>Hello World!</span>
            HTML,
        ]);
    }
}
```

Run a `bin/magento setup:upgrade` and navigate to the _/my-new-page route_, the page will be there. And this will be automatically replicated in your staging/production (and even other dev machines) environments.

> 💡 **Tip:** We have facades for the most common tasks we came across so far, **don't forget to check out the [official wiki](https://github.com/discorgento/module-migrations/wiki) to make the most use of this powerful m2 tool ;)**

## Notes 🗒
 - roadmap: create cli command to generate migrations for existant cms content (thanks [@vpjoao98](https://github.com/vpjoao98));
 - issues and PRs are welcome in this repo;
 - we want **YOU** for [our community](https://discord.io/Discorgento)!

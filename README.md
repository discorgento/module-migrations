![Discorgento Migrations](docs/header.png)

<p align="center">A dev-friendly approach to handle background jobs in Magento 2</p>
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

Probably you already heard about [data patches](https://developer.adobe.com/commerce/php/development/components/declarative-schema/patches/), the Magento way of writing database migrations (at least I hope so; you're not redoing those changes manually in each environment, right? [Right](https://i.imgflip.com/4/5c7lwq.jpg)?). But as always with Magento things, they are just too ridiculously verbose to write without checking the docs every single time.

Well, what if I say that it can be really, _really_ simplified?  
![FMAB Sloth](docs/such-a-pain.gif)

## Install 🔧
This module is compatible with both Magento 2.3 and 2.4, from PHP 7.3 to 8.1.
```
composer require discorgento/module-migrations:^2 && bin/magento setup:upgrade
```

## Usage ⚙️
@todo

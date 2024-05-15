<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Facade;

use Discorgento\Migrations\Common\Cms;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

class CmsPage extends Cms
{
    // phpcs:ignore
    public function __construct(
        PageCollectionFactory $collectionFactory,
        PageFactory $factory,
        PageRepository $repository
    ) {
        parent::__construct($collectionFactory, $factory, $repository);
    }
}

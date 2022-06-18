<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Plugin;

use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

class CmsPage extends Common\Cms
{
    public function __construct(
        PageCollectionFactory $collectionFactory,
        PageFactory $factory,
        PageRepository $repository
    ) {
        parent::__construct($collectionFactory, $factory, $repository);
    }
}

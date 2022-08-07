<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Plugin;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;

class CmsBlock extends Common\Cms
{
    public function __construct(
        BlockCollectionFactory $collectionFactory,
        BlockFactory $factory,
        BlockRepository $repository
    ) {
        parent::__construct($collectionFactory, $factory, $repository);
    }
}

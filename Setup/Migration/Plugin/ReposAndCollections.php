<?php declare(strict_types=1);
/** Copyright © Discorgento. All rights reserved. */

namespace Discorgento\Migrations\Setup\Migration\Plugin;

use Magento\Catalog\Api\CategoryRepositoryInterface as CategoryRepository;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Quote\Api\CartRepositoryInterface as QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuotesCollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrdersCollectionFactory;

class ReposAndCollections
{
    protected CategoryCollectionFactory $categoryCollectionFactory;
    protected CategoryRepository $categoryRepository;
    protected ProductCollectionFactory $productCollectionFactory;
    protected ProductRepository $productRepository;
    protected OrdersCollectionFactory $ordersCollectionFactory;
    protected OrderRepository $orderRepository;
    protected QuotesCollectionFactory $quotesCollectionFactory;
    protected QuoteRepository $quoteRepository;

    // @todo use proxies to avoid overhead
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryRepository $categoryRepository,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        OrdersCollectionFactory $ordersCollectionFactory,
        OrderRepository $orderRepository,
        QuotesCollectionFactory $quotesCollectionFactory,
        QuoteRepository $quoteRepository
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->ordersCollectionFactory = $ordersCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->quotesCollectionFactory = $quotesCollectionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Retrieve an instance of category repository
     * @return CategoryRepository
     */
    public function getCategoryRepository()
    {
        return $this->categoryRepository;
    }

    /**
     * Retrieve a fresh instance of categories collection
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoriesCollection()
    {
        return $this->categoryCollectionFactory->create();
    }

    /**
     * Retrieve an instance of product repository
     * @return ProductRepository
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * Retrieve a fresh instance of products collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductsCollection()
    {
        return $this->productCollectionFactory->create();
    }

    /**
     * Retrieve a fresh instance of orders collection
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrdersCollection()
    {
        return $this->ordersCollectionFactory->create();
    }

    /**
     * Retrieve an instance of order repository
     * @return OrderRepository
     */
    public function getOrderRepository()
    {
        return $this->orderRepository;
    }

    /**
     * Retrieve a fresh instance of quotes collection
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    public function getQuotesCollection()
    {
        return $this->quoteCollectionFactory->create();
    }

    /**
     * Retrieve an instance of quote repository
     * @return QuoteRepository
     */
    public function getQuoteRepository()
    {
        return $this->quoteRepository;
    }
}

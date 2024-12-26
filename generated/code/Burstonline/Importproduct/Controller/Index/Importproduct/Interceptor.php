<?php
namespace Burstonline\Importproduct\Controller\Index\Importproduct;

/**
 * Interceptor class for @see \Burstonline\Importproduct\Controller\Index\Importproduct
 */
class Interceptor extends \Burstonline\Importproduct\Controller\Index\Importproduct implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory, \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry, \Magento\Indexer\Model\IndexerFactory $indexerFactory, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Burstonline\Importproduct\Model\ImportlogFactory $importlogFactory, \Magento\Framework\App\ResourceConnection $resourceConnection, \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Framework\App\Filesystem\DirectoryList $directoryList, \Magento\Framework\Filesystem\Io\File $file)
    {
        $this->___init();
        parent::__construct($context, $pageFactory, $productFactory, $productRepository, $stockRegistry, $indexerFactory, $categoryFactory, $importlogFactory, $resourceConnection, $connectionFactory, $productCollectionFactory, $directoryList, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}

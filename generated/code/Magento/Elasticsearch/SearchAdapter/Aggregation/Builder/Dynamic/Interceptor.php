<?php
namespace Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic;

/**
 * Interceptor class for @see \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic
 */
class Interceptor extends \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder\Dynamic implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Search\Dynamic\Algorithm\Repository $algorithmRepository, \Magento\Framework\Search\Dynamic\EntityStorageFactory $entityStorageFactory)
    {
        $this->___init();
        parent::__construct($algorithmRepository, $entityStorageFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function build(\Magento\Framework\Search\Request\BucketInterface $bucket, array $dimensions, array $queryResult, \Magento\Framework\Search\Dynamic\DataProviderInterface $dataProvider)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'build');
        return $pluginInfo ? $this->___callPlugins('build', func_get_args(), $pluginInfo) : parent::build($bucket, $dimensions, $queryResult, $dataProvider);
    }
}

<?php
namespace Amasty\Storelocator\Model\Import\Location;

/**
 * Interceptor class for @see \Amasty\Storelocator\Model\Import\Location
 */
class Interceptor extends \Amasty\Storelocator\Model\Import\Location implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Json\Helper\Data $jsonHelper, \Magento\ImportExport\Helper\Data $importExportData, \Magento\ImportExport\Model\ResourceModel\Import\Data $importData, \Magento\Framework\App\ResourceConnection $resource, \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper, \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator, \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Filesystem\File\ReadFactory $readFactory, \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Amasty\Storelocator\Model\Import\Proxy\Location\ResourceModelFactory $resourceModelFactory, \Amasty\Storelocator\Model\Import\Validator\Country $validatorCountry, \Amasty\Storelocator\Model\Import\Validator\Photo $validatorPhoto, \Amasty\Storelocator\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory, \Amasty\Storelocator\Model\LocationFactory $locationFactory, \Amasty\Storelocator\Helper\Data $dataHelper, \Amasty\Storelocator\Model\Import\Validator $validator, \Amasty\Base\Model\Serializer $serializer, \Magento\Catalog\Model\ImageUploader $imageUploader, \Amasty\Storelocator\Model\ImageProcessor $imageProcessor, \Amasty\Storelocator\Model\GalleryFactory $galleryFactory, \Amasty\Storelocator\Model\ConfigProvider $configProvider, \Amasty\Storelocator\Model\ResourceModel\Gallery $galleryResource, \Magento\Framework\App\Request\Http $request)
    {
        $this->___init();
        parent::__construct($jsonHelper, $importExportData, $importData, $resource, $resourceHelper, $errorAggregator, $uploaderFactory, $filesystem, $readFactory, $curlFactory, $scopeConfig, $resourceModelFactory, $validatorCountry, $validatorPhoto, $attributeCollectionFactory, $locationFactory, $dataHelper, $validator, $serializer, $imageUploader, $imageProcessor, $galleryFactory, $configProvider, $galleryResource, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function isNeedToLogInHistory()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'isNeedToLogInHistory');
        return $pluginInfo ? $this->___callPlugins('isNeedToLogInHistory', func_get_args(), $pluginInfo) : parent::isNeedToLogInHistory();
    }
}

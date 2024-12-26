<?php

namespace Burstonline\Epipayment\Block\Adminhtml\Order\View\Tab;
use Magento\Directory\Model\RegionFactory as RegionCollectionFactory;
use Burstonline\Epipayment\Model\EpiLogFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;

class Epilog extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'Burstonline_Epipayment::order/view/tab/epilog.phtml';
    /**
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;
    protected $quoteFactory;
    protected $_productRepository;
    protected $RegionCollectionFactory;
    protected $_modelEpilogFactory;
    protected $_backendUrl;
    protected $scopeConfig;
    protected $shippingmodelconfig;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry             $registry,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentCollection,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Sales\Model\OrderFactory $orderData,
        RegionCollectionFactory $regionCollectionFactory,
        EpilogFactory $modelEpilogFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        Config $shippingmodelconfig, 
        ScopeConfigInterface $scopeConfig,
        array                                   $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->quoteFactory = $quoteRepository;
        $this->productFactory = $productFactory;
        $this->_shipmentCollection = $shipmentCollection;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->_modelEpilogFactory = $modelEpilogFactory;
        $this->_backendUrl = $backendUrl;
        $this->orderData = $orderData;
        $this->shippingmodelconfig = $shippingmodelconfig;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve order model instance
     *
     * @return int
     *Get current id order
     */

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * Retrieve order increment id
     *
     * @return string
     */
    // Get EPI Log Title
    public function getTitle()
    {
        return "Epi payment API Log Status";
    }

    // Ger API Data

    public function getApidata($apicall)
    {
        $formatteddata = "";
        $apiAction = 'integration/auth/token';

        if($apicall == "tokenauth"){
            $apiAction = 'integration/auth/token';
        }
        else if($apicall == "createorderlog"){
            $apiAction = 'create order';
        }
        else if($apicall == "tokenexlog"){
            $apiAction = 'tokenex_api';
        }
        else if($apicall == "paymentauth"){
            $apiAction = 'payment auth';
        }
        else if($apicall == "completionlog"){
            $apiAction = 'Order Completion';
        } 
        else if($apicall == "xtlog"){
            $apiAction = 'Send Data to XT';
        }
        else if($apicall == "refund"){
            $apiAction = 'refund';
        }
        else if($apicall == "Cancel"){
            $apiAction = 'Cancel';
        }
        $formattedData = "";
        $orderIncId = $this->getOrderIncrementId();
        $epiLogCollection = $this->_modelEpilogFactory->create()->getCollection()
        ->addFieldToSelect('*')
        ->addFieldToFilter('orderID', $orderIncId)
        ->addFieldToFilter('apiAction', $apiAction);
        if ($epiLogCollection->getSize() > 0) {
            foreach ($epiLogCollection as $epiDataItem) {
                // Call your formatting function for each record
                $formattedData .= $this->getFormattedapidata($apicall, $epiDataItem->getData(), $apiAction);
               // print_r($formattedData);
            }
        }
        else{
            $formattedData .= $this->getFormattedapidata($apicall, "", $apiAction);
        }
        //die;
        print_r($formattedData);
        return $formatteddata;
    }

    // Get Formatted Data

    public function getFormattedapidata($apiAction, $apiData, $apiCall)
    {
        
        $apirequestData = "";
        $apiresponseData = "";
        $id = 0;
            $formatteddata = '
            <div>
                <p>
            '.ucfirst($apiCall).' API log :: ';
            if (!empty($apiData)) {
                if ($apiData['responseStatus'] == 1) {
                    $formatteddata .= '<i class="successmark"></i>';
                } else {
                    $formatteddata .= '<i class="failmark"></i>';
                } 
                $apirequestData = ($apiAction == 'tokenauth' || $apiAction == 'tokenexlog') 
                    ? $apiData['requestData'] 
                    : (!empty($apiData['requestData']) ? json_decode($apiData['requestData'], TRUE) : "");

                $apiresponseData = !empty($apiData['returnData']) ? json_decode($apiData['returnData'], TRUE) : "";
                $id = $apiData['id'];

                $formatteddata .= '<div class="hide requestData' . $id . '"><h3>' . ucfirst($apiCall) . ' API log :: Request Data</h3><p><pre>' . (($apiAction == 'tokenauth' || $apiAction == 'tokenexlog') ? $apirequestData : json_encode($apirequestData, JSON_PRETTY_PRINT)) . '</pre></p></div>';
                $formatteddata .= '<div class="hide responseData' . $id . '"><h3>' . ucfirst($apiCall) . ' API log :: Response Data</h3><p><pre>' . json_encode($apiresponseData, JSON_PRETTY_PRINT) . '</pre></p></div>';

            } else {
                $formatteddata .= '<i class="notyetmark"></i> ';
                $formatteddata .= ($apiAction=='xtlog') ? ' <button class="syncorder"> <i class="syncmark"></i></button>' : '';
                $formatteddata .= '<div class="hide requestData' . $id . '"><h3>' . ucfirst($apiCall) . ' API log :: Request Data</h3><p>No Data</p></div>';
                $formatteddata .= '<div class="hide responseData' . $id . '"><h3>' . ucfirst($apiCall) . ' API log :: Response Data</h3><p>No Data</p></div>';
            }
            $formatteddata .= '
                    </p>
                    <a href="#" id="' . $id . '" class="apirequest">Request Data</a> | <a href="#" id="' . $id . '" class="apiresponse">Response Data</a>
                </div>
            ';
        return $formatteddata; 
    }
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Epi Log Status');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Epi Log Status');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
    public function getTabClass()
    {
        // I wanted mine to load via AJAX when it's selected
        // That's what this does
        return 'ajax only';
    }
    public function getClass()
    {
        return $this->getTabClass();
    }

}

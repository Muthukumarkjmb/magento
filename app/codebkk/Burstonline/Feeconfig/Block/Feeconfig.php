<?php
namespace Burstonline\Feeconfig\Block;

use Burstonline\Feeconfig\Model\FeeconfigFactory;

class Feeconfig extends \Magento\Framework\View\Element\Template
{
	protected $_feeconfigFactory;
	protected $_storeManager;
	protected $_countryFactory;
	protected $_countryCollectionFactory;
	protected $_region;
	protected $_request;
	
	protected $_storesCollection = null;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		FeeconfigFactory $feeconfigFactory,
		\Magento\Framework\App\RequestInterface $request,
		array $data = []
	) {
		$this->_feeconfigFactory = $feeconfigFactory;
		$this->_request = $request;
		parent::__construct($context, $data);
	}
	
	public function getFeeconfiglist()
    {
		$collection = array();
        $collection = $this->_feeconfigFactory->create()
							->getCollection()->addFieldToFilter('status', 1);
		return $collection->getData();
    }

}

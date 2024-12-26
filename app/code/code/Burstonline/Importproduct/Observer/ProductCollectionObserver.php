<?php
namespace Burstonline\Importproduct\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * ProductCollectionObserver
 */
class ProductCollectionObserver implements ObserverInterface
{
    /**
     * Handler for load product collection event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		//echo "wel";die;
        /* set your filter here */
        $observer->getEvent()
            ->getCollection()
            ->addAttributeToSelect('*');
            //->addAttributeToFilter('small_image', array('neq' => 'no_selection'))
            //->addAttributeToFilter('small_image', array('neq' => ''))
			//->addAttributeToFilter('small_image', array('neq' => null));;
        /* attribute_code (custom or default both are work) */
        /* If multiselect type attribute use finset as showing below */
//            ->addAttributeToFilter('attribute_code', ['finset' => [$attrOptionId]]); $attrOptionId like (1,2,3)
    
       return $this;
    }
}

<?php
namespace Epi\Observers\OrderObserver;
  

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use \Magento\Framework\Session\SessionManagerInterface;
use \Epi\EpiPay\lib\Tax;
use \Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\CacheInterface;
use \Zend\Json\Json;

class AddCustomTax implements ObserverInterface
{
    public $additionalTaxAmt;
    protected $customerSession;
    private $tax;

    public function __construct(
        Tax $tax,
        SessionManagerInterface $customerSession,
        ProductRepository $productRepository,
        CacheInterface $cache,
        Json $serializer
    )
    {    
        $ds = DIRECTORY_SEPARATOR;
        $this->ini = parse_ini_file(__DIR__ ."$ds../lib/config.ini");
        $this->customerSession=$customerSession;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/taxes1.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
        $this->additionalTaxAmt=0;
        $this->tax=$tax;
        $this->productRepository=$productRepository;
        $this->cache=$cache;
        $this->serializer=$serializer;
    }
    
    public function execute(Observer $observer)
    {
        try{
            $this->additionalTaxAmt=0;
            $this->logger->info('<----------Custom tax---------->');

            // fetch quote data
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            $items=$quote->getAllItems();
            
            foreach($items as $item) {
                $sku=$item->getSku();
                $this->logger->info("sku".print_r($sku,true));
                $itemQty=$item->getQty();
                if($cachedTax=$this->cache->load($sku)){
                    $this->logger->info("Cached tax->".print_r($cachedTax,true));
                    $itemTotalTax=$cachedTax*$itemQty;
                    $this->additionalTaxAmt+=$itemTotalTax;
                }
                else{
                   
                    $itemPrice=$item->getPrice();
                    $xtmid=$this->customerSession->getMxtmid();
                 
                    $itemIdAttribute=$this->productRepository->get($sku)->getCustomAttribute("item_id");
                   
                    // $this->logger->info('sku->'.print_r($item->getSku(),true));
                    // $this->logger->info('price->'.print_r($item->getPrice(),true));
                    // $this->logger->info('qty->'.print_r($item->getQty(),true));
                    // $this->logger->info('xtmid->'.print_r($this->customerSession->getMxtmid(),true));
                    if(!isset($itemIdAttribute)){
                     
                        $api_url=$this->ini['ExaTouchItemsRestAPI'].$xtmid."/"."sku/".$sku;
                        $this->logger->info('Exatouch Api URL->'.print_r($api_url,true));
                        $itemData=$this->tax->getDataFromXt($api_url);
                        if(count($itemData)!=0){
                            $itemId=$itemData['ItemID'];
                            $this->logger->info('itemid from API call->'.print_r($itemId,true));
                        }
                    }
                    else{
                        $itemId= $itemIdAttribute->getValue();
                        $this->logger->info('itemid->'.print_r($itemId,true));
                    }
                    $tax_api_url=$this->ini['ExaTouchTaxRestAPI'].$xtmid."/"."itemId/".$itemId."/tax";
                    $this->logger->info('Exatouch Tax Api URL->'.print_r($tax_api_url,true));

                    $taxData=$this->tax->getDataFromXt($tax_api_url);
                    if(count($taxData)!=0)
                    {
                        $itemTax=$this->tax->calculateTax($taxData,$itemPrice);
                        $itemTotalTax=$itemTax*$itemQty;
                        $this->additionalTaxAmt+=$itemTotalTax;
                        $this->cache->save($itemTax, $sku, [], 600);
                    }  
                }                     
            }
            $this->logger->info('total tax calculated->'.print_r($this->additionalTaxAmt,true));

            /** @var Magento\Quote\Model\Quote\Address\Total */
            $total = $observer->getData('total');
            $total->addTotalAmount('tax', round($this->additionalTaxAmt,2));
            $total->addBaseTotalAmount('tax',  round($this->additionalTaxAmt,2));
            $total->setGrandTotal(round((float)$total->getGrandTotal(),2) +  round($this->additionalTaxAmt,2));
            $total->setBaseGrandTotal(round((float)$total->getBaseGrandTotal(),2) +  round($this->additionalTaxAmt,2));

            return $this;
        }
        catch(\Exception $e){
            $this->logger->info($e->getMessage());
        }
    }
}
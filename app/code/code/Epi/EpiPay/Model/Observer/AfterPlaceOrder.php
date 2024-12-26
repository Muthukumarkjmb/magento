<?php

namespace Epi\EpiPay\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Zend\Json\Json;
use Epi\EpiPay\Logger\Logger;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    protected $order;
    private $logger;
     public function __construct(
        \Magento\Sales\Model\Order $order,
        \Zend\Http\Client $zendClient,
        Logger $logger
    )
    {
        $this->order = $order;
        $this->zendClient = $zendClient;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('<----------Inside AfterPlaceOrder Observer---------->');

        #include config file
        // $path = '/home/ubuntu/test/magento/app/code/Epi/EpiPay/lib';
        // set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        $ini = parse_ini_file(__DIR__ . "/config.ini");

        #initialize logger
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/OrderPlaced.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);

        #get orderId from event
        $orderId = $observer->getEvent()->getOrderIds();
        #get order details based on orderId
        $order = $this->order->load($orderId);
        #get customerId from order details
        $customer = $order->getCustomerId();
        #get items ordered in order
        $itemCollection = $order->getItemsCollection();
        #itemsData array to store sku and quantity
        $itemsData = array();
        #loop through each items in items collection to extract sku and quantity
        foreach($itemCollection as $item){
            if ($item->getData()) {
                $itemsData[] = [
                    'sku'=>$item->getSku(),
                    'qtyOrdered'=>$item->getQtyOrdered(),
                ];
            }
            $this->logger->info('Item Ordered->'.print_r($item->getSku(),true));
            $this->logger->info('Quantity->'.print_r($item->getQtyOrdered(),true));

        }
        $apiData['orderId']=$orderId;
        $apiData['orderedItems']=json_encode($itemsData, JSON_FORCE_OBJECT);
        $apiData['customerId']=$customer;

        // -------------------------------ZEND---------------------
        try 
        {
            $this->zendClient->reset();
            $this->zendClient->setUri($ini['inventoryApi'].'updateStock');
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
                $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                ]);

            $this->zendClient->setRawBody(Json::encode($itemsData));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response From updateStock->'.print_r($response->getBody(),true));
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in AfterPlaceOrder Event POST call->'.print_r($runtimeException->getMessage(),true));
        }
        // --------------------------------ZEND---------------
    }
}

<?php

namespace Epi\ExternalPrint\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Epi\ExternalPrint\Model\Config\ExternalPrint;

class OrderPlaced implements ObserverInterface
{
    /**
     * @var ExternalPrint
     */
    private $externalPrintConfig;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * OrderPlaced constructor.
     * @param ExternalPrint $externalPrintConfig
     * @param ClientInterface $httpClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExternalPrint $externalPrintConfig,
        ClientInterface $httpClient,
        LoggerInterface $logger,
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->externalPrintConfig = $externalPrintConfig;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->logger->info('[EPI]New order placed: ' . $order->getIncrementId());
        // log json details
        try {
            $this->logJsonOrderDetails($order);
        } catch (\Exception $e) {

        }


    }


    private function logJsonOrderDetails($order)
    {
        $orderData = [
            'order_id' => $order->getIncrementId(),
            'location_id' => '6116f0377b063651a560771a', // storeId $order->getStoreId()
            'customer_name' => $order->getCustomerName(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_mobile_number' => $order->getCustomAttribute('customer_mobile_number') ? $order->getCustomAttribute("customer_mobile_number")->getValue() : '+15617022607',
            'grand_total' => $order->getGrandTotal(),
            'payment_method' => $order->getPayment()->getMethodInstance()->getTitle(),
            'billing_address' => $order->getBillingAddress()->getData(),
            'shipping_address' => $order->getShippingAddress()->getData(),
            'items' => []
        ];

        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            $orderData['items'][] = [
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'quantity' => $item->getQtyOrdered(),
                'price' => $item->getPrice()
            ];
        }

        $jsonOrderData = json_encode($orderData, JSON_PRETTY_PRINT);
        $this->logger->info('Order Details (JSON):');
        $this->logger->info($jsonOrderData);

        // notify external system
        $this->notifyExternalSystem($orderData);
    }

    private function notifyExternalSystem($order)
    {
        if (!$this->externalPrintConfig->isEnabled()) {
            return;
        }

        $baseUrl = $this->externalPrintConfig->getBaseUrl();
        $endpoint = $this->externalPrintConfig->getEndpointUrl();

        $url = $baseUrl . $endpoint;

        $params = json_encode(['order' => $order]);
        $this->logger->info('Sending order data to external system', [
            'url' => $url,
            'params' => $params
        ]);

        try {
            // Initialize cURL session
            $ch = curl_init($url);

            if ($ch === false) {
                throw new \Exception('Failed to initialize cURL session');
            }

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

	    // Ignore certificate errors
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // Execute cURL request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL error: ' . curl_error($ch));
            }

            // Check the HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                throw new \Exception('Error calling external API: ' . $response);
            }

        } catch (\Exception $e) {
            // Log any exceptions
            $this->logger->error('Exception occurred in notifyExternalSystem: ' . $e->getMessage());
        } finally {
            // Close cURL session
            if (isset($ch) && is_resource($ch)) {
                curl_close($ch);
            }
        }
    }


}


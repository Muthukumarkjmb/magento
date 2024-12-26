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

    /**
     * @param Order $order
     */
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

    /**
     * @param Order $order
     */
    private function notifyExternalSystem($order)
    {
        if (!$this->externalPrintConfig->isEnabled()) {
            return;
        }
        $baseUrl = $this->externalPrintConfig->getBaseUrl();
        $endpoint = $this->externalPrintConfig->getEndpointUrl();

        $url = $baseUrl . $endpoint;

        // $endpoint = "https://5wjkqhp8-7082.brs.devtunnels.ms/magento-agent/order-created";;//$this->externalApiConfigProvider->getEndpoint('product-request-status');
        $params = [
            'order' => $order
            // Add any other relevant order data
        ];
        $this->logger->info('Sending order data to external system', [
            'url' => $url,
            'params' => $params
        ]);

        try {
            $this->logger->info('Sending payload');
            $response = $this->httpClient->request('POST', $url, [
                'body' => json_encode($params),
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        } catch (\Exception $e) {
            $this->logger->info('Failing to send order data to external system', [
                'exception' => $e
            ]);
        }

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(
                sprintf('Error calling external API: %s', $response->getBody()->getContents())
            );
        }
    }

}


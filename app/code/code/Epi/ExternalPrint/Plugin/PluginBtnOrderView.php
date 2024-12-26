<?php

namespace Epi\ExternalPrint\Plugin;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\ObjectManagerInterface;

class PluginBtnOrderView
{
    protected $object_manager;
    protected $_backendUrl;

    public function __construct(
        ObjectManagerInterface $om,
        UrlInterface $backendUrl
    ) {
        $this->object_manager = $om;
        $this->_backendUrl = $backendUrl;
    }

    public function beforeSetLayout( \Magento\Sales\Block\Adminhtml\Order\View $subject )
    {
        //$sendOrder = $this->_backendUrl->getUrl('sales/send/order/order_id/'.$subject->getOrderId() );
        $subject->addButton(
            'sendordersms',
            [
                'label' => __('Custom BUTTON SMS'),

                'class' => 'ship primary'
            ]
        );

        return null;
    }

}
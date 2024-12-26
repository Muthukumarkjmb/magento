<?php

namespace Burstonline\Epipayment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;

class EpipaymentConfigProvider implements ConfigProviderInterface
{
    /**
    * @param CcConfig $ccConfig
    * @param Source $assetSource
    */
    public function __construct(
        public \Magento\Payment\Model\CcConfig $ccConfig,
        public Source $assetSource
    ) {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
    * @var string[]
    */
    protected $_methodCode = 'epipayment';

    /**
    * {@inheritdoc}
    */
    public function getConfig()
    {
        return [
            'payment' => [
                'epipayment' => [
                    'availableTypes' => [$this->_methodCode => $this->ccConfig->getCcAvailableTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => $this->ccConfig->hasVerification(),
                ]
            ]
        ];
    }
}
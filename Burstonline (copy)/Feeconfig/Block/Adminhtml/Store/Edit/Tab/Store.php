<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Burstonline\Feeconfig\Block\Adminhtml\Store\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\System\Store as SystemStore;
use Burstonline\Feeconfig\Model\feeconfigFinish as feeconfigFinish;

/**
 * Class Author
 * @package Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab
 */
class Store extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    public $systemStore;

    /**
     * @var Config
     */
    public $wysiwygConfig;

    /**
     * @var Data
     */
    protected $_helperData;
    
    /**
     * @var feeconfigFinish
     */
    protected $_feeconfigFinish;


    /**
     * Author constructor.
     *
     * @param Config $wysiwygConfig
     * @param Store $systemStore
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ImageHelper $imageHelper
     * @param AuthorStatus $authorStatus
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,
        SystemStore $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        feeconfigFinish $feeconfigFinish,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->_feeconfigFinish = $feeconfigFinish;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Author $author */
        $store = $this->_coreRegistry->registry('burstonline_feeconfig_store');

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('store_');
        $form->setFieldNameSuffix('store');
        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Fees Information'),
            'class' => 'fieldset-wide'
        ]);
        
        $fieldset->addField('id', 'hidden', ['name' => 'id']);
        
        $fieldset->addField('enabled', 'checkbox', [
            'name' => 'enabled',
            'label' => __('Enabled'),
            'title' => __('Enabled'),
            'required' => false,
            'value' => '1' 
        ]);
        
        $fieldset->addField('title', 'text', [
            'name' => 'title',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => true
        ]);

        $fieldset->addField('sort_order', 'text', [
            'name' => 'sort_order',
            'label' => __('sort_order'),
            'title' => __('sort_order'),
            'required' => false
        ]);

        $fieldset->addField('amount', 'text', [
            'name' => 'amount',
            'label' => __('Amount'),
            'title' => __('Amount'),
            'required' => false
        ]);
        
		$fieldset->addField('fee_type', 'select', [
			'name' => 'fee_type',
			'label' => __('Fee Type'),
			'title' => __('Fee Type'),
			'required' => false,
			'values' => $this->_feeconfigFinish->getFeeType()
		]);
        
        $fieldset->addField('application_method', 'select', [
			'name' => 'application_method',
			'label' => __('Application Method'),
			'title' => __('Application Method'),
			'required' => false,
			'values' => $this->_feeconfigFinish->getApplicationMethod()
		]);
        
        
        $fieldset->addField('applies_to', 'select', [
			'name' => 'applies_to',
			'label' => __('Applies To'),
			'title' => __('Applies To'),
			'required' => false,
			'values' => $this->_feeconfigFinish->getAppliesTo()
		]);
        
        $fieldset->addField('mapping', 'select', [
			'name' => 'mapping',
			'label' => __('Mapping'),
			'title' => __('Mapping'),
			'required' => false,
			'values' => $this->_feeconfigFinish->getMapping()
		]);
        
        $form->addValues($store->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Fee Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }
    
    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}

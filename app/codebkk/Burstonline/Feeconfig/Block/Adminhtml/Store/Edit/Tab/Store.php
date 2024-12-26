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
        
    
        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true
        ]);

        $fieldset->addField('email', 'text', [
            'name' => 'email',
            'label' => __('Email'),
            'title' => __('Email'),
            'required' => true
        ]);

        $fieldset->addField('message', 'text', [
            'name' => 'message',
            'label' => __('Message'),
            'title' => __('Message'),
            'required' => true
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

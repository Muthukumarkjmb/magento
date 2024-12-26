<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created By : Rohan Hapani
 */
namespace Burstonline\Importproduct\Block\Adminhtml\Grid;

/**
 * Block for edit page
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Init container
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'burstonline_importproduct';
        $this->_controller = 'adminhtml_grid';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Grid'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ],
            ],
            -100
        );

        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Get edit form container header text
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('burstonline_importproduct_form_data')->getId()) {
            return __("Edit Post '%1'", $this->escapeHtml($this->_coreRegistry->registry('burstonline_importproduct_form_data')->getTitle()));
        } else {
            return __('New Record');
        }
    }

    /**
     * Retrieve the save and continue edit Url
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('importproduct/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
        function toggleEditor() {
            if (tinyMCE.getInstanceById('page_content') == null) {
                tinyMCE.execCommand('mceAddControl', false, 'content');
            } else {
                tinyMCE.execCommand('mceRemoveControl', false, 'content');
            }
        };
        ";
        return parent::_prepareLayout();
    }
}

<?php
namespace Burstonline\Importproduct\Block\Adminhtml;

class Importlog extends \Magento\Backend\Block\Widget\Grid\Container
{

	protected function _construct()
	{
		$this->_controller = 'adminhtml_post';
		$this->_blockGroup = 'Burstonline_Importproduct';
		$this->_headerText = __('Import Log');
		$this->_addButtonLabel = __('Create New Log');
		parent::_construct();
	}
}

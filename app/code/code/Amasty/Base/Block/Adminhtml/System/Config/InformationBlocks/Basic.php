<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Block\Adminhtml\System\Config\InformationBlocks;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Element\Template;

class Basic extends Template
{
    /**
     * @var string
     */
    private $class;

    public function toHtml()
    {
        $html = '';

        foreach ($this->getChildNames() as $childName) {
            $html .= $this->getChildBlock($childName)->toHtml();
        }

        if ($this->getClass()) {
            $html = '<div class="' . $this->getClass() .'">' . $html . '</div>';
        }

        return $html;
    }

    public function getElement(): AbstractElement
    {
        return $this->getData('element') ?? $this->getParentBlock()->getElement();
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}

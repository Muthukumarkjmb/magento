<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\Framework\View\TemplateEngine;

use Magento\Framework\View\Element\BlockInterface;

class Php
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    public function __construct(\Magento\Framework\Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    public function beforeRender(
        \Magento\Framework\View\TemplateEngine\Php $subject,
        BlockInterface $block,
        $fileName,
        array $dictionary = []
    ) {
        if (!isset($dictionary['escaper'])) {
            $dictionary['escaper'] = $this->escaper;
        }

        return [$block, $fileName, $dictionary];
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Magento 2 Base Package
 */

namespace Amasty\Base\Plugin\AdminNotification\Block\Grid\Renderer;

use Magento\AdminNotification\Block\Grid\Renderer\Notice as NativeNotice;

class Notice
{
    public function aroundRender(
        NativeNotice $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $row
    ) {
        $result = $proceed($row);

        $amastyLogo = '';
        $amastyImage = '';
        if ($row->getData('is_amasty')) {
            if ($row->getData('image_url')) {
                $amastyImage = ' style="background: url(' . $row->getData("image_url") . ') no-repeat;"';
            } else {
                $amastyLogo = ' amasty-grid-logo';
            }
        }
        $result = '<div class="ambase-grid-message' . $amastyLogo . '"' . $amastyImage . '>' . $result . '</div>';

        return  $result;
    }
}

<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Pickup with Locator for Magento 2
 */

namespace Amasty\StorePickupWithLocator\ViewModel\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\StorePickupWithLocator\Model\ConfigHtmlConverter\VariablesRenderer;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CurbsideLabel implements ArgumentInterface
{
    /**
     * @var VariablesRenderer
     */
    private $variableRenderer;

    public function __construct(
        VariablesRenderer $variablesRenderer
    ) {
        $this->variableRenderer = $variablesRenderer;
    }

    /**
     * @param LocationInterface $location
     * @return string
     */
    public function getCurbsideLabelHtml(LocationInterface $location): string
    {
        return $this->variableRenderer->renderVariable($location, VariablesRenderer::CURBSIDE_LABEL_VARIABLE_KEY);
    }
}

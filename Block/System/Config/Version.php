<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Billmate\BillmateCheckout\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var dataHelperInterface|\Billmate\BillmateCheckout\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param dataHelperInterface $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Billmate\BillmateCheckout\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->dataHelper->getPluginVersion();
    }
}
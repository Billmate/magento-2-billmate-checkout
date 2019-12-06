<?php
namespace Billmate\BillmateCheckout\Block\Adminhtml\Order\View;

use Billmate\BillmateCheckout\Model\Order as BillmateOrder;

class Payment extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        $this->_adminHelper = $adminHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getMethodDescription()
    {
        $payment = $this->getOrder()->getPayment();
        $bmPaymentData = $payment->getAdditionalInformation(BillmateOrder::BM_ADDITIONAL_INFO_CODE);
        if ($bmPaymentData) {
            return $bmPaymentData;
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
}
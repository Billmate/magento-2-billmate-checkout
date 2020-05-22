<?php

namespace Billmate\BillmateCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

/**
 * Class SaveData
 * @package Billmate\BillmateCheckout\Observer
 */
class SaveData extends AbstractDataAssignObserver
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    private $configHelper;

    /**
     * SaveData constructor.
     *
     * @param \Magento\Sales\Model\Order               $order
     * @param \Magento\Quote\Model\Quote               $quote
     * @param \Magento\Checkout\Model\Session          $_checkoutSession
     * @param \Billmate\BillmateCheckout\Helper\Config $configHelper
     */
    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Billmate\BillmateCheckout\Helper\Config $configHelper
    ){
        $this->order = $order;
        $this->quote = $quote;
        $this->checkoutSession = $_checkoutSession;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isCommentsEnabled()) {
            return;
        }
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds) || $this->checkoutSession->getData('has_saved_comment') == 1) {
            return;
        }
        foreach ($orderIds as $orderId){
            $order = $this->order->load($orderId);
            $quote = $this->quote->loadByIdWithoutStore($order->getQuoteId());
            $comment = $quote->getData('order_comment');
            $order->addStatusHistoryComment(__('Order Comment:') . " " . $comment);
            $order->save();
        }
        $this->checkoutSession->setData('has_saved_comment', 1);
    }
}

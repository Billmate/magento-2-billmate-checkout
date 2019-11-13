<?php

namespace Billmate\BillmateCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class SaveData extends AbstractDataAssignObserver{

    protected $order;
    protected $quote;
    protected $checkoutSession;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Checkout\Model\Session $_checkoutSession
    ){
        $this->order = $order;
        $this->quote = $quote;
        $this->checkoutSession = $_checkoutSession;
    }

    public function execute(Observer $observer){
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds) || $this->checkoutSession->getData('has_saved_comment') == 1) {
            return;
        }
        foreach ($orderIds as $orderId){
            $order = $this->order->load($orderId);
            $quote = $this->quote->loadByIdWithoutStore($order->getQuoteId());
            $comment = $quote->getData('order_comment');
            $order->addStatusHistoryComment($comment);
            $order->save();
        }
        $this->checkoutSession->setData('has_saved_comment', 1);
    }
}
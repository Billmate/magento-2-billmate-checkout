<?php
/**
 * Adding order comments field on collector checkout
 * Copyright (C) 2017 Ecomatic
 *
 * This file is part of Ecomatic/OrderComments.
 *
 * Ecomatic/OrderComments is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


namespace Billmate\BillmateCheckout\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Payment\Model\InfoInterface;

class SaveData extends AbstractDataAssignObserver{

    protected $order;
    protected $quote;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote
    ){
        $this->order = $order;
        $this->quote = $quote;
    }

    public function execute(Observer $observer){
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        foreach ($orderIds as $orderId){
            $order = $this->order->load($orderId);
            $quote = $this->quote->loadByIdWithoutStore($order->getQuoteId());
            $comment = $quote->getData('order_comment');
            $order->addStatusHistoryComment($comment);
            $order->save();
        }
    }
}
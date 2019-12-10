<?php

namespace Billmate\BillmateCheckout\Model\Config\Source;

class Business implements \Magento\Framework\Option\ArrayInterface {
    /**
     * Get status options
     *
     * @return array
     */
    public function toOptionArray(){
        return [
            'individual' => __('Consumer'),
            'business' => __('Company'),
        ];
    }
}
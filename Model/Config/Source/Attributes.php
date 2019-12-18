<?php

namespace Billmate\BillmateCheckout\Model\Config\Source;

class Attributes implements \Magento\Framework\Option\ArrayInterface {
	/**
	* Get status options
	*
	* @return array
	*/	
    public function toOptionArray(){
        return [
            'super' => __('Show Super Attributes'),
            'all' => __('Show All Visible Attributes'),
            'none' => __('Show no Attributes'),
        ];
    }
}
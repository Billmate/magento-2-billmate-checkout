<?php

namespace Billmate\BillmateCheckout\Model\Config\Source;

class Interval implements \Magento\Framework\Option\ArrayInterface {
	/**
	* Get status options
	*
	* @return array
	*/	
    public function toOptionArray(){
        return [
            'minute' => __('Minute'),
            'hour' => __('Hourly'),
        ];
    }
}